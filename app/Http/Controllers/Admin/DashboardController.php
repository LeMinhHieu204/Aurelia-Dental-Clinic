<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Inquiry;
use App\Models\Treatment;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (auth()->user()->hasPermission('calendar.own') && ! auth()->user()->hasPermission('calendar.manage')) {
            return redirect()->route('admin.calendar');
        }

        $pendingStatuses = [AppointmentRequest::STATUS_REQUESTED, AppointmentRequest::STATUS_CONTACTED, AppointmentRequest::STATUS_PROPOSED];
        return view('admin.dashboard', [
            'todayRequests' => AppointmentRequest::whereDate('created_at', today())->count(),
            'pendingRequests' => AppointmentRequest::whereIn('status', $pendingStatuses)->count(),
            'newRequests' => AppointmentRequest::where('status', AppointmentRequest::STATUS_REQUESTED)->count(),
            'confirmed' => AppointmentRequest::where('status', AppointmentRequest::STATUS_CONFIRMED)->count(),
            'thisWeek' => AppointmentRequest::whereDate('preferred_date', '>=', today()->toDateString())->whereDate('preferred_date', '<=', today()->endOfWeek()->toDateString())->whereNotIn('status', [AppointmentRequest::STATUS_CANCELLED])->count(),
            'cancelled' => AppointmentRequest::where('status', AppointmentRequest::STATUS_CANCELLED)->count(),
            'upcoming' => AppointmentRequest::whereDate('preferred_date', '>=', today())->whereNotIn('status', [AppointmentRequest::STATUS_CANCELLED])->count(),
            'doctors' => Doctor::where('is_active', true)->count(),
            'treatments' => Treatment::where('is_published', true)->count(),
            'inquiries' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'recentAppointments' => AppointmentRequest::with(['patient', 'treatment'])->latest()->limit(8)->get(),
            'upcomingAppointments' => AppointmentRequest::with(['patient', 'doctor', 'location'])->whereDate('preferred_date', '>=', today())->whereNotIn('status', [AppointmentRequest::STATUS_CANCELLED, AppointmentRequest::STATUS_COMPLETED])->orderBy('preferred_date')->orderBy('preferred_time')->limit(8)->get(),
            'recentAudit' => auth()->user()->hasPermission('audit.view') ? AuditLog::with('user')->latest()->limit(8)->get() : collect(),
        ]);
    }
}
