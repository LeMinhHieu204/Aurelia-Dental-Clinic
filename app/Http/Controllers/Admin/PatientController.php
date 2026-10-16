<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::withCount('appointments')->withMax('appointments', 'created_at')->latest();
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('full_name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('phone', 'like', $term));
        }
        return view('admin.patients.index', ['patients' => $query->paginate(25)->withQueryString()]);
    }

    public function show(Patient $patient): View
    {
        $this->authorize('view', $patient);

        return view('admin.patients.show', ['patient' => $patient->load(['appointments.treatment', 'appointments.doctor', 'appointments.location'])]);
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorize('update', $patient);

        $data = $request->validate(['full_name' => ['required', 'string', 'min:2', 'max:120'], 'email' => ['required', 'email:rfc', 'max:180'], 'phone' => ['required', 'string', 'min:7', 'max:40']]);
        $patient->update($data);
        AuditLog::record('patient.contact_updated', $patient, ['fields' => ['full_name', 'email', 'phone']]);
        return back()->with('success', 'Patient contact details updated.');
    }

    public function anonymize(Patient $patient): RedirectResponse
    {
        $this->authorize('anonymize', $patient);

        DB::transaction(function () use ($patient): void {
            $patient->update(['full_name' => 'Anonymized patient', 'email' => 'anonymized+'.Str::lower(Str::random(20)).'@example.invalid', 'phone' => 'REDACTED']);
            AuditLog::record('patient.anonymized', $patient, ['retention_action' => 'contact_details_redacted']);
        });
        return redirect()->route('admin.patients')->with('success', 'Patient contact details anonymized; booking history was preserved.');
    }
}
