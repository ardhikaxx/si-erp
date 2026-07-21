<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $logs = ActivityLog::with('user');

            if ($search = request('search.value')) {
                $logs->where(function ($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('ip_address', 'like', "%{$search}%");
                });
            }

            if ($filterUser = request('filter_user')) {
                $logs->where('user_id', $filterUser);
            }

            if ($filterModule = request('filter_module')) {
                $logs->where('module', $filterModule);
            }

            if ($filterAction = request('filter_action')) {
                $logs->where('action', $filterAction);
            }

            if ($filterDateFrom = request('filter_date_from')) {
                $logs->whereDate('created_at', '>=', $filterDateFrom);
            }

            if ($filterDateTo = request('filter_date_to')) {
                $logs->whereDate('created_at', '<=', $filterDateTo);
            }

            return DataTables::of($logs->latest())
                ->addIndexColumn()
                ->editColumn('user_id', fn($l) => $l->user?->name ?? 'Sistem')
                ->editColumn('created_at', fn($l) => $l->created_at->format('d/m/Y H:i:s'))
                ->editColumn('action', function ($log) {
                    return match ($log->action) {
                        'create'  => '<span class="badge bg-success">Buat</span>',
                        'update'  => '<span class="badge bg-warning">Ubah</span>',
                        'delete'  => '<span class="badge bg-danger">Hapus</span>',
                        default   => '<span class="badge bg-info">' . ucfirst($log->action) . '</span>',
                    };
                })
                ->editColumn('description', fn($l) => strlen($l->description) > 150 ? substr($l->description, 0, 150) . '...' : $l->description)
                ->rawColumns(['action'])
                ->make(true);
        }

        $users = User::orderBy('name')->get();
        $modules = ActivityLog::select('module')->distinct()->whereNotNull('module')->orderBy('module')->pluck('module');
        $actions = ActivityLog::select('action')->distinct()->whereNotNull('action')->orderBy('action')->pluck('action');
        return view('settings.activity_logs.index', compact('users', 'modules', 'actions'));
    }
}
