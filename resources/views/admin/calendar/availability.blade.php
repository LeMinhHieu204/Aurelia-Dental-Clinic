@extends('layouts.admin', ['title' => 'Availability'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Scheduling</p><h1>Doctor availability</h1><p class="muted">Configure weekly hours, date-specific exceptional hours, and unavailable periods. These rules protect confirmed appointments.</p></div><a class="btn btn-outline" href="{{ route('admin.calendar') }}">Open calendar</a></div>

<div class="admin-panel">
    <form class="admin-form" method="GET"><div class="field"><label for="doctor_id">Dentist</label><select id="doctor_id" name="doctor_id">@foreach($doctors as $item)<option value="{{ $item->id }}" @selected($doctor?->id === $item->id)>{{ $item->full_name }}</option>@endforeach</select></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Load schedule</button></div></form>
</div>

@if($doctor)
<div class="admin-panel">
    <h2 style="font-size:30px">Working hours — {{ $doctor->full_name }}</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.availability.store') }}" style="margin-top:18px">
        @csrf <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <div class="field"><label for="weekday">Weekly day (optional)</label><select id="weekday" name="weekday"><option value="">Use an exceptional date instead</option>@foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day => $label)<option value="{{ $day }}">{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label for="available_date">Exceptional date (optional)</label><input id="available_date" type="date" name="available_date" min="{{ today()->toDateString() }}"><small>When set, this date overrides weekly hours.</small></div>
        <div class="field"><label for="starts_at">Starts</label><input id="starts_at" type="time" name="starts_at" required></div>
        <div class="field"><label for="ends_at">Ends</label><input id="ends_at" type="time" name="ends_at" required></div>
        <div class="field"><label for="timezone">Time zone</label><input id="timezone" name="timezone" value="{{ $doctor->locations->first()?->timezone ?: 'UTC' }}" required></div>
        <div class="field"><label for="location_id">Location</label><select id="location_id" name="location_id"><option value="">All assigned locations</option>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></div>
        <div class="field" style="justify-content:end"><button class="btn btn-primary">Add hours</button></div>
    </form>
    <table class="admin-table"><thead><tr><th>Schedule</th><th>Hours</th><th>Location</th><th>Time zone</th><th></th></tr></thead><tbody>
    @forelse($availability as $slot)
        <tr><td>{{ $slot->available_date ? 'Exceptional · '.$slot->available_date->format('d M Y') : 'Weekly · '.(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][$slot->weekday]) }}</td><td>{{ substr($slot->starts_at, 0, 5) }}–{{ substr($slot->ends_at, 0, 5) }}</td><td>{{ $slot->location?->name ?: 'All assigned locations' }}</td><td>{{ $slot->timezone }}</td><td><form method="POST" action="{{ route('admin.availability.destroy', $slot) }}">@csrf @method('DELETE')<button class="btn btn-outline">Remove</button></form></td></tr>
    @empty
        <tr><td colspan="5">No working hours configured.</td></tr>
    @endforelse
    </tbody></table>
</div>

<div class="admin-panel">
    <h2 style="font-size:30px">Unavailable dates and leave</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.availability.blocks.store') }}">
        @csrf <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
        <div class="field"><label for="blocked_date">Date</label><input id="blocked_date" type="date" name="blocked_date" min="{{ today()->toDateString() }}" required></div>
        <div class="field"><label for="block_starts_at">Starts (optional)</label><input id="block_starts_at" type="time" name="starts_at"></div>
        <div class="field"><label for="block_ends_at">Ends (optional)</label><input id="block_ends_at" type="time" name="ends_at"></div>
        <div class="field"><label for="reason">Reason</label><input id="reason" name="reason" placeholder="Annual leave, clinic closure..."></div>
        <div class="field" style="justify-content:end"><button class="btn btn-primary">Block period</button></div>
    </form>
    <table class="admin-table"><thead><tr><th>Date</th><th>Time</th><th>Reason</th><th></th></tr></thead><tbody>
    @forelse($blocks as $block)
        <tr><td>{{ $block->blocked_date->format('d M Y') }}</td><td>{{ $block->starts_at ? substr($block->starts_at, 0, 5).'–'.substr($block->ends_at, 0, 5) : 'All day' }}</td><td>{{ $block->reason ?: 'Unavailable' }}</td><td><form method="POST" action="{{ route('admin.availability.blocks.destroy', $block) }}">@csrf @method('DELETE')<button class="btn btn-outline">Remove</button></form></td></tr>
    @empty
        <tr><td colspan="4">No upcoming blocked periods.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endif
@endsection
