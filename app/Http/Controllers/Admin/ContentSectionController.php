<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContentSection;
use App\Models\MediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentSectionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ContentSection::class);

        return view('admin.content.sections', ['sections' => ContentSection::orderBy('sort_order')->orderBy('key')->get(), 'mediaAssets' => MediaAsset::where('is_public', true)->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ContentSection::class);

        $data = $request->validate(['key' => ['required', 'regex:/^[A-Za-z0-9._-]+$/', 'max:100', 'unique:content_sections,key'], 'title' => ['required', 'string', 'max:180'], 'body' => ['nullable', 'string', 'max:10000'], 'image_url' => ['nullable', 'string', 'max:500'], 'seo_title' => ['nullable', 'string', 'max:180'], 'seo_description' => ['nullable', 'string', 'max:320']]);
        $section = ContentSection::create($data + ['is_published' => false, 'sort_order' => ContentSection::max('sort_order') + 1]);
        AuditLog::record('content_section.created', $section);
        return back()->with('success', 'Content section created as a draft.');
    }

    public function update(Request $request, ContentSection $section): RedirectResponse
    {
        $this->authorize('update', $section);

        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'body' => ['nullable', 'string', 'max:10000'], 'image_url' => ['nullable', 'string', 'max:500'], 'seo_title' => ['nullable', 'string', 'max:180'], 'seo_description' => ['nullable', 'string', 'max:320'], 'is_published' => ['required', 'boolean'], 'sort_order' => ['required', 'integer', 'min:0']]);
        $data['published_at'] = $data['is_published'] ? ($section->published_at ?: now()) : null;
        $data['updated_by'] = auth()->id();
        $section->update($data);
        AuditLog::record('content_section.updated', $section, ['published' => (bool) $data['is_published']]);
        return back()->with('success', 'Content section updated.');
    }
}
