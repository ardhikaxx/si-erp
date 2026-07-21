<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('user_id', Auth::id())->latest();

        if ($request->ajax()) {
            $notifications = $query->get();

            return datatables()->of($notifications)
                ->addIndexColumn()
                ->editColumn('is_read', function ($notification) {
                    return $notification->is_read
                        ? '<span class="badge bg-success">Sudah dibaca</span>'
                        : '<span class="badge bg-warning">Belum dibaca</span>';
                })
                ->editColumn('created_at', function ($notification) {
                    return $notification->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function ($notification) {
                    $btn = '';

                    if (!$notification->is_read) {
                        $btn .= '<a href="' . route('notifications.markAsRead', $notification->id) . '"
                                     class="btn btn-sm btn-info me-1"
                                     title="Tandai dibaca">
                                     <i class="fas fa-check"></i>
                                 </a>';
                    }

                    if ($notification->url) {
                        $btn .= '<a href="' . $notification->url . '"
                                     class="btn btn-sm btn-primary"
                                     title="Lihat">
                                     <i class="fas fa-eye"></i>
                                 </a>';
                    }

                    return $btn;
                })
                ->rawColumns(['is_read', 'action'])
                ->make(true);
        }

        return view('notifications.index');
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($notification->url) {
            return redirect($notification->url);
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
