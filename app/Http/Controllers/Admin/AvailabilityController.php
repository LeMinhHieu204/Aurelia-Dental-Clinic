<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Location;
use App\Models\ScheduleBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function index(Request $request): View
    {
        $doctor = $request->filled('doctor_id') ? Doctor::find($request->integer('doctor_id')) : Doctor::orderBy('full_name')->first();
        return view('admin.calendar.availability', [
            'doctors' => Doctor::where('is_active', true)->whereNull('archived_at')->orderBy('full_name')->get(),
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
            'doctor' => $doctor,
            'availability' => $doctor?->availability()->with('location')->orderBy('weekday')->orderBy('starts_at')->get() ?? collect(),
            'blocks' => $doctor?->relationLoaded('blocks') ? $doctor->blocks : ScheduleBlock::where('doctor_id', $doctor?->id)->whereDate('blocked_date', '>=', today())->orderBy('blocked_date')->limit(50)->get(),
        ]);
    }

    public function storeAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'available_date' => ['nullable', 'date', 'after_or_equal:today'],
            'weekday' => ['nullable', 'integer', 'between:0,6'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'timezone' => ['required', 'timezone'],
        ]);
        if (empty($data['available_date']) && ($data['weekday'] ?? null) === null) {
            throw ValidationException::withMessages(['weekday' => 'Choose a weekly day or an exceptional date.']);
        }
        if (! empty($data['available_date'])) {
            $data['weekday'] = Carbon::parse($data['available_date'])->dayOfWeek;
        }
        $this->assertDoctorLocation($data['doctor_id'], $data['location_id'] ?? null);
        $availability = DoctorAvailability::create($data + ['is_active' => true]);
        AuditLog::record('availability.created', $availability);
        return redirect()->route('admin.availability', ['doctor_id' => $data['doctor_id']])->with('success', 'Working hours added.');
    }

    public function destroyAvailability(DoctorAvailability $availability): RedirectResponse
    {
        $doctorId = $availability->doctor_id;
        AuditLog::record('availability.deleted', $availability);
        $availability->delete();
        return redirect()->route('admin.availability', ['doctor_id' => $doctorId])->with('success', 'Working hours removed.');
    }

    public function storeBlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'blocked_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['nullable', 'date_format:H:i', 'required_with:ends_at'],
            'ends_at' => ['nullable', 'date_format:H:i', 'after:starts_at', 'required_with:starts_at'],
            'reason' => ['nullable', 'string', 'max:180'],
        ]);
        $this->assertDoctorLocation($data['doctor_id'], $data['location_id'] ?? null);
        $block = ScheduleBlock::create($data);
        AuditLog::record('schedule_block.created', $block);
        return redirect()->route('admin.availability', ['doctor_id' => $data['doctor_id']])->with('success', 'Unavailable period added.');
    }

    public function destroyBlock(ScheduleBlock $block): RedirectResponse
    {
        $doctorId = $block->doctor_id;
        AuditLog::record('schedule_block.deleted', $block);
        $block->delete();
        return redirect()->route('admin.availability', ['doctor_id' => $doctorId])->with('success', 'Unavailable period removed.');
    }

    private function assertDoctorLocation(int $doctorId, ?int $locationId): void
    {
        $doctor = Doctor::where('is_active', true)->whereNull('archived_at')->findOrFail($doctorId);
        if ($locationId && ! Location::where('is_active', true)->whereKey($locationId)->exists()) {
            throw ValidationException::withMessages(['location_id' => 'Choose an active clinic location.']);
        }
        if ($locationId && ! $doctor->locations()->whereKey($locationId)->exists()) {
            throw ValidationException::withMessages(['location_id' => 'The selected dentist is not assigned to that location.']);
        }
    }
}
