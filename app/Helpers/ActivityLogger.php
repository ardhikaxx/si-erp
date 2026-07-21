<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log($module, $action, $description = null, $referenceType = null, $referenceId = null, $data = null)
    {
        if (!Auth::check()) return;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'description' => $description,
            'data' => $data,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
