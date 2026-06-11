<?php
$models = [
    'Vehicle', 'Incident', 'Checklist', 'ChecklistItem', 
    'Shift', 'Trip', 'VehicleShift', 'AmbulanceInspection'
];
foreach($models as $model) {
    $path = "app/Models/{$model}.php";
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        if (strpos($content, 'use Spatie\Activitylog\Traits\LogsActivity;') === false) {
            $content = preg_replace(
                '/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/',
                "use Illuminate\\Database\\Eloquent\\Model;\nuse Spatie\\Activitylog\\Traits\\LogsActivity;\nuse Spatie\\Activitylog\\LogOptions;",
                $content
            );
            
            $content = preg_replace(
                '/(class\s+'.$model.'\s+extends\s+Model\s*\{)/',
                "$1\n    use LogsActivity;\n",
                $content
            );
            
            $method = "
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
";
            $content = preg_replace('/\}\s*$/', $method . "\n}", $content);
            file_put_contents($path, $content);
            echo "Updated {$model}\n";
        } else {
            echo "Already updated {$model}\n";
        }
    } else {
        echo "Not found: {$model}\n";
    }
}
?>
