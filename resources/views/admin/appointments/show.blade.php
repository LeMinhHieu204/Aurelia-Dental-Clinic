@extends('layouts.admin', ['title' => 'Appointment '.$appointment->reference])

@section('content')
@php($canManage = auth()->user()->hasPermission('appointments.manage'))
<div class="admin-head">
    <div><p class="eyebrow">Appointment request</p><h1>{{ $appointment->reference }}</h1></div>
    <a class="btn btn-outline" href="{{ $canManage ? route('admin.appointments.index') : route('admin.calendar') }}">Back to {{ $canManage ? 'appointments' : 'calendar' }}</a>
</div>
<div class="content-grid">
    <div class="admin-panel" style="margin-top:0">
        <h2 style="font-size:30px">Request details</h2>
        <table class="admin-table" style="margin-top:17px">
            <tr><th>Patient</th><td>{{ $appointment->patient->full_name }}@if($canManage)<br>{{ $appointment->patient->email }}<br>{{ $appointment->patient->phone }}@endif</td></tr>
            <tr><th>Treatment</th><td>{{ $appointment->treatment->name }}</td></tr>
            @if($canManage)<tr><th>Message</th><td>{{ $appointment->message ?: '—' }}</td></tr>@endif
            <tr><th>Schedule</th><td>{{ $appointment->preferred_date->format('D, d M Y') }}{{ $appointment->preferred_time ? ' at '.substr($appointment->preferred_time, 0, 5) : '' }}<br>{{ $appointment->doctor?->full_name ?: 'No dentist assigned' }} · {{ $appointment->location->name }}</td></tr>
        </table>
        @if($canManage)
            <h2 style="font-size:26px;margin-top:28px">Assign and schedule</h2>
            <form method="POST" action="{{ route('admin.appointments.details', $appointment) }}" style="margin-top:15px">
                @csrf @method('PATCH')
                <div class="field"><label for="doctor_id">Dentist</label><select id="doctor_id" name="doctor_id"><option value="">No preference</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected($appointment->doctor_id === $doctor->id)>{{ $doctor->full_name }}</option>@endforeach</select></div>
                <div class="field" style="margin-top:12px"><label for="location_id">Location</label><select id="location_id" name="location_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($appointment->location_id === $location->id)>{{ $location->name }}</option>@endforeach</select></div>
                <div class="form-grid" style="margin-top:12px"><div class="field"><label for="preferred_date">Date</label><input id="preferred_date" type="date" name="preferred_date" min="{{ today()->toDateString() }}" value="{{ $appointment->preferred_date->toDateString() }}" required></div><div class="field"><label for="preferred_time">Time</label><input id="preferred_time" type="time" name="preferred_time" value="{{ $appointment->preferred_time ? substr($appointment->preferred_time, 0, 5) : '' }}"></div></div>
                <button class="btn btn-outline" style="margin-top:16px">Save schedule</button>
            </form>
        @endif
    </div>
    <div class="admin-panel" style="margin-top:0">
        @if($canManage)
            <h2 style="font-size:30px">Update status</h2>
            <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}" style="margin-top:18px">
                @csrf @method('PATCH')
                <div class="field"><label for="status">Status</label><select id="status" name="status">@foreach(['REQUESTED','CONTACTED','PROPOSED','CONFIRMED','COMPLETED','CANCELLED','NO_SHOW'] as $status)<option @selected($appointment->status === $status)>{{ $status }}</option>@endforeach</select></div>
                <div class="field" style="margin-top:15px"><label for="notes">Internal note</label><textarea id="notes" name="notes"></textarea></div>
                <button class="btn btn-primary" style="margin-top:18px">Save status</button>
            </form>
        @else
            <h2 style="font-size:30px">Appointment status</h2>
            <p class="muted" style="margin-top:15px">{{ $appointment->status }}. This read-only view shows only appointments assigned to your dentist profile.</p>
        @endif
        <h3 style="margin-top:30px">History</h3>
        <div class="footer-links" style="margin-top:10px">@foreach($appointment->statusHistory as $history)<span><strong>{{ $history->status }}</strong> · {{ $history->created_at->format('d M Y H:i') }}{{ $history->notes && $canManage ? ' — '.$history->notes : '' }}</span>@endforeach</div>
    </div>
</div>
@endsection
