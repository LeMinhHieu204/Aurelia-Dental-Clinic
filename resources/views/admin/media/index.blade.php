@extends('layouts.admin', ['title' => 'Media Library'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Assets</p><h1>Media library</h1><p class="muted">Only approved image formats are accepted. Record licensing and alt text before using an asset publicly.</p></div></div>
<div class="admin-panel">
    <h2 style="font-size:30px">Upload images</h2>
    <form class="admin-form" method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" style="margin-top:18px">
        @csrf
        <div class="field"><label for="images">Images</label><input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required></div><div class="field"><label for="alt_text">Alt text (applied to each image)</label><input id="alt_text" name="alt_text" required></div><div class="field"><label for="license">License</label><input id="license" name="license" placeholder="Clinic-owned, licensed stock..."></div><div class="field"><label for="attribution">Attribution</label><input id="attribution" name="attribution"></div><div class="field"><label for="source">Source</label><input id="source" name="source"></div><div class="field" style="justify-content:end"><button class="btn btn-primary">Upload images</button></div>
    </form>
</div>
<div class="admin-panel">
    <form class="admin-form" method="GET"><div class="field field-full"><label for="q">Search filename, alt text, or source</label><input id="q" name="q" value="{{ request('q') }}"></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Search</button></div></form>
    <div class="admin-media-grid">
    @forelse($media as $asset)
        @php($assetUrl = Storage::disk($asset->disk ?: 'public')->url($asset->path))
        <article class="admin-media-card"><img src="{{ $assetUrl }}" alt="{{ $asset->alt_text }}"><div class="admin-media-meta"><strong>{{ $asset->filename }}</strong><span>{{ $asset->mime_type }} · {{ number_format(($asset->size_bytes ?: 0) / 1024, 1) }} KB</span><form method="POST" action="{{ route('admin.media.update', $asset) }}">@csrf @method('PATCH')<div class="field"><label for="alt-{{ $asset->id }}">Alt text</label><input id="alt-{{ $asset->id }}" name="alt_text" value="{{ $asset->alt_text }}" required></div><div class="field"><label for="license-{{ $asset->id }}">License</label><input id="license-{{ $asset->id }}" name="license" value="{{ $asset->license }}"></div><input type="hidden" name="is_public" value="1"><button class="btn btn-outline">Save metadata</button></form><form method="POST" action="{{ route('admin.media.replace', $asset) }}" enctype="multipart/form-data" style="margin-top:8px"><label for="replace-{{ $asset->id }}">Replace file</label><input id="replace-{{ $asset->id }}" type="file" name="image" accept="image/jpeg,image/png,image/webp" required><button class="btn btn-outline">Replace image</button></form><form method="POST" action="{{ route('admin.media.destroy', $asset) }}" style="margin-top:8px">@csrf @method('DELETE')<button class="btn btn-outline" onclick="return confirm('Remove this unused media asset?')">Remove</button></form></div></article>
    @empty
        <p class="notice">No uploaded media assets.</p>
    @endforelse
    </div>
    {{ $media->links() }}
</div>
@endsection
