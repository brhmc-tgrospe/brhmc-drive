<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemHealthController extends Controller
{
    public function getMetrics(Request $request)
    {
        // 1. Security & Auth Check
        if ($request->user()->role !== 'developer') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // 2. Database Stats
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        
        $tables = DB::select("SELECT SUM(TABLE_ROWS) as total_rows, SUM(DATA_LENGTH + INDEX_LENGTH) as total_size, SUM(DATA_FREE) as total_overhead FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?", [$dbName]);
        $dbRows = $tables[0]->total_rows ?? 0;
        $dbSize = $tables[0]->total_size ?? 0;
        $dbOverhead = $tables[0]->total_overhead ?? 0;
        
        $maxConn = DB::select("SHOW VARIABLES LIKE 'max_connections'")[0]->Value ?? 0;
        $activeConn = DB::select("SHOW STATUS WHERE variable_name = 'Threads_connected'")[0]->Value ?? 0;
        $isRoot = strtolower($dbUser) === 'root';

        // 3. Disk & Storage
        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());
        
        $getDirSize = function($dir) {
            if (!is_dir($dir)) return 0;
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $size += $file->getSize();
            }
            return $size;
        };
        $uploadsSize = $getDirSize(storage_path('app/public/uploads'));
        $reportsSize = $getDirSize(storage_path('app/public/reports'));

        // 4. Permissions & Environment
        $perms = [
            'storage' => is_writable(storage_path()),
            'bootstrap_cache' => is_writable(base_path('bootstrap/cache'))
        ];
        
        $phpEnv = [
            'mysqli' => extension_loaded('mysqli'),
            'gd' => extension_loaded('gd'),
            'curl' => extension_loaded('curl'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'display_errors' => in_array(strtolower(ini_get('display_errors')), ['1', 'on', 'true'])
        ];

        // 5. Service Connectivity
        $ping = function($host, $port, $timeout = 2) {
            try {
                $fsock = @fsockopen($host, $port, $errno, $errstr, $timeout);
                if ($fsock) {
                    fclose($fsock);
                    return true;
                }
            } catch (\Exception $e) {}
            return false;
        };
        
        $services = [
            'mysql' => true, // DB is active if queries above passed
            'google' => $ping('google.com', 80),
            'smtp' => $ping('smtp.gmail.com', 587)
        ];

        $sslExpiry = 'N/A';
        try {
            $url = config('app.url');
            if (str_starts_with($url, 'https')) {
                $parsedUrl = parse_url($url);
                $host = $parsedUrl['host'] ?? '';
                if ($host) {
                    $g = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
                    $r = @stream_socket_client("ssl://{$host}:443", $errno, $errstr, 2, STREAM_CLIENT_CONNECT, $g);
                    if ($r) {
                        $cont = stream_context_get_params($r);
                        $cert = openssl_x509_parse($cont["options"]["ssl"]["peer_certificate"]);
                        $sslExpiry = date('Y-m-d', $cert['validTo_time_t']);
                    }
                }
            }
        } catch (\Exception $e) {}

        // 6. Server Error Log
        $logPath = storage_path('logs/laravel.log');
        $logLines = [];
        $oomErrors = 0;
        $slowQueries = 0;
        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);
            $lines = explode("\n", $content);
            $logLines = array_slice(array_filter($lines), -50); 
            $oomErrors = substr_count($content, 'Allowed memory size of') + substr_count(strtolower($content), 'out of memory');
            $slowQueries = substr_count(strtolower($content), 'slow query');
        }

        // 7. Backups
        $backupDir = storage_path('app/backups');
        $staleBackups = false;
        $latestBackup = null;
        if (is_dir($backupDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($backupDir));
            $newestTime = 0;
            $newestFile = null;
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'zip') {
                    $mtime = $file->getMTime();
                    if ($mtime > $newestTime) {
                        $newestTime = $mtime;
                        $newestFile = $file->getFilename();
                    }
                }
            }
            if ($newestTime > 0) {
                $latestBackup = $newestFile . ' (' . date('Y-m-d H:i:s', $newestTime) . ')';
                if (time() - $newestTime > 7 * 86400) {
                    $staleBackups = true;
                }
            } else {
                $staleBackups = true;
            }
        } else {
            $staleBackups = true;
        }

        // Fetch Schedule Settings
        $scheduleFile = storage_path('app/backup-schedule.json');
        $schedule = [
            'enabled' => false, 
            'frequency' => 'daily',
            'dayOfWeek' => 1,
            'dayOfMonth' => 1,
            'time' => '02:00'
        ];
        if (file_exists($scheduleFile)) {
            $saved = json_decode(file_get_contents($scheduleFile), true);
            if (is_array($saved)) {
                $schedule = array_merge($schedule, $saved);
            }
        }

        // 8. Health Score Algorithm
        $score = 100;
        $diskUsagePercent = ($diskTotal > 0) ? (($diskTotal - $diskFree) / $diskTotal) * 100 : 0;
        if ($diskUsagePercent > 90) $score -= 20;
        
        $connPercent = ($maxConn > 0) ? ($activeConn / $maxConn) * 100 : 0;
        if ($connPercent > 80) $score -= 15;
        
        if ($phpEnv['display_errors']) $score -= 10;
        if ($isRoot) $score -= 10;
        if ($oomErrors > 0) $score -= 20;
        if ($staleBackups) $score -= 10;
        
        $score = max(0, $score);

        return response()->json([
            'score' => $score,
            'database' => [
                'rows' => $dbRows,
                'size' => $dbSize,
                'overhead' => $dbOverhead,
                'activeConnections' => $activeConn,
                'maxConnections' => $maxConn,
                'isRoot' => $isRoot
            ],
            'disk' => [
                'total' => $diskTotal,
                'free' => $diskFree,
                'uploadsSize' => $uploadsSize,
                'reportsSize' => $reportsSize
            ],
            'permissions' => $perms,
            'environment' => $phpEnv,
            'services' => $services,
            'sslExpiry' => $sslExpiry,
            'logs' => [
                'oomErrors' => $oomErrors,
                'slowQueries' => $slowQueries,
                'recentLines' => $logLines,
                'size' => file_exists($logPath) ? filesize($logPath) : 0
            ],
            'backups' => [
                'latest' => $latestBackup,
                'isStale' => $staleBackups,
                'schedule' => $schedule
            ]
        ]);
    }

    public function clearErrorLog(Request $request)
    {
        if ($request->user()->role !== 'developer') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return response()->json(['success' => true]);
    }

    public function triggerBackup(Request $request)
    {
        if ($request->user()->role !== 'developer') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $type = $request->input('type', 'database');

        try {
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            $flags = ['--disable-notifications' => true];
            if ($type === 'database') {
                $flags['--only-db'] = true;
            } elseif ($type === 'files') {
                $flags['--only-files'] = true;
            }
            
            // Fix for Windows mysqldump WSAEPROVIDERFAILEDINIT (10106) error
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                putenv('SystemRoot=C:\\WINDOWS');
                $_ENV['SystemRoot'] = 'C:\\WINDOWS';
                $_SERVER['SystemRoot'] = 'C:\\WINDOWS';
            }
            
            $exitCode = \Illuminate\Support\Facades\Artisan::call('backup:run', $flags);
            $output = \Illuminate\Support\Facades\Artisan::output();
            
            if ($exitCode !== 0) {
                return response()->json(['error' => 'Backup command failed. Output: ' . $output], 500);
            }
            
            return response()->json(['success' => true, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSchedule(Request $request)
    {
        if ($request->user()->role !== 'developer') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'enabled' => 'required|boolean',
            'frequency' => 'nullable|string|in:daily,weekly,monthly',
            'dayOfWeek' => 'nullable|integer|min:1|max:7',
            'dayOfMonth' => 'nullable|integer|min:1|max:31',
            'time' => 'required|string'
        ]);

        $scheduleFile = storage_path('app/backup-schedule.json');
        file_put_contents($scheduleFile, json_encode([
            'enabled' => $request->enabled,
            'frequency' => $request->frequency ?? 'daily',
            'dayOfWeek' => (int) ($request->dayOfWeek ?? 1),
            'dayOfMonth' => (int) ($request->dayOfMonth ?? 1),
            'time' => $request->time
        ]));

        return response()->json(['success' => true]);
    }
}
