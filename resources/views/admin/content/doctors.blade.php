@extends('layouts.admin', ['title' => 'Doctors'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Content</p><h1>Doctors</h1><p class="muted">Profiles remain drafts until verified clinic credentials and portraits are approved.</p></div></div>
<div class="admin-panel"><form class="admin-form" method="GET"><div class="field"><label for="q">Search doctors</label><input id="q" name="q" value="{{ request('q') }}" placeholder="Name or professional title"></div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><option value="active" @selected(request('status') === 'active')>Active</option><option value="inactive" @selected(request('status') === 'inactive')>Inactive</option></select></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Filter</button></div></form></div>
<div class="admin-panel">
    <h2 style="font-size:30px">Add doctor</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.doctors.store') }}" enctype="multipart/form-data" style="margin-top:18px">
        @csrf
        <div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" required></div>
        <div class="field"><label for="professional_title">Professional title</label><input id="professional_title" name="professional_title" required></div>
        <div class="field"><label for="user_id">Login account</label><select id="user_id" name="user_id"><option value="">No doctor login assigned</option>@foreach($doctorUsers as $doctorUser)<option value="{{ $doctorUser->id }}">{{ $doctorUser->name }} — {{ $doctorUser->email }}</option>@endforeach</select></div>
        <div class="field"><label for="specialties">Specialties</label><input id="specialties" name="specialties" placeholder="Comma-separated"></div>
        <div class="field"><label for="portrait">Portrait upload</label><input id="portrait" type="file" name="portrait" accept="image/jpeg,image/png,image/webp"></div>
        <div class="field"><label for="portrait_url">Portrait URL</label><input id="portrait_url" name="portrait_url" type="url"></div>
        <div class="field field-full"><label for="bio">Biography</label><textarea id="bio" name="bio"></textarea></div>
        <div class="field"><label for="qualifications">Verified qualifications</label><textarea id="qualifications" name="qualifications"></textarea></div>
        <div class="field"><label for="registration_identifier">Registration number</label><input id="registration_identifier" name="registration_identifier"></div>
        <div class="field"><label for="languages">Languages</label><input id="languages" name="languages"></div>
        <div class="field"><label for="display_order">Display order</label><input id="display_order" type="number" min="0" name="display_order" value="0"></div>
        <div class="field"><label for="location_ids">Locations</label><select id="location_ids" name="location_ids[]" multiple>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></div>
        <div class="field"><label for="treatment_ids">Treatments</label><select id="treatment_ids" name="treatment_ids[]" multiple>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}">{{ $treatment->name }}</option>@endforeach</select></div>
        <div class="field" style="justify-content:end"><button class="btn btn-primary">Save unpublished doctor</button></div>
    </form>
</div>
<div class="admin-panel">
    <table class="admin-table"><thead><tr><th>Name</th><th>Role</th><th>Assignments</th><th>Status</th><th>Demo</th><th></th></tr></thead><tbody>
    @forelse($doctors as $doctor)
        <tr><td>{{ $doctor->full_name }}</td><td>{{ $doctor->professional_title }}</td><td>{{ $doctor->locations->count() }} locations · {{ $doctor->treatments->count() }} treatments</td><td><span class="status-pill">{{ $doctor->archived_at ? 'Archived' : ($doctor->is_active ? ($doctor->is_published ? 'Published' : 'Draft') : 'Inactive') }}</span></td><td>{{ $doctor->is_demo ? 'Yes' : 'No' }}</td><td>@if($doctor->archived_at)<form method="POST" action="{{ route('admin.doctors.archive', $doctor) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-outline">Restore draft</button></form>@else<form method="POST" action="{{ route('admin.doctors.toggle', $doctor) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-outline">Publish toggle</button></form>@if($doctor->is_active)<form method="POST" action="{{ route('admin.doctors.archive', $doctor) }}" style="display:inline;margin-left:6px">@csrf @method('PATCH')<button class="btn btn-outline">Archive</button></form>@endif @endif</td></tr>
    @empty
        <tr><td colspan="6">No doctors yet.</td></tr>
    @endforelse
    </tbody></table>
    {{ $doctors->links() }}
</div>
<div class="admin-panel">
    <h2 style="font-size:30px">Edit profiles</h2>
    <div class="admin-edit-list">
    @forelse($doctors as $doctor)
        <details class="admin-edit-card"><summary>{{ $doctor->full_name }} <span class="status-pill">{{ $doctor->is_active ? 'Active' : 'Inactive' }}</span></summary>
            <form class="admin-form" method="POST" action="{{ route('admin.doctors.update', $doctor) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="field"><label>Full name</label><input name="full_name" value="{{ $doctor->full_name }}" required></div>
                <div class="field"><label>Professional title</label><input name="professional_title" value="{{ $doctor->professional_title }}" required></div>
                <div class="field"><label>Login account</label><select name="user_id"><option value="">No doctor login assigned</option>@foreach($doctorUsers as $doctorUser)<option value="{{ $doctorUser->id }}" @selected($doctor->user_id === $doctorUser->id)>{{ $doctorUser->name }} — {{ $doctorUser->email }}</option>@endforeach</select></div>
                <div class="field"><label>Specialties</label><input name="specialties" value="{{ $doctor->specialties->pluck('name')->implode(', ') }}"></div>
                <div class="field"><label>Portrait upload</label><input type="file" name="portrait" accept="image/jpeg,image/png,image/webp"></div>
                <div class="field"><label>Portrait URL</label><input name="portrait_url" type="url" value="{{ $doctor->portrait_url }}"></div>
                <div class="field field-full"><label>Biography</label><textarea name="bio">{{ $doctor->bio }}</textarea></div>
                <div class="field"><label>Qualifications</label><textarea name="qualifications">{{ $doctor->qualifications }}</textarea></div>
                <div class="field"><label>Registration number</label><input name="registration_identifier" value="{{ $doctor->registration_identifier }}"></div>
                <div class="field"><label>Languages</label><input name="languages" value="{{ $doctor->languages }}"></div>
                <div class="field"><label>Display order</label><input type="number" min="0" name="display_order" value="{{ $doctor->display_order }}"></div>
                <div class="field"><label>Active</label><select name="is_active"><option value="1" @selected($doctor->is_active)>Active</option><option value="0" @selected(! $doctor->is_active)>Inactive</option></select></div>
                <div class="field"><label>Published</label><select name="is_published"><option value="1" @selected($doctor->is_published)>Published</option><option value="0" @selected(! $doctor->is_published)>Draft</option></select></div>
                <div class="field"><label>Locations</label><select name="location_ids[]" multiple>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($doctor->locations->contains('id', $location->id))>{{ $location->name }}</option>@endforeach</select></div>
                <div class="field"><label>Treatments</label><select name="treatment_ids[]" multiple>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}" @selected($doctor->treatments->contains('id', $treatment->id))>{{ $treatment->name }}</option>@endforeach</select></div>
                <div class="field" style="justify-content:end"><button class="btn btn-primary">Save changes</button></div>
            </form>
        </details>
    @empty
        <p>No profiles to edit.</p>
    @endforelse
    </div>
</div>
@endsection
