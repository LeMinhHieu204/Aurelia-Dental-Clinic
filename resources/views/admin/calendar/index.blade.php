@extends('layouts.admin', ['title' => 'Calendar'])

@section('content')
<div class="admin-head">
    <div><p class="eyebrow">Scheduling</p><h1>Calendar</h1><p class="muted">Requested and confirmed appointments are shown separately; availability is configured per dentist.</p></div>
    @if(auth()->user()->hasPermission('calendar.manage'))<a class="btn btn-outline" href="{{ route('admin.availability') }}">Configure availability</a>@endif
</div>

<div class="admin-panel">
    <form class="admin-form" method="GET">
        <div class="field"><label for="view">View</label><select id="view" name="view"><option value="day" @selected($mode === 'day')>Day</option><option value="week" @selected($mode === 'week')>Week</option><option value="month" @selected($mode === 'month')>Month</option></select></div>
        <div class="field"><label for="date">Anchor date</label><input id="date" type="date" name="date" value="{{ $anchor->toDateString() }}"></div>
        <div class="field"><label for="doctor_id">Dentist</label><select id="doctor_id" name="doctor_id"><option value="">All dentists</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(request('doctor_id') == $doctor->id)>{{ $doctor->full_name }}</option>@endforeach</select></div>
        <div class="field"><label for="location_id">Location</label><select id="location_id" name="location_id"><option value="">All locations</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ $location->name }}</option>@endforeach</select></div>
        <div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option>@foreach(['REQUESTED','CONTACTED','PROPOSED','CONFIRMED','COMPLETED','CANCELLED','NO_SHOW'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="field" style="justify-content:end"><button class="btn btn-primary">Apply filters</button></div>
    </form>
    <div class="calendar-toolbar">
        <a class="btn btn-outline" href="{{ request()->fullUrlWithQuery(['date' => $anchor->subDays($mode === 'month' ? 28 : ($mode === 'week' ? 7 : 1))->toDateString()]) }}">Previous</a>
        <strong>{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</strong>
        <a class="btn btn-outline" href="{{ request()->fullUrlWithQuery(['date' => $anchor->addDays($mode === 'month' ? 28 : ($mode === 'week' ? 7 : 1))->toDateString()]) }}">Next</a>
    </div>
</div>

<div class="admin-panel">
    <div class="calendar-event-list">
    @forelse($appointments as $appointment)
        <article class="calendar-event status-{{ strtolower($appointment->status) }}">
            <div><strong>{{ $appointment->preferred_date->format('D, d M Y') }} {{ $appointment->preferred_time ? substr($appointment->preferred_time, 0, 5) : 'No time' }}</strong><span class="status-pill">{{ $appointment->status }}</span></div>
            <p>{{ $appointment->patient->full_name }} · {{ $appointment->treatment->name }} · {{ $appointment->doctor?->full_name ?: 'No dentist assigned' }} · {{ $appointment->location->name }}</p>
            <a href="{{ route('admin.appointments.show', $appointment) }}">Open {{ $appointment->reference }} →</a>
        </article>
    @empty
        <p class="notice">No appointments match this calendar window.</p>
    @endforelse
    </div>
</div>

<div class="admin-panel">
    <h2 style="font-size:30px">Blocked periods</h2>
    <table class="admin-table"><thead><tr><th>Date</th><th>Time</th><th>Dentist</th><th>Location</th><th>Reason</th></tr></thead><tbody>
    @forelse($blocks as $block)
        <tr><td>{{ $block->blocked_date->format('d M Y') }}</td><td>{{ $block->starts_at ? substr($block->starts_at, 0, 5).'–'.substr($block->ends_at, 0, 5) : 'All day' }}</td><td>{{ $block->doctor->full_name }}</td><td>{{ $block->location?->name ?: 'All locations' }}</td><td>{{ $block->reason ?: 'Unavailable' }}</td></tr>
    @empty
        <tr><td colspan="5">No blocked periods in this range.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endsection
