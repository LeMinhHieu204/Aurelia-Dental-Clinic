@extends('layouts.admin', ['title' => 'Appointments'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Operations</p><h1>Appointments</h1></div><a class="btn btn-outline" href="{{ route('admin.calendar') }}">Calendar →</a></div>
<div class="admin-panel">
    <form class="admin-form" method="GET">
        <div class="field"><label for="q">Search</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Reference or patient"></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option>@foreach(['REQUESTED','CONTACTED','PROPOSED','CONFIRMED','COMPLETED','CANCELLED','NO_SHOW'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="field"><label for="doctor_id">Dentist</label><select id="doctor_id" name="doctor_id"><option value="">All dentists</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>{{ $doctor->full_name }}</option>@endforeach</select></div>
        <div class="field"><label for="treatment_id">Treatment</label><select id="treatment_id" name="treatment_id"><option value="">All treatments</option>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}" @selected(request('treatment_id') == $treatment->id)>{{ $treatment->name }}</option>@endforeach</select></div>
        <div class="field"><label for="location_id">Location</label><select id="location_id" name="location_id"><option value="">All locations</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>@endforeach</select></div>
        <div class="field"><label for="date">Preferred date</label><input id="date" type="date" name="date" value="{{ request('date') }}"></div>
        <div class="field" style="justify-content:end"><button class="btn btn-outline">Filter</button></div>
    </form>
    <table class="admin-table"><thead><tr><th>Reference</th><th>Patient</th><th>Treatment</th><th>Doctor</th><th>Requested</th><th>Status</th></tr></thead><tbody>
    @forelse($appointments as $appointment)
        <tr><td><a href="{{ route('admin.appointments.show', $appointment) }}">{{ $appointment->reference }}</a></td><td>{{ $appointment->patient->full_name }}<br><span class="muted">{{ $appointment->patient->email }}</span></td><td>{{ $appointment->treatment->name }}</td><td>{{ $appointment->doctor?->full_name ?: 'No preference' }}</td><td>{{ $appointment->preferred_date->format('d M Y') }}{{ $appointment->preferred_time ? ' · '.substr($appointment->preferred_time, 0, 5) : '' }}</td><td><span class="status-pill">{{ $appointment->status }}</span></td></tr>
    @empty
        <tr><td colspan="6">No appointment requests match this filter.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:20px">{{ $appointments->links() }}</div>
</div>
@endsection
