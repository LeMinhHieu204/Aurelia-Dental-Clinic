<?php

namespace App\Services\Scheduling;

use App\Models\AppointmentRequest;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Location;
use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SchedulingService
{
    public function assertAvailable(
        AppointmentRequest $appointment,
        int $doctorId,
        int $locationId,
        string $date,
        string $time,
        string $errorKey = 'preferred_time',
    ): array {
        // Serialize confirmations for the same doctor. The row lock closes the race
        // where two transactions both observe an otherwise-free slot.
        $doctor = Doctor::where('is_active', true)->whereNull('archived_at')->whereKey($doctorId)->lockForUpdate()->first();
        $location = Location::where('is_active', true)->find($locationId);
        if (! $doctor || ! $location) {
            throw ValidationException::withMessages([$errorKey => 'Choose an active dentist and clinic location.']);
        }
        if (! $doctor->locations()->whereKey($location->id)->exists()) {
            throw ValidationException::withMessages([$errorKey => 'The selected dentist is not assigned to that location.']);
        }

        $duration = max(1, (int) ($appointment->treatment?->duration_minutes ?: 60));
        $window = $this->window($location, $date, $time, $duration);
        $this->assertClosureAndHours($doctor, $location, $window['start'], $window['end'], $errorKey);

        $existing = AppointmentRequest::query()
            ->where('doctor_id', $doctor->id)
            ->where('status', AppointmentRequest::STATUS_CONFIRMED)
            ->where('id', '!=', $appointment->id)
            ->with(['location', 'treatment'])
            ->lockForUpdate()
            ->get();

        foreach ($existing as $other) {
            $otherWindow = $this->existingWindow($other);
            if ($otherWindow && $window['start']->lt($otherWindow['end']) && $window['end']->gt($otherWindow['start'])) {
                throw ValidationException::withMessages([$errorKey => 'This dentist already has an overlapping confirmed appointment.']);
            }
        }

        return $window + ['doctor' => $doctor, 'location' => $location, 'duration' => $duration];
    }

    public function window(Location $location, string $date, string $time, int $duration): array
    {
        try {
            $start = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.$time, $location->timezone);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['preferred_time' => 'Enter a valid appointment date and time.']);
        }
        if (! $start || $start->format('Y-m-d H:i') !== $date.' '.$time) {
            throw ValidationException::withMessages(['preferred_time' => 'Enter a valid appointment date and time.']);
        }

        return ['start' => $start, 'end' => $start->addMinutes($duration), 'start_utc' => $start->utc(), 'end_utc' => $start->addMinutes($duration)->utc()];
    }

    private function existingWindow(AppointmentRequest $appointment): ?array
    {
        if ($appointment->scheduled_start_utc && $appointment->scheduled_end_utc) {
            return ['start' => CarbonImmutable::parse($appointment->scheduled_start_utc), 'end' => CarbonImmutable::parse($appointment->scheduled_end_utc)];
        }
        if (! $appointment->preferred_date || ! $appointment->preferred_time || ! $appointment->location) {
            return null;
        }

        $duration = max(1, (int) ($appointment->treatment?->duration_minutes ?: 60));
        return $this->window($appointment->location, $appointment->preferred_date->toDateString(), substr((string) $appointment->preferred_time, 0, 5), $duration);
    }

    private function assertClosureAndHours(Doctor $doctor, Location $location, CarbonImmutable $start, CarbonImmutable $end, string $errorKey): void
    {
        $date = $start->toDateString();
        $weekday = $start->dayOfWeek;
        $weekdayName = strtolower($start->format('l'));
        $closures = $location->closure_days ?? [];
        if (in_array($date, $closures, true) || in_array($weekdayName, array_map('strtolower', $closures), true)) {
            throw ValidationException::withMessages([$errorKey => 'This clinic is closed on the selected date.']);
        }

        $blocks = ScheduleBlock::query()->where('doctor_id', $doctor->id)->whereDate('blocked_date', $date)->where(fn ($query) => $query->whereNull('location_id')->orWhere('location_id', $location->id))->get();
        foreach ($blocks as $block) {
            if (! $block->starts_at || ! $block->ends_at) {
                throw ValidationException::withMessages([$errorKey => 'The dentist is unavailable on the selected date.']);
            }
            $blockStart = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($block->starts_at, 0, 5), $location->timezone);
            $blockEnd = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($block->ends_at, 0, 5), $location->timezone);
            if ($start->lt($blockEnd) && $end->gt($blockStart)) {
                throw ValidationException::withMessages([$errorKey => 'The dentist is unavailable during the selected time.']);
            }
        }

        $availabilityQuery = DoctorAvailability::query()
            ->where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('location_id')->orWhere('location_id', $location->id));
        $exceptionalAvailability = (clone $availabilityQuery)->whereDate('available_date', $date)->get();
        $availability = $exceptionalAvailability->isNotEmpty()
            ? $exceptionalAvailability
            : $availabilityQuery->whereNull('available_date')->where('weekday', $weekday)->get();
        if ($availability->isNotEmpty()) {
            $fits = $availability->contains(function (DoctorAvailability $slot) use ($start, $end, $date, $location): bool {
                $slotStart = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($slot->starts_at, 0, 5), $slot->timezone ?: $location->timezone);
                $slotEnd = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($slot->ends_at, 0, 5), $slot->timezone ?: $location->timezone);
                return $start->greaterThanOrEqualTo($slotStart) && $end->lessThanOrEqualTo($slotEnd);
            });
            if (! $fits) {
                throw ValidationException::withMessages([$errorKey => 'The selected time is outside the dentist’s configured working hours.']);
            }
        }

        $hours = $location->opening_hours[$weekdayName] ?? null;
        if (is_array($hours) && isset($hours['open'], $hours['close'])) {
            $open = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($hours['open'], 0, 5), $location->timezone);
            $close = CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.substr($hours['close'], 0, 5), $location->timezone);
            if ($start->lt($open) || $end->gt($close)) {
                throw ValidationException::withMessages([$errorKey => 'The selected time is outside the clinic opening hours.']);
            }
        }
    }
}
