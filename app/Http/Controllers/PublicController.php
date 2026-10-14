<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Mail\AppointmentRequestReceived;
use App\Models\AppointmentRequest;
use App\Models\AuditLog;
use App\Models\ContentSection;
use App\Models\Doctor;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Review;
use App\Models\Treatment;
use App\Mail\InquiryReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('home', [
            'treatments' => Treatment::publicVisible()->limit(4)->get(),
            'doctors' => Doctor::publicVisible()->limit(3)->get(),
            'reviews' => Review::publicVisible()->latest('review_date')->limit(3)->get(),
            'sections' => $this->contentSections(),
        ]);
    }

    public function treatments(): View
    {
        return view('treatments.index', ['treatments' => Treatment::publicVisible()->orderBy('display_order')->get(), 'sections' => $this->contentSections()]);
    }

    public function treatment(Treatment $treatment): View
    {
        abort_unless($treatment->isPubliclyVisible(), 404);

        return view('treatments.show', compact('treatment'));
    }

    public function doctors(): View
    {
        return view('doctors.index', ['doctors' => Doctor::publicVisible()->orderBy('display_order')->get(), 'sections' => $this->contentSections()]);
    }

    public function doctor(Doctor $doctor): View
    {
        abort_unless($doctor->isPubliclyVisible(), 404);

        return view('doctors.show', compact('doctor'));
    }

    public function reviews(): View
    {
        return view('reviews', ['reviews' => Review::publicVisible()->latest('review_date')->get(), 'sections' => $this->contentSections()]);
    }

    public function contact(): View
    {
        return view('contact', [
            'locations' => Location::publicVisible()->get(),
            'treatments' => Treatment::publicVisible()->get(),
            'doctors' => Doctor::publicVisible()->get(),
            'sections' => $this->contentSections(),
        ]);
    }

    public function book(): View
    {
        return view('booking', [
            'locations' => Location::publicVisible()->get(),
            'treatments' => Treatment::publicVisible()->get(),
            'doctors' => Doctor::publicVisible()->get(),
            'sections' => $this->contentSections(),
        ]);
    }

    public function storeAppointment(StoreAppointmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['doctor_id'])) {
            $doctor = Doctor::findOrFail($data['doctor_id']);
            if (! $doctor->locations()->whereKey($data['location_id'])->exists()) {
                throw ValidationException::withMessages(['doctor_id' => 'The selected dentist is not assigned to that location.']);
            }
        }

        $appointment = DB::transaction(function () use ($data, $request): AppointmentRequest {
            $patient = Patient::updateOrCreate(
                ['email' => Str::lower($data['email'])],
                ['full_name' => $data['full_name'], 'phone' => $data['phone']],
            );

            $reference = $this->uniqueReference();
            $appointment = AppointmentRequest::create([
                'patient_id' => $patient->id,
                'treatment_id' => $data['treatment_id'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'location_id' => $data['location_id'],
                'reference' => $reference,
                'preferred_date' => $data['preferred_date'],
                'preferred_time' => $data['preferred_time'] ?? null,
                'status' => AppointmentRequest::STATUS_REQUESTED,
                'message' => $data['message'] ?? null,
                'privacy_acknowledgement' => true,
                'source_ip_hash' => hash('sha256', (string) $request->ip()),
            ]);

            $appointment->statusHistory()->create(['status' => AppointmentRequest::STATUS_REQUESTED, 'notes' => 'Public request received.']);

            return $appointment;
        });

        AuditLog::record('appointment.requested', $appointment, ['reference' => $appointment->reference]);

        if (config('aurelia.notifications.staff_email')) {
            Mail::to(config('aurelia.notifications.staff_email'))->queue(new AppointmentRequestReceived($appointment));
        }

        return redirect()->route('booking')->with('success', "Your appointment request was received. Reference {$appointment->reference}. A clinic team member will contact you; this is not a confirmed appointment.");
    }

    public function storeInquiry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:2000'],
            'website' => ['nullable', 'max:0'],
        ]);

        $inquiry = DB::transaction(fn (): Inquiry => Inquiry::create($data + ['status' => Inquiry::STATUS_NEW]));
        AuditLog::record('inquiry.created', $inquiry);
        if (config('aurelia.notifications.staff_email')) {
            Mail::to(config('aurelia.notifications.staff_email'))->queue(new InquiryReceived($inquiry));
        }

        return back()->with('success', 'Your enquiry was received. The clinic team will follow up using the contact details provided.');
    }

    private function uniqueReference(): string
    {
        do {
            $reference = 'AUR-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        } while (AppointmentRequest::where('reference', $reference)->exists());

        return $reference;
    }

    private function contentSections()
    {
        return ContentSection::publicVisible()->orderBy('sort_order')->get()->keyBy('key');
    }
}
