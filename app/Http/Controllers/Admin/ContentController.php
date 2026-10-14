<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\MediaAsset;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function doctors(Request $request): View
    {
        $doctorQuery = Doctor::with(['specialties', 'locations', 'treatments'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $query->where(fn ($nested) => $nested->where('full_name', 'like', $term)->orWhere('professional_title', 'like', $term));
            })
            ->when($request->filled('status'), fn ($query) => $request->string('status')->toString() === 'active' ? $query->where('is_active', true) : $query->where('is_active', false));

        return view('admin.content.doctors', [
            'doctors' => $doctorQuery->latest()->paginate(20)->withQueryString(),
            'doctorUsers' => User::whereHas('role', fn ($query) => $query->where('name', 'doctor'))->orderBy('name')->get(),
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
            'treatments' => Treatment::orderBy('name')->get(),
        ]);
    }

    public function treatments(): View
    {
        return view('admin.content.treatments', ['treatments' => Treatment::with(['doctors', 'locations'])->orderBy('display_order')->orderBy('name')->paginate(20), 'doctors' => Doctor::where('is_active', true)->whereNull('archived_at')->orderBy('full_name')->get()]);
    }

    public function locations(): View
    {
        return view('admin.content.locations', ['locations' => Location::with(['doctors', 'treatments'])->latest()->paginate(20), 'doctors' => Doctor::where('is_active', true)->whereNull('archived_at')->orderBy('full_name')->get(), 'treatments' => Treatment::orderBy('name')->get()]);
    }

    public function reviews(): View
    {
        return view('admin.content.reviews', ['reviews' => Review::latest('review_date')->paginate(25)]);
    }

    public function inquiries(Request $request): View
    {
        $query = Inquiry::with('assignee')->latest();
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('message', 'like', $term));
        }
        return view('admin.content.inquiries', [
            'inquiries' => $query->paginate(25)->withQueryString(),
            'staff' => User::whereHas('role', fn ($q) => $q->whereIn('name', ['super_admin', 'admin', 'staff', 'receptionist']))->orderBy('name')->get(),
        ]);
    }

    public function settings(): View
    {
        $defaults = [
            'support_phone' => '+1 (800) 452-2876',
            'general_email' => 'hello@aureliadental.example',
            'tagline' => 'Thoughtful dental care, designed around you',
            'footer_note' => 'Demo content — replace with approved clinic details before launch.',
        ];
        $settings = collect($defaults)->mapWithKeys(fn (string $default, string $key) => [$key => SiteSetting::where('key', $key)->value('value') ?? $default]);

        return view('admin.content.settings', compact('settings'));
    }

    public function storeTreatment(Request $request): RedirectResponse
    {
        $data = $this->treatmentData($request);
        $data += ['slug' => $this->uniqueSlug($data['name'], Treatment::class), 'is_demo' => true, 'is_published' => false, 'requires_approval' => true];
        unset($data['cover_image']);
        $treatment = Treatment::create($data);
        $this->syncTreatmentRelationships($treatment, $request);
        $this->storeImageIfPresent($request, 'cover_image', 'treatments', $treatment, 'cover_image_url');
        AuditLog::record('treatment.created', $treatment);
        return back()->with('success', 'Treatment saved as an unpublished demo record.');
    }

    public function updateTreatment(Request $request, Treatment $treatment): RedirectResponse
    {
        $data = $this->treatmentData($request, false);
        $data['slug'] = $this->uniqueSlug($data['name'], Treatment::class, $treatment->id);
        unset($data['cover_image']);
        $treatment->update($data);
        $this->syncTreatmentRelationships($treatment, $request);
        $this->storeImageIfPresent($request, 'cover_image', 'treatments', $treatment, 'cover_image_url');
        AuditLog::record('treatment.updated', $treatment);
        return back()->with('success', 'Treatment updated.');
    }

    public function storeDoctor(Request $request): RedirectResponse
    {
        $data = $this->doctorData($request);
        $data += ['slug' => $this->uniqueSlug($data['full_name'], Doctor::class), 'is_demo' => true, 'is_published' => false, 'is_active' => true];
        unset($data['portrait']);
        $doctor = Doctor::create($data);
        $this->syncDoctorRelationships($doctor, $request);
        $this->storeImageIfPresent($request, 'portrait', 'doctors', $doctor, 'portrait_url');
        AuditLog::record('doctor.created', $doctor);
        return back()->with('success', 'Doctor saved as an unpublished demo profile.');
    }

    public function updateDoctor(Request $request, Doctor $doctor): RedirectResponse
    {
        $data = $this->doctorData($request, false, $doctor);
        $data['slug'] = $this->uniqueSlug($data['full_name'], Doctor::class, $doctor->id);
        if (! $data['is_active'] || $doctor->archived_at) $data['is_published'] = false;
        unset($data['portrait']);
        $doctor->update($data);
        $this->syncDoctorRelationships($doctor, $request);
        $this->storeImageIfPresent($request, 'portrait', 'doctors', $doctor, 'portrait_url');
        AuditLog::record('doctor.updated', $doctor);
        return back()->with('success', 'Doctor profile updated.');
    }

    public function storeLocation(Request $request): RedirectResponse
    {
        $data = $this->locationData($request);
        $data += ['slug' => $this->uniqueSlug($data['name'], Location::class), 'is_demo' => true, 'is_active' => false, 'public_visibility' => false];
        $location = Location::create($data);
        if ($request->has('doctor_ids')) $location->doctors()->sync($request->input('doctor_ids', []));
        if ($request->has('treatment_ids')) $location->treatments()->sync($request->input('treatment_ids', []));
        AuditLog::record('location.created', $location);
        return back()->with('success', 'Location saved as an inactive demo record.');
    }

    public function updateLocation(Request $request, Location $location): RedirectResponse
    {
        $data = $this->locationData($request, false);
        $data['slug'] = $this->uniqueSlug($data['name'], Location::class, $location->id);
        $location->update($data);
        if ($request->has('doctor_ids')) $location->doctors()->sync($request->input('doctor_ids', []));
        if ($request->has('treatment_ids')) $location->treatments()->sync($request->input('treatment_ids', []));
        AuditLog::record('location.updated', $location);
        return back()->with('success', 'Location updated.');
    }

    public function updateInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:NEW,IN_PROGRESS,RESOLVED,SPAM,CONTACTED,CLOSED'],
            'internal_notes' => ['nullable', 'string', 'max:4000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);
        $inquiry->update($data);
        AuditLog::record('inquiry.updated', $inquiry, ['status' => $data['status'], 'assigned_to' => $data['assigned_to'] ?? null]);
        return back()->with('success', 'Inquiry updated.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'support_phone' => ['required', 'string', 'max:40'],
            'general_email' => ['required', 'email', 'max:180'],
            'tagline' => ['required', 'string', 'max:160'],
            'footer_note' => ['required', 'string', 'max:240'],
        ]);
        foreach ($data as $key => $value) {
            $setting = SiteSetting::updateOrCreate(['key' => $key], ['value' => $value, 'is_public' => true]);
            AuditLog::record('site_setting.updated', $setting, ['key' => $key]);
        }
        return back()->with('success', 'Website settings updated.');
    }

    public function toggleDoctor(Doctor $doctor): RedirectResponse
    {
        if (! $doctor->is_active || $doctor->archived_at) {
            return back()->withErrors(['doctor' => 'Activate and restore the doctor profile before publishing it.']);
        }
        $doctor->update(['is_published' => ! $doctor->is_published]);
        AuditLog::record('doctor.visibility_changed', $doctor, ['is_published' => $doctor->is_published]);
        return back()->with('success', 'Doctor visibility updated.');
    }

    public function archiveDoctor(Doctor $doctor): RedirectResponse
    {
        if ($doctor->archived_at) {
            $doctor->update(['archived_at' => null, 'is_active' => false, 'is_published' => false]);
            AuditLog::record('doctor.restored', $doctor);
            return back()->with('success', 'Doctor restored as an inactive draft without changing historical appointments.');
        }
        if ($doctor->appointments()->where('status', 'CONFIRMED')->whereDate('preferred_date', '>=', today())->exists()) {
            return back()->withErrors(['doctor' => 'Reassign future confirmed appointments before archiving this doctor.']);
        }
        $doctor->update(['is_active' => false, 'is_published' => false, 'archived_at' => now()]);
        AuditLog::record('doctor.archived', $doctor);
        return back()->with('success', 'Doctor archived without deleting historical appointments.');
    }

    public function toggleTreatment(Treatment $treatment): RedirectResponse
    {
        if (! $treatment->is_published && $treatment->archived_at) {
            return back()->withErrors(['treatment' => 'Restore the archived treatment before publishing it.']);
        }
        $treatment->update(['is_published' => ! $treatment->is_published]);
        AuditLog::record('treatment.visibility_changed', $treatment, ['is_published' => $treatment->is_published]);
        return back()->with('success', 'Treatment visibility updated.');
    }

    public function toggleTreatmentArchive(Treatment $treatment): RedirectResponse
    {
        $archiving = ! $treatment->archived_at;
        $treatment->update(['archived_at' => $archiving ? now() : null, 'is_published' => $archiving ? false : $treatment->is_published]);
        AuditLog::record($archiving ? 'treatment.archived' : 'treatment.restored', $treatment);
        return back()->with('success', $archiving ? 'Treatment archived without deleting historical relationships.' : 'Treatment restored as a draft.');
    }

    public function toggleReview(Review $review): RedirectResponse
    {
        if (! $review->is_published && ! $review->permission_granted) {
            return back()->withErrors(['review' => 'Record written permission before publishing this review.']);
        }
        $review->update(['is_published' => ! $review->is_published]);
        AuditLog::record('review.visibility_changed', $review, ['is_published' => $review->is_published]);
        return back()->with('success', 'Review visibility updated.');
    }

    public function updateReview(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate(['approved_name' => ['required', 'string', 'max:120'], 'location_label' => ['nullable', 'string', 'max:120'], 'body' => ['required', 'string', 'max:2000'], 'rating' => ['nullable', 'integer', 'between:1,5'], 'review_date' => ['nullable', 'date'], 'source' => ['nullable', 'string', 'max:160'], 'permission_granted' => ['required', 'boolean'], 'is_published' => ['required', 'boolean']]);
        if ($data['is_published'] && ! $data['permission_granted']) {
            throw ValidationException::withMessages(['is_published' => 'A review needs recorded permission before publication.']);
        }
        $review->update($data);
        AuditLog::record('review.updated', $review, ['published' => (bool) $data['is_published'], 'permission_granted' => (bool) $data['permission_granted']]);
        return back()->with('success', 'Review metadata updated.');
    }

    public function destroyReview(Review $review): RedirectResponse
    {
        $review->update(['approved_name' => 'Anonymous patient', 'body' => '[Review removed under the clinic retention policy.]', 'is_published' => false, 'permission_granted' => false]);
        AuditLog::record('review.anonymized', $review);
        return back()->with('success', 'Review anonymized and unpublished.');
    }

    private function doctorData(Request $request, bool $creating = true, ?Doctor $doctor = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'professional_title' => ['required', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'qualifications' => ['nullable', 'string', 'max:2000'],
            'registration_identifier' => ['nullable', 'string', 'max:160'],
            'languages' => ['nullable', 'string', 'max:240'],
            'user_id' => ['nullable', 'integer', 'exists:users,id', Rule::unique('doctors', 'user_id')->ignore($doctor?->id)],
            'portrait_url' => ['nullable', 'url', 'max:500'],
            'portrait' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=3200,max_height=3200'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => [$creating ? 'nullable' : 'required', 'boolean'],
            'is_published' => [$creating ? 'nullable' : 'required', 'boolean'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'treatment_ids' => ['nullable', 'array'],
            'treatment_ids.*' => ['integer', 'exists:treatments,id'],
        ]);
        if (! empty($data['user_id']) && ! User::whereKey($data['user_id'])->whereHas('role', fn ($query) => $query->where('name', 'doctor'))->exists()) {
            throw ValidationException::withMessages(['user_id' => 'Only users with the doctor role can be assigned to a dentist profile.']);
        }
        return $data;
    }

    private function treatmentData(Request $request, bool $creating = true): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'short_description' => ['required', 'string', 'max:500'],
            'overview' => ['nullable', 'string', 'max:10000'],
            'process' => ['nullable', 'string', 'max:10000'],
            'benefits' => ['nullable', 'string', 'max:10000'],
            'risks_limitations' => ['nullable', 'string', 'max:10000'],
            'aftercare' => ['nullable', 'string', 'max:10000'],
            'faqs' => ['nullable', 'string', 'max:10000'],
            'duration_label' => ['nullable', 'string', 'max:80'],
            'duration_minutes' => ['nullable', 'integer', 'between:1,1440'],
            'starting_price' => ['nullable', 'numeric', 'min:0'],
            'price_currency' => ['nullable', 'string', 'size:3'],
            'consultation_required' => ['nullable', 'boolean'],
            'cover_image_url' => ['nullable', 'url', 'max:500'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=3200,max_height=3200'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'requires_approval' => ['nullable', 'boolean'],
            'is_published' => [$creating ? 'nullable' : 'required', 'boolean'],
            'doctor_ids' => ['nullable', 'array'],
            'doctor_ids.*' => ['integer', 'exists:doctors,id'],
        ]);
        unset($data['doctor_ids']);
        if (isset($data['faqs']) && $data['faqs'] !== '') {
            $faqs = json_decode($data['faqs'], true);
            if (! is_array($faqs)) throw ValidationException::withMessages(['faqs' => 'FAQs must be valid JSON.']);
            $data['faqs'] = $faqs;
        }
        if (isset($data['price_currency'])) $data['price_currency'] = strtoupper($data['price_currency']);
        return $data;
    }

    private function locationData(Request $request, bool $creating = true): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'timezone' => ['required', 'timezone'],
            'map_url' => ['nullable', 'url', 'max:500'],
            'opening_hours' => ['nullable', 'string', 'max:4000'],
            'closure_days' => ['nullable', 'string', 'max:4000'],
            'is_active' => [$creating ? 'nullable' : 'required', 'boolean'],
            'public_visibility' => ['nullable', 'boolean'],
            'doctor_ids' => ['nullable', 'array'],
            'doctor_ids.*' => ['integer', 'exists:doctors,id'],
            'treatment_ids' => ['nullable', 'array'],
            'treatment_ids.*' => ['integer', 'exists:treatments,id'],
        ]);
        unset($data['doctor_ids'], $data['treatment_ids']);
        if (! empty($data['opening_hours'])) {
            $hours = json_decode($data['opening_hours'], true);
            if (! is_array($hours)) throw ValidationException::withMessages(['opening_hours' => 'Opening hours must be valid JSON.']);
            $data['opening_hours'] = $hours;
        } else {
            $data['opening_hours'] = null;
        }
        if (! empty($data['closure_days'])) {
            $closures = json_decode($data['closure_days'], true);
            if (! is_array($closures)) throw ValidationException::withMessages(['closure_days' => 'Closure days must be valid JSON.']);
            $data['closure_days'] = array_values(array_filter($closures, fn ($value) => is_string($value) && trim($value) !== ''));
        } else {
            $data['closure_days'] = null;
        }
        if (array_key_exists('public_visibility', $data) && $data['public_visibility'] === null) unset($data['public_visibility']);
        return $data;
    }

    private function syncDoctorRelationships(Doctor $doctor, Request $request): void
    {
        if ($request->has('specialties')) {
            $specialties = collect(explode(',', (string) $request->input('specialties')))->map(fn ($name) => trim($name))->filter()->unique()->map(fn ($name) => DoctorSpecialty::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id)->all();
            $doctor->specialties()->sync($specialties);
        }
        if ($request->has('location_ids')) $doctor->locations()->sync($request->input('location_ids', []));
        if ($request->has('treatment_ids')) $doctor->treatments()->sync($request->input('treatment_ids', []));
    }

    private function syncTreatmentRelationships(Treatment $treatment, Request $request): void
    {
        if ($request->has('doctor_ids')) $treatment->doctors()->sync($request->input('doctor_ids', []));
    }

    private function uniqueSlug(string $value, string $modelClass, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(10));
        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function storeImageIfPresent(Request $request, string $field, string $folder, object $model, string $urlField): void
    {
        if (! $request->hasFile($field)) return;
        $file = $request->file($field);
        $filename = Str::uuid()->toString().'.'.$file->extension();
        $path = Storage::disk('public')->putFileAs($folder, $file, $filename);
        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
        $url = Storage::disk('public')->url($path);
        $model->update([$urlField => $url]);
        MediaAsset::create(['filename' => $file->getClientOriginalName(), 'path' => $path, 'disk' => 'public', 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(), 'width' => $dimensions[0], 'height' => $dimensions[1], 'alt_text' => $model->name ?? $model->full_name, 'source' => 'admin content upload', 'sha256' => hash_file('sha256', $file->getRealPath()), 'is_public' => true, 'uploaded_by' => auth()->id()]);
    }
}
