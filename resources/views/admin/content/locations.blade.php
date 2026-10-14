@extends('layouts.admin', ['title' => 'Locations'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Operations</p><h1>Locations</h1><p class="muted">Keep demo branches distinguishable until clinic identity, address, hours, supported treatments, and map details are verified.</p></div></div>
<div class="admin-panel">
    <h2 style="font-size:30px">Add location</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.locations.store') }}" style="margin-top:18px">
        @csrf
        <div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Country</label><input name="country"></div><div class="field"><label>City</label><input name="city"></div><div class="field field-full"><label>Public address</label><input name="address"></div><div class="field"><label>Phone</label><input name="phone"></div><div class="field"><label>Email</label><input name="email" type="email"></div><div class="field"><label>Time zone</label><input name="timezone" value="UTC" required></div><div class="field"><label>Map URL</label><input name="map_url" type="url"></div><div class="field field-full"><label>Opening hours JSON</label><textarea name="opening_hours" placeholder='{"monday":{"open":"08:00","close":"17:00"}}'></textarea></div><div class="field field-full"><label>Closure dates or weekdays JSON</label><textarea name="closure_days" placeholder='["2026-12-25","sunday"]'></textarea></div><div class="field"><label>Assign dentists</label><select name="doctor_ids[]" multiple>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>@endforeach</select></div><div class="field"><label>Supported treatments</label><select name="treatment_ids[]" multiple>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}">{{ $treatment->name }}</option>@endforeach</select></div><div class="field" style="justify-content:end"><button class="btn btn-primary">Save inactive location</button></div>
    </form>
</div>
<div class="admin-panel">
    <table class="admin-table"><thead><tr><th>Name</th><th>City/country</th><th>Time zone</th><th>Doctors</th><th>Treatments</th><th>Visibility</th><th>Demo</th></tr></thead><tbody>
    @forelse($locations as $location)
        <tr><td>{{ $location->name }}</td><td>{{ $location->city }}{{ $location->country ? ', '.$location->country : '' }}</td><td>{{ $location->timezone }}</td><td>{{ $location->doctors->count() }}</td><td>{{ $location->treatments->count() }}</td><td><span class="status-pill">{{ $location->is_active && $location->public_visibility ? 'Public' : 'Inactive/private' }}</span></td><td>{{ $location->is_demo ? 'Yes' : 'No' }}</td></tr>
    @empty
        <tr><td colspan="7">No locations yet.</td></tr>
    @endforelse
    </tbody></table>
    {{ $locations->links() }}
</div>
<div class="admin-panel">
    <h2 style="font-size:30px">Edit locations</h2>
    <div class="admin-edit-list">
    @forelse($locations as $location)
        <details class="admin-edit-card"><summary>{{ $location->name }} <span class="status-pill">{{ $location->is_active ? 'Active' : 'Inactive' }}</span></summary>
            <form class="admin-form" method="POST" action="{{ route('admin.locations.update', $location) }}">
                @csrf @method('PATCH')
                <div class="field"><label>Name</label><input name="name" value="{{ $location->name }}" required></div><div class="field"><label>Country</label><input name="country" value="{{ $location->country }}"></div><div class="field"><label>City</label><input name="city" value="{{ $location->city }}"></div><div class="field field-full"><label>Public address</label><input name="address" value="{{ $location->address }}"></div><div class="field"><label>Phone</label><input name="phone" value="{{ $location->phone }}"></div><div class="field"><label>Email</label><input name="email" type="email" value="{{ $location->email }}"></div><div class="field"><label>Time zone</label><input name="timezone" value="{{ $location->timezone }}" required></div><div class="field"><label>Map URL</label><input name="map_url" type="url" value="{{ $location->map_url }}"></div><div class="field field-full"><label>Opening hours JSON</label><textarea name="opening_hours">{{ $location->opening_hours ? json_encode($location->opening_hours) : '' }}</textarea></div><div class="field field-full"><label>Closure dates or weekdays JSON</label><textarea name="closure_days">{{ $location->closure_days ? json_encode($location->closure_days) : '' }}</textarea></div><div class="field"><label>Active</label><select name="is_active"><option value="1" @selected($location->is_active)>Active</option><option value="0" @selected(! $location->is_active)>Inactive</option></select></div><div class="field"><label>Public visibility</label><select name="public_visibility"><option value="1" @selected($location->public_visibility)>Public</option><option value="0" @selected(! $location->public_visibility)>Private</option></select></div><div class="field"><label>Dentists</label><select name="doctor_ids[]" multiple>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected($location->doctors->contains('id', $doctor->id))>{{ $doctor->full_name }}</option>@endforeach</select></div><div class="field"><label>Supported treatments</label><select name="treatment_ids[]" multiple>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}" @selected($location->treatments->contains('id', $treatment->id))>{{ $treatment->name }}</option>@endforeach</select></div><div class="field" style="justify-content:end"><button class="btn btn-primary">Save changes</button></div>
            </form>
        </details>
    @empty
        <p>No locations to edit.</p>
    @endforelse
    </div>
</div>
@endsection
