<?php

namespace App\Http\Controllers;

use App\Models\Dokter;
use App\Models\NotificationLog;
use App\Models\SipRecord;
use App\Models\User;
use App\Services\WebNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationCenterController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $typeFilter = $request->query('message_type');
        $statusFilter = $request->query('status');
        $viewMode = $request->query('mode', $user->isAdminOrSuperAdmin() ? 'all' : 'personal');

        $query = NotificationLog::with(['recipient.dokter.ksm', 'sender', 'sipRecord.dokter']);

        if (! $user->isAdminOrSuperAdmin() || $viewMode === 'personal') {
            $query->where('recipient_user_id', $user->id);
        }

        if ($typeFilter) {
            $query->where('message_type', $typeFilter);
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $notifications = $query->orderByDesc('sent_at')->paginate(15)->withQueryString();

        // Summary metrics
        $baseMetricQuery = NotificationLog::query();
        if (! $user->isAdminOrSuperAdmin() || $viewMode === 'personal') {
            $baseMetricQuery->where('recipient_user_id', $user->id);
        }

        $totalNotif = (clone $baseMetricQuery)->count();
        $totalRead = (clone $baseMetricQuery)->where('status', 'read')->count();
        $totalUnread = (clone $baseMetricQuery)->where('status', 'sent')->count();
        $readRate = $totalNotif > 0 ? round(($totalRead / $totalNotif) * 100, 1) : 0.0;

        $dokterList = $user->isAdminOrSuperAdmin()
            ? Dokter::with(['user', 'ksm', 'activeSipRecords'])->orderBy('nama_lengkap')->get()
            : collect();

        return view('notifications.index', compact(
            'notifications',
            'totalNotif',
            'totalRead',
            'totalUnread',
            'readRate',
            'dokterList',
            'typeFilter',
            'statusFilter',
            'viewMode'
        ));
    }

    /**
     * Mark a single notification as read and optionally redirect to its target URL.
     */
    public function markAsRead(Request $request, NotificationLog $notificationLog): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $isRecipient = $notificationLog->recipient_user_id === $user->id;
        $isAdminSharedSubmissionAlert = $user->isAdminOrSuperAdmin()
            && $notificationLog->message_type === 'pengajuan_sip_baru';

        // Only mark as read if the current user is the actual recipient (or an Admin handling an admin submission alert)
        // so that Admin monitoring does not falsify Doctor read-rate audit metrics.
        if (($isRecipient || $isAdminSharedSubmissionAlert) && $notificationLog->status !== 'read') {
            $notificationLog->update([
                'status' => 'read',
                'read_at' => Carbon::now(),
            ]);
        }

        if ($request->boolean('redirect')) {
            if ($isRecipient && $notificationLog->action_url) {
                return redirect($notificationLog->action_url);
            }

            if ($user->isAdminOrSuperAdmin()) {
                if ($notificationLog->message_type === 'pengajuan_sip_baru') {
                    return redirect()->route('verifikasi.index');
                }

                $notificationLog->loadMissing(['sipRecord', 'recipient.dokter']);
                $dokterId = $notificationLog->sipRecord?->dokter_id
                    ?? $notificationLog->recipient?->dokter?->id;

                if ($dokterId) {
                    return redirect()->route('dokter.show', $dokterId);
                }

                if ($notificationLog->action_url && ! str_starts_with($notificationLog->action_url, '/portal')) {
                    return redirect($notificationLog->action_url);
                }
            }
        }

        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    /**
     * Mark all unread notifications for the logged-in user as read.
     */
    public function markAllAsRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        NotificationLog::where('recipient_user_id', $user->id)
            ->where('status', 'sent')
            ->update([
                'status' => 'read',
                'read_at' => Carbon::now(),
            ]);

        return back()->with('success', 'Seluruh notifikasi masuk Anda telah ditandai dibaca.');
    }

    /**
     * Send a direct In-App Web Notification from Admin SDM / Super Admin to a Doctor.
     */
    public function sendDirect(Request $request, WebNotificationService $notificationService): RedirectResponse
    {
        $validated = $request->validate([
            'recipient_user_id' => ['required', 'exists:users,id'],
            'sip_record_id' => ['nullable', 'exists:sip_records,id'],
            'message_type' => ['required', 'in:early_warning,urgent,account_created,verifikasi_approved,verifikasi_rejected'],
            'title' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'string', 'max:2000'],
        ]);

        $recipient = User::findOrFail($validated['recipient_user_id']);
        $sipRecord = ! empty($validated['sip_record_id']) ? SipRecord::find($validated['sip_record_id']) : null;

        $notificationService->send(
            recipient: $recipient,
            messageType: $validated['message_type'],
            title: $validated['title'],
            payload: $validated['payload'],
            sipRecord: $sipRecord,
            sender: $request->user(),
            actionUrl: $recipient->role === 'dokter' ? route('portal.renewal.create', absolute: false) : route('dashboard', absolute: false)
        );

        return back()->with('success', 'Direct In-App Web Notification berhasil dikirim ke ' . $recipient->name . '!');
    }

    /**
     * Polling endpoint for real-time Topbar Bell Badge & Browser Web Notification API.
     */
    public function pollUnread(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $unreadCount = NotificationLog::where('recipient_user_id', $user->id)
            ->where('status', 'sent')
            ->count();

        $latestUnread = NotificationLog::where('recipient_user_id', $user->id)
            ->where('status', 'sent')
            ->latest('sent_at')
            ->limit(5)
            ->get()
            ->map(fn (NotificationLog $log) => [
                'id' => $log->id,
                'title' => $log->title,
                'payload' => $log->payload,
                'message_type' => $log->message_type,
                'type_label' => $log->message_type_label,
                'type_badge_class' => $log->type_badge_class,
                'sent_at_human' => $log->sent_at?->diffForHumans() ?? 'Baru saja',
                'read_url' => route('notifications.read', ['notificationLog' => $log->id, 'redirect' => 1], false),
            ]);

        return response()->json([
            'unread_count' => $unreadCount,
            'latest' => $latestUnread,
        ]);
    }
}

