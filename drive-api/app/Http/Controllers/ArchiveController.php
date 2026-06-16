<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class ArchiveController extends Controller
{
    private function checkDeveloperAccess(Request $request)
    {
        if (strtolower($request->user()->role) !== 'developer') {
            abort(403, 'Unauthorized access. Only developers can access archives.');
        }
    }

    private function getModelClass($type)
    {
        // Convert type like 'users' -> 'User', 'ambulance_inspections' -> 'AmbulanceInspection'
        $modelName = Str::studly(Str::singular($type));
        $fullClass = '\\App\\Models\\' . $modelName;
        
        if (!class_exists($fullClass)) {
            abort(404, "Model not found.");
        }
        
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($fullClass))) {
            abort(400, "This model does not support soft deletes.");
        }
        
        return $fullClass;
    }

    public function index($type, Request $request)
    {
        $this->checkDeveloperAccess($request);
        
        $model = $this->getModelClass($type);
        
        $query = $model::onlyTrashed();

        if ($request->has('search') && !empty($request->input('search'))) {
            $search = $request->input('search');
            $tableName = (new $model)->getTable();
            $columns = Schema::getColumnListing($tableName);
            
            $query->where(function($q) use ($search, $columns) {
                // Always search by ID
                if (in_array('id', $columns)) {
                    $q->orWhere('id', 'like', "%{$search}%");
                }

                // Search across ALL string-compatible columns in the table
                $excluded = ['password', 'remember_token', 'legacy_permissions', 'image_path', 'signature', 'evidence_image', 'dispatcher_signature'];
                foreach ($columns as $col) {
                    if (!in_array($col, $excluded) && $col !== 'id') {
                        $q->orWhere($col, 'like', "%{$search}%");
                    }
                }
            });
        }

        $perPage = $request->input('per_page', 10);
        return response()->json($query->paginate($perPage));
    }

    public function restore($type, $id, Request $request)
    {
        $this->checkDeveloperAccess($request);
        
        $model = $this->getModelClass($type);
        $record = $model::onlyTrashed()->findOrFail($id);
        $record->restore();

        return response()->json(['message' => 'Record restored successfully.', 'data' => $record]);
    }

    public function forceDelete($type, $id, Request $request)
    {
        $this->checkDeveloperAccess($request);
        
        $model = $this->getModelClass($type);
        $record = $model::onlyTrashed()->findOrFail($id);
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->event('force_deleted')
            ->log('permanently wiped and is not recoverable');

        \Illuminate\Support\Facades\DB::table($record->getTable())->where('id', $record->id)->delete();

        return response()->json(['message' => 'Record permanently deleted.']);
    }
}
