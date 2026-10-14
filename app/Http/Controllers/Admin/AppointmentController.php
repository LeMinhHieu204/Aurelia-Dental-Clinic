<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentStatusChanged;
use App\Models\AppointmentRequest;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Treatment;
use App\Services\Scheduling\SchedulingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly SchedulingService $scheduling) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AppointmentRequest::class);

        $query = AppointmentRequest::with(['patient', 'treatment', 'doctor', 'location'])->latest();
        $ownDoctor = auth()->user()->hasPermission('calendar.own') && ! auth()->user()->hasPermission('appointments.manage')
            ? auth()->user()->doctor
            : null;
        $query->when($ownDoctor, fn ($q) => $q->where('doctor_id', $ownDoctor->id));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $query->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->integer('doctor_id')));
        $query->when($request->filled('treatment_id'), fn ($q) => $q->where('treatment_id', $request->integer('treatment_id')));
        $query->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->integer('location_id')));
        $query->when($request->filled('date'), fn ($q) => $q->whereDate('preferred_date', $request->date('date')));
        if ($request->filled('q')) {
            $query->where(function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where('reference', 'like', $term)
                    ->orWhereHas('patient', fn ($patient) => $patient->where('full_name', 'like', $term)->orWhere('email', 'like', $term));
            });
        }

        return view('admin.appointments.index', [
            'appointments' => $query->paginate(20)->withQueryString(),
            'doctors' => Doctor::where('is_active', true)->when($ownDoctor, fn ($q) => $q->whereKey($ownDoctor->id))->orderBy('full_name')->get(),
            'treatments' => Treatment::orderBy('name')->get(),
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function show(AppointmentRequest $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['patient', 'treatment', 'doctor', 'location', 'statusHistory.changedBy']);

        return view('admin.appointments.show', [
            'appointment' => $appointment,
            'doctors' => Doctor::where('is_active', true)->whereNull('archived_at')->orderBy('full_name')->get(),
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updateDetails(Request $request, AppointmentRequest $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', 'date_format:H:i'],
            'internal_notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $rescheduled = false;
        $updated = DB::transaction(function () use ($appointment, $data, &$rescheduled): AppointmentRequest {
            $locked = AppointmentRequest::whereKey($appointment->id)->lockForUpdate()->with(['treatment', 'location', 'patient'])->firstOrFail();
            $oldDate = $locked->preferred_date?->toDateString();
            $oldTime = $locked->preferred_time ? substr((string) $locked->preferred_time, 0, 5) : null;
            $oldDoctor = $locked->doctor_id;
            $oldLocation = $locked->location_id;
            $newDoctorId = $data['doctor_id'] ? (int) $data['doctor_id'] : null;
            $location = Location::where('is_active', true)->findOrFail($data['location_id']);

            if ($newDoctorId) {
                $doctor = Doctor::where('is_active', true)->whereNull('archived_at')->findOrFail($newDoctorId);
                if (! $doctor->locations()->whereKey($location->id)->exists()) {
                    throw ValidationException::withMessages(['doctor_id' => 'The selected dentist is not assigned to that location.']);
                }
            }

            $updates = $data + ['doctor_id' => $newDoctorId, 'location_id' => $location->id];
            if ($locked->status === AppointmentRequest::STATUS_CONFIRMED) {
                if (! $newDoctorId || empty($data['preferred_time'])) {
                    throw ValidationException::withMessages(['preferred_time' => 'A confirmed appointment needs an assigned dentist and time.']);
                }
                $window = $this->scheduling->assertAvailable($locked, $newDoctorId, $location->id, $data['preferred_date'], $data['preferred_time']);
                $updates += ['scheduled_start_utc' => $window['start_utc'], 'scheduled_end_utc' => $window['end_utc'], 'timezone' => $location->timezone, 'confirmed_at' => $locked->confirmed_at ?: now()];
                $rescheduled = $oldDate !== $data['preferred_date'] || $oldTime !== $data['preferred_time'] || $oldDoctor !== $newDoctorId || $oldLocation !== $location->id;
            } else {
                $updates += ['scheduled_start_utc' => null, 'scheduled_end_utc' => null, 'timezone' => null, 'confirmed_at' => null];
            }

            $locked->update($updates);
            if ($rescheduled) {
                $locked->statusHistory()->create(['status' => $locked->status, 'notes' => 'Confirmed appointment rescheduled.', 'changed_by' => auth()->id(), 'metadata' => ['rescheduled' => true]]);
            }
            AuditLog::record('appointment.details_changed', $locked, ['doctor_id' => $newDoctorId, 'location_id' => $location->id, 'rescheduled' => $rescheduled]);

            return $locked->fresh(['patient', 'treatment', 'doctor', 'location']);
        });

        if ($rescheduled && $updated->patient?->email) {
            Mail::to($updated->patient->email)->queue(new AppointmentStatusChanged($updated));
        }

        return back()->with('success', $rescheduled ? 'Appointment rescheduled and the patient notification was queued.' : 'Appointment details updated.');
    }

    public function updateStatus(Request $request, AppointmentRequest $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $data = $request->validate([
            'status' => ['required', 'in:REQUESTED,CONTACTED,PROPOSED,CONFIRMED,COMPLETED,CANCELLED,NO_SHOW'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $statusChanged = false;

        $updated = DB::transaction(function () use ($appointment, $data, &$statusChanged): AppointmentRequest {
            $locked = AppointmentRequest::whereKey($appointment->id)->lockForUpdate()->with(['patient', 'treatment', 'location'])->firstOrFail();
            $previousStatus = $locked->status;
            $statusChanged = $locked->status !== $data['status'];
            if ($statusChanged) $this->assertTransition($locked->status, $data['status']);
            $updates = ['status' => $data['status']];

            if ($statusChanged && $data['status'] === AppointmentRequest::STATUS_CONFIRMED) {
                if (! $locked->doctor_id || ! $locked->preferred_time) {
                    throw ValidationException::withMessages(['status' => 'Assign a dentist and time before confirming this request.']);
                }
                $window = $this->scheduling->assertAvailable($locked, $locked->doctor_id, $locked->location_id, $locked->preferred_date->toDateString(), substr((string) $locked->preferred_time, 0, 5), 'status');
                $updates += ['scheduled_start_utc' => $window['start_utc'], 'scheduled_end_utc' => $window['end_utc'], 'timezone' => $locked->location->timezone, 'confirmed_at' => now()];
            } elseif ($data['status'] !== AppointmentRequest::STATUS_CONFIRMED) {
                $updates += ['scheduled_start_utc' => $locked->scheduled_start_utc, 'scheduled_end_utc' => $locked->scheduled_end_utc];
            }
            if (! empty($data['notes'])) $updates['internal_notes'] = $data['notes'];

            $locked->update($updates);
            if ($statusChanged || ! empty($data['notes'])) {
                $locked->statusHistory()->create(['status' => $data['status'], 'notes' => $data['notes'] ?? null, 'changed_by' => auth()->id()]);
                AuditLog::record($statusChanged ? 'appointment.status_changed' : 'appointment.note_added', $locked, ['from' => $previousStatus, 'to' => $data['status']]);
            }

            return $locked->fresh(['patient', 'treatment', 'location']);
        });

        if ($statusChanged && in_array($data['status'], [AppointmentRequest::STATUS_PROPOSED, AppointmentRequest::STATUS_CONFIRMED, AppointmentRequest::STATUS_CANCELLED], true) && $updated->patient?->email) {
            Mail::to($updated->patient->email)->queue(new AppointmentStatusChanged($updated));
        }

        return back()->with('success', $statusChanged ? 'Appointment status updated and any notification was queued.' : 'Appointment note saved without sending a duplicate status notification.');
    }

    private function assertTransition(string $from, string $to): void
    {
        $allowed = [
            AppointmentRequest::STATUS_REQUESTED => [AppointmentRequest::STATUS_REQUESTED, AppointmentRequest::STATUS_CONTACTED, AppointmentRequest::STATUS_PROPOSED, AppointmentRequest::STATUS_CONFIRMED, AppointmentRequest::STATUS_CANCELLED],
            AppointmentRequest::STATUS_CONTACTED => [AppointmentRequest::STATUS_CONTACTED, AppointmentRequest::STATUS_PROPOSED, AppointmentRequest::STATUS_CONFIRMED, AppointmentRequest::STATUS_CANCELLED],
            AppointmentRequest::STATUS_PROPOSED => [AppointmentRequest::STATUS_PROPOSED, AppointmentRequest::STATUS_CONFIRMED, AppointmentRequest::STATUS_CONTACTED, AppointmentRequest::STATUS_CANCELLED],
            AppointmentRequest::STATUS_CONFIRMED => [AppointmentRequest::STATUS_CONFIRMED, AppointmentRequest::STATUS_PROPOSED, AppointmentRequest::STATUS_COMPLETED, AppointmentRequest::STATUS_CANCELLED, AppointmentRequest::STATUS_NO_SHOW],
            AppointmentRequest::STATUS_COMPLETED => [AppointmentRequest::STATUS_COMPLETED],
            AppointmentRequest::STATUS_CANCELLED => [AppointmentRequest::STATUS_CANCELLED],
            AppointmentRequest::STATUS_NO_SHOW => [AppointmentRequest::STATUS_NO_SHOW],
        ];
        if (! in_array($to, $allowed[$from] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "The appointment cannot move from {$from} to {$to}. Start a new request if needed."]);
        }
    }
}
