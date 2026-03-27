<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        // ambil notifikasi user yang login
        $notifications = Auth::user()->notifications()->latest()->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markAllRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function markRead(Request $request, $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }
    public function redirectAndMarkRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Tandai sebagai sudah dibaca
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // Cek apakah URL terkait masih valid
        $url = $notification->data['url'] ?? '/';

        // Normalize absolute URLs (possibly from outdated APP_URL) to local app-relative paths.
        if (is_string($url) && preg_match('/^https?:\/\//i', $url)) {
            $parts = parse_url($url);
            if (!empty($parts['path'])) {
                $url = $parts['path'];
                if (!empty($parts['query'])) {
                    $url .= '?' . $parts['query'];
                }
                if (!empty($parts['fragment'])) {
                    $url .= '#' . $parts['fragment'];
                }
            } else {
                $url = '/';
            }
        }

        if (!is_string($url) || $url === '') {
            $url = '/';
        }

        // Contoh untuk document: cek apakah document masih ada
        if (isset($notification->data['document_id'])) {
            $documentExists = \App\Models\Document::find($notification->data['document_id']);
            if (!$documentExists) {
                return redirect()->route('document-control.index')
                    ->with('error', 'The document no longer exists.');
            }
        }

        return redirect($url);
    }
}
