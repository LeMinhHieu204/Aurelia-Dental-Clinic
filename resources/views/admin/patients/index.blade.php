@extends('layouts.admin', ['title' => 'Patients'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Protected contacts</p><h1>Patients</h1><p class="muted">Administrative contact records only — no diagnoses or clinical histories.</p></div></div>
<div class="admin-panel">
    <form class="admin-form" method="GET"><div class="field field-full"><label for="q">Search name, email, or phone</label><input id="q" name="q" value="{{ request('q') }}"></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Search</button></div></form>
    <table class="admin-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Requests</th><th>Last request</th></tr></thead><tbody>
    @forelse($patients as $patient)
        <tr><td><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></td><td>{{ $patient->email }}</td><td>{{ $patient->phone }}</td><td>{{ $patient->appointments_count }}</td><td>{{ $patient->appointments_max_created_at ? \Illuminate\Support\Carbon::parse($patient->appointments_max_created_at)->format('d M Y') : '—' }}</td></tr>
    @empty
        <tr><td colspan="5">No contact records found.</td></tr>
    @endforelse
    </tbody></table>
    {{ $patients->links() }}
</div>
@endsection
