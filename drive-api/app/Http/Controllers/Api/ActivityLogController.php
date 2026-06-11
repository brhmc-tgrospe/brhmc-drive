<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'developer') {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }
        $query = Activity::with(['causer', 'subject' => function ($morphTo) {
            $morphTo->morphWith([
                \App\Models\Shift::class => ['driver', 'vehicle'],
                \App\Models\Trip::class => ['shift.driver', 'shift.vehicle'],
                \App\Models\AmbulanceInspection::class => ['vehicle'],
                \App\Models\Incident::class => ['vehicle']
            ]);
        }])
        ->where('subject_type', '!=', 'App\\Models\\ChecklistItem')
        ->orderBy('created_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('log_name')) {
            $type = $request->log_name;
            if ($type === 'incident') $query->where('subject_type', \App\Models\Incident::class);
            elseif ($type === 'checklist') $query->whereIn('subject_type', [\App\Models\Checklist::class, \App\Models\ChecklistItem::class]);
            elseif ($type === 'maintenance') $query->where('subject_type', \App\Models\AmbulanceInspection::class);
            elseif ($type === 'trip') $query->whereIn('subject_type', [\App\Models\Trip::class, \App\Models\TripLog::class, \App\Models\Shift::class]);
            elseif ($type === 'auth') $query->where('subject_type', \App\Models\User::class)->where('log_name', '!=', 'authentication');
            elseif ($type === 'vehicle') $query->where('subject_type', \App\Models\Vehicle::class);
            elseif ($type === 'login') $query->where('log_name', 'authentication')->where('event', 'login');
            elseif ($type === 'logout') $query->where('log_name', 'authentication')->where('event', 'logout');
            else $query->where('log_name', $type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhere('created_at', 'like', "%{$search}%")
                  ->orWhereRaw("CAST(properties AS CHAR) LIKE ?", ["%{$search}%"])
                  ->orWhereHas('causer', function($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                  });
            });
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }
}
