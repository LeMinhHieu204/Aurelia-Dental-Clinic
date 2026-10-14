<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContentSection;
use App\Models\Doctor;
use App\Models\MediaAsset;
use App\Models\Treatment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MediaAsset::class);

        $query = MediaAsset::with('uploader')->latest();
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('filename', 'like', $term)->orWhere('alt_text', 'like', $term)->orWhere('source', 'like', $term));
        }
        return view('admin.media.index', ['media' => $query->paginate(24)->withQueryString()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MediaAsset::class);

        $data = $request->validate([
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=3200,max_height=3200'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=3200,max_height=3200'],
            'alt_text' => ['required', 'string', 'max:240'],
            'license' => ['nullable', 'string', 'max:160'],
            'attribution' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:240'],
        ]);
        $files = $request->file('images', []);
        if (! is_array($files)) $files = [];
        if ($request->hasFile('image')) $files[] = $request->file('image');
        if ($files === []) throw ValidationException::withMessages(['images' => 'Select at least one image to upload.']);

        $storedPaths = [];
        try {
            DB::transaction(function () use ($files, $data, &$storedPaths): void {
                foreach ($files as $file) {
                    $filename = Str::uuid()->toString().'.'.$file->extension();
                    $path = Storage::disk('public')->putFileAs('media', $file, $filename);
                    if (! is_string($path)) throw new \RuntimeException('The media file could not be stored.');
                    $storedPaths[] = $path;
                    $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
                    $asset = MediaAsset::create([
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'disk' => 'public',
                        'mime_type' => $file->getMimeType(),
                        'size_bytes' => $file->getSize(),
                        'width' => $dimensions[0],
                        'height' => $dimensions[1],
                        'alt_text' => $data['alt_text'],
                        'license' => $data['license'] ?? null,
                        'attribution' => $data['attribution'] ?? null,
                        'source' => $data['source'] ?? null,
                        'sha256' => hash_file('sha256', $file->getRealPath()),
                        'is_public' => true,
                        'uploaded_by' => auth()->id(),
                    ]);
                    AuditLog::record('media.created', $asset, ['filename' => $asset->filename, 'mime_type' => $asset->mime_type, 'size_bytes' => $asset->size_bytes]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $storedPath) Storage::disk('public')->delete($storedPath);
            throw $exception;
        }

        return back()->with('success', count($files).' media asset(s) uploaded.');
    }

    public function update(Request $request, MediaAsset $media): RedirectResponse
    {
        $this->authorize('update', $media);

        $data = $request->validate(['alt_text' => ['required', 'string', 'max:240'], 'license' => ['nullable', 'string', 'max:160'], 'attribution' => ['nullable', 'string', 'max:500'], 'source' => ['nullable', 'string', 'max:240'], 'is_public' => ['required', 'boolean']]);
        $media->update($data);
        AuditLog::record('media.updated', $media, ['fields' => array_keys($data)]);
        return back()->with('success', 'Media metadata updated.');
    }

    public function replace(Request $request, MediaAsset $media): RedirectResponse
    {
        $this->authorize('update', $media);

        $data = $request->validate(['image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=3200,max_height=3200']]);
        $file = $data['image'];
        $oldPath = $media->path;
        $oldUrl = Storage::disk($media->disk ?: 'public')->url($oldPath);
        $newFilename = Str::uuid()->toString().'.'.$file->extension();
        $newPath = Storage::disk($media->disk ?: 'public')->putFileAs('media', $file, $newFilename);
        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
        $newUrl = Storage::disk($media->disk ?: 'public')->url($newPath);

        try {
            DB::transaction(function () use ($media, $file, $dimensions, $newPath, $newUrl, $oldUrl): void {
                $media->update(['filename' => $file->getClientOriginalName(), 'path' => $newPath, 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'width' => $dimensions[0], 'height' => $dimensions[1], 'sha256' => hash_file('sha256', $file->getRealPath())]);
                Doctor::where('portrait_url', $oldUrl)->update(['portrait_url' => $newUrl]);
                Treatment::where('cover_image_url', $oldUrl)->update(['cover_image_url' => $newUrl]);
                \App\Models\ContentSection::where('image_url', $oldUrl)->update(['image_url' => $newUrl]);
                AuditLog::record('media.replaced', $media, ['filename' => $media->filename, 'mime_type' => $media->mime_type, 'size_bytes' => $media->size_bytes]);
            });
        } catch (\Throwable $exception) {
            Storage::disk($media->disk ?: 'public')->delete($newPath);
            throw $exception;
        }

        if ($oldPath !== $newPath) Storage::disk($media->disk ?: 'public')->delete($oldPath);
        return back()->with('success', 'Media asset replaced and linked usages updated.');
    }

    public function destroy(MediaAsset $media): RedirectResponse
    {
        $this->authorize('delete', $media);

        $url = Storage::disk($media->disk ?: 'public')->url($media->path);
        if (Doctor::where('portrait_url', $url)->exists() || Treatment::where('cover_image_url', $url)->exists() || ContentSection::where('image_url', $url)->exists()) {
            return back()->withErrors(['media' => 'This asset is still referenced by published content and cannot be removed.']);
        }
        AuditLog::record('media.deleted', null, ['media_id' => $media->id, 'filename' => $media->filename]);
        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();
        return back()->with('success', 'Media asset removed.');
    }
}
