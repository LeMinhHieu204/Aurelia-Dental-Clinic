<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppointmentRequest;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $ownScope = auth()->user()->hasPermission('calendar.own') && ! auth()->user()->hasPermission('calendar.manage');
        $ownDoctor = $ownScope
            ? auth()->user()->doctor
            : null;
        abort_unless(! $ownScope || $ownDoctor, 403, 'Your doctor account is not linked to a doctor profile.');
        $mode = in_array($request->string('view')->toString(), ['day', 'week', 'month'], true) ? $request->string('view')->toString() : 'month';
        $anchor = CarbonImmutable::parse($request->input('date', today()->toDateString()));
        $start = match ($mode) {
            'day' => $anchor->startOfDay(),
            'week' => $anchor->startOfWeek(),
            default => $anchor->startOfMonth()->startOfWeek(),
        };
        $end = match ($mode) {
            'day' => $anchor->endOfDay(),
            'week' => $anchor->endOfWeek(),
            default => $anchor->endOfMonth()->endOfWeek(),
        };
        $query = AppointmentRequest::with(['patient', 'doctor', 'location', 'treatment'])
            ->whereDate('preferred_date', '>=', $start->toDateString())
            ->whereDate('preferred_date', '<=', $end->toDateString())
            ->orderBy('preferred_date')->orderBy('preferred_time');
        $query->when($request->filled('doctor_id'), fn ($q) => $q->where('doctor_id', $request->integer('doctor_id')));
        $query->when($ownDoctor, fn ($q) => $q->where('doctor_id', $ownDoctor->id));
        $query->when($request->filled('location_id'), fn ($q) => $q->where('location_id', $request->integer('location_id')));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        return view('admin.calendar.index', [
            'appointments' => $query->get(),
            'blocks' => ScheduleBlock::with(['doctor', 'location'])->whereDate('blocked_date', '>=', $start->toDateString())->whereDate('blocked_date', '<=', $end->toDateString())->when($ownDoctor, fn ($q) => $q->where('doctor_id', $ownDoctor->id))->get(),
            'doctors' => Doctor::where('is_active', true)->whereNull('archived_at')->when($ownDoctor, fn ($q) => $q->whereKey($ownDoctor->id))->orderBy('full_name')->get(),
            'locations' => Location::where('is_active', true)->orderBy('name')->get(),
            'mode' => $mode,
            'anchor' => $anchor,
            'start' => $start,
            'end' => $end,
        ]);
    }
}
