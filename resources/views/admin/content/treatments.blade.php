@extends('layouts.admin', ['title' => 'Treatments'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Content</p><h1>Treatments</h1><p class="muted">Clinical copy, risks, pricing, and FAQs require clinic approval before publication.</p></div></div>
<div class="admin-panel">
    <h2 style="font-size:30px">Add treatment</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.treatments.store') }}" enctype="multipart/form-data" style="margin-top:18px">
        @csrf
        <div class="field"><label>Name</label><input name="name" required></div><div class="field"><label>Short description</label><input name="short_description" required></div>
        <div class="field"><label>Qualified dentists</label><select name="doctor_ids[]" multiple>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>@endforeach</select></div>
        <div class="field"><label>Cover image</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></div><div class="field"><label>Cover image URL</label><input name="cover_image_url" type="url"></div><div class="field"><label>Duration (minutes)</label><input type="number" min="1" name="duration_minutes"></div><div class="field"><label>Starting price</label><input type="number" min="0" step="0.01" name="starting_price"></div><div class="field"><label>Currency</label><input name="price_currency" value="USD" maxlength="3"></div><div class="field"><label>Consultation required</label><select name="consultation_required"><option value="1">Yes</option><option value="0">No</option></select></div><div class="field field-full"><label>Full overview</label><textarea name="overview"></textarea></div><div class="field"><label>Risks and limitations</label><textarea name="risks_limitations"></textarea></div><div class="field"><label>FAQs JSON</label><textarea name="faqs" placeholder='[{"question":"...","answer":"..."}]'></textarea></div><div class="field" style="justify-content:end"><button class="btn btn-primary">Save unpublished</button></div>
    </form>
</div>
<div class="admin-panel">
    <table class="admin-table"><thead><tr><th>Name</th><th>Duration</th><th>Price</th><th>Doctors</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($treatments as $treatment)
        <tr><td>{{ $treatment->name }}<br><span class="muted">{{ $treatment->short_description }}</span></td><td>{{ $treatment->duration_minutes ? $treatment->duration_minutes.' min' : ($treatment->duration_label ?: 'Not set') }}</td><td>{{ $treatment->starting_price ? $treatment->price_currency.' '.number_format($treatment->starting_price, 2) : 'Consultation' }}</td><td>{{ $treatment->doctors->count() }}</td><td><span class="status-pill">{{ $treatment->archived_at ? 'Archived' : ($treatment->is_published ? 'Published' : 'Draft') }}</span></td><td><form method="POST" action="{{ route('admin.treatments.toggle', $treatment) }}" style="display:inline">@csrf @method('PATCH')<button class="btn btn-outline">Publish toggle</button></form><form method="POST" action="{{ route('admin.treatments.archive', $treatment) }}" style="display:inline;margin-left:6px">@csrf @method('PATCH')<button class="btn btn-outline">{{ $treatment->archived_at ? 'Restore' : 'Archive' }}</button></form></td></tr>
    @empty
        <tr><td colspan="6">No treatments yet.</td></tr>
    @endforelse
    </tbody></table>
    {{ $treatments->links() }}
</div>
<div class="admin-panel">
    <h2 style="font-size:30px">Edit treatments</h2>
    <div class="admin-edit-list">
    @forelse($treatments as $treatment)
        <details class="admin-edit-card"><summary>{{ $treatment->name }} <span class="status-pill">{{ $treatment->archived_at ? 'Archived' : ($treatment->is_published ? 'Published' : 'Draft') }}</span></summary>
            <form class="admin-form" method="POST" action="{{ route('admin.treatments.update', $treatment) }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="field"><label>Name</label><input name="name" value="{{ $treatment->name }}" required></div><div class="field"><label>Short description</label><input name="short_description" value="{{ $treatment->short_description }}" required></div>
                <div class="field"><label>Qualified dentists</label><select name="doctor_ids[]" multiple>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected($treatment->doctors->contains('id', $doctor->id))>{{ $doctor->full_name }}</option>@endforeach</select></div>
                <div class="field"><label>Cover image upload</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></div><div class="field"><label>Cover image URL</label><input name="cover_image_url" type="url" value="{{ $treatment->cover_image_url }}"></div><div class="field"><label>Duration (minutes)</label><input type="number" min="1" name="duration_minutes" value="{{ $treatment->duration_minutes }}"></div><div class="field"><label>Duration label</label><input name="duration_label" value="{{ $treatment->duration_label }}"></div><div class="field"><label>Starting price</label><input type="number" min="0" step="0.01" name="starting_price" value="{{ $treatment->starting_price }}"></div><div class="field"><label>Currency</label><input name="price_currency" value="{{ $treatment->price_currency ?: 'USD' }}" maxlength="3"></div><div class="field"><label>Consultation required</label><select name="consultation_required"><option value="1" @selected($treatment->consultation_required)>Yes</option><option value="0" @selected(! $treatment->consultation_required)>No</option></select></div><div class="field"><label>Publication</label><select name="is_published"><option value="1" @selected($treatment->is_published)>Published</option><option value="0" @selected(! $treatment->is_published)>Draft</option></select></div><div class="field"><label>Approval required</label><select name="requires_approval"><option value="1" @selected($treatment->requires_approval)>Yes</option><option value="0" @selected(! $treatment->requires_approval)>Approved</option></select></div><div class="field field-full"><label>Overview</label><textarea name="overview">{{ $treatment->overview }}</textarea></div><div class="field"><label>Process</label><textarea name="process">{{ $treatment->process }}</textarea></div><div class="field"><label>Risks and limitations</label><textarea name="risks_limitations">{{ $treatment->risks_limitations }}</textarea></div><div class="field"><label>Aftercare</label><textarea name="aftercare">{{ $treatment->aftercare }}</textarea></div><div class="field"><label>FAQs JSON</label><textarea name="faqs">{{ $treatment->faqs ? json_encode($treatment->faqs) : '' }}</textarea></div><div class="field"><label>Display order</label><input type="number" min="0" name="display_order" value="{{ $treatment->display_order }}"></div><div class="field" style="justify-content:end"><button class="btn btn-primary">Save changes</button></div>
            </form>
        </details>
    @empty
        <p>No treatments to edit.</p>
    @endforelse
    </div>
</div>
@endsection
