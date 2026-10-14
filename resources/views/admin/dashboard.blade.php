@extends('layouts.admin', ['title' => 'Dashboard'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Operations</p><h1>Dashboard</h1><p class="muted">Live operational counts from the Aurelia database.</p></div><a class="btn btn-primary" href="{{ route('admin.appointments.index') }}">Review appointments →</a></div>
<div class="admin-grid">
    <div class="admin-stat"><strong>{{ $todayRequests }}</strong><span>Requests today</span></div>
    <div class="admin-stat"><strong>{{ $pendingRequests }}</strong><span>Pending requests</span></div>
    <div class="admin-stat"><strong>{{ $confirmed }}</strong><span>Confirmed appointments</span></div>
    <div class="admin-stat"><strong>{{ $thisWeek }}</strong><span>Appointments this week</span></div>
    <div class="admin-stat"><strong>{{ $cancelled }}</strong><span>Cancelled appointments</span></div>
    <div class="admin-stat"><strong>{{ $doctors }}</strong><span>Active doctors</span></div>
    <div class="admin-stat"><strong>{{ $treatments }}</strong><span>Published treatments</span></div>
    <div class="admin-stat"><strong>{{ $inquiries }}</strong><span>Pending inquiries</span></div>
</div>
<div class="content-grid">
    <div class="admin-panel"><div class="section-head"><div><p class="eyebrow">Next up</p><h2 style="font-size:30px">Upcoming schedule</h2></div><a class="btn-link" href="{{ route('admin.calendar') }}">Open calendar →</a></div><table class="admin-table"><thead><tr><th>Date</th><th>Patient</th><th>Dentist</th><th>Status</th></tr></thead><tbody>@forelse($upcomingAppointments as $appointment)<tr><td>{{ $appointment->preferred_date->format('d M Y') }}<br>{{ $appointment->preferred_time ? substr($appointment->preferred_time, 0, 5) : 'No time' }}</td><td>{{ $appointment->patient->full_name }}</td><td>{{ $appointment->doctor?->full_name ?: 'Unassigned' }}</td><td><span class="status-pill">{{ $appointment->status }}</span></td></tr>@empty<tr><td colspan="4">No upcoming appointments.</td></tr>@endforelse</tbody></table></div>
    @if(auth()->user()->hasPermission('audit.view'))<div class="admin-panel"><div class="section-head"><div><p class="eyebrow">Security</p><h2 style="font-size:30px">Recent activity</h2></div><a class="btn-link" href="{{ route('admin.audit-logs') }}">View audit log →</a></div><div class="footer-links">@forelse($recentAudit as $log)<span><strong>{{ $log->action }}</strong><br>{{ $log->user?->email ?: 'System' }} · {{ $log->created_at->format('d M H:i') }}</span>@empty<span>No administrative activity yet.</span>@endforelse</div></div>@endif
</div>
<div class="admin-panel"><div class="section-head"><div><p class="eyebrow">Recent requests</p><h2 style="font-size:30px">Appointment requests</h2></div><a class="btn-link" href="{{ route('admin.appointments.index') }}">View all →</a></div><table class="admin-table"><thead><tr><th>Reference</th><th>Patient</th><th>Treatment</th><th>Preferred date</th><th>Status</th></tr></thead><tbody>@forelse($recentAppointments as $appointment)<tr><td><a href="{{ route('admin.appointments.show', $appointment) }}">{{ $appointment->reference }}</a></td><td>{{ $appointment->patient->full_name }}</td><td>{{ $appointment->treatment->name }}</td><td>{{ $appointment->preferred_date->format('d M Y') }}</td><td><span class="status-pill">{{ $appointment->status }}</span></td></tr>@empty<tr><td colspan="5">No appointment requests yet.</td></tr>@endforelse</tbody></table></div>
@endsection
