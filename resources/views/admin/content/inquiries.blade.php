@extends('layouts.admin', ['title' => 'Inquiries'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Patient communications</p><h1>Inquiries</h1></div></div>
<div class="admin-panel">
    <form class="admin-form" method="GET"><div class="field field-full"><label for="q">Search name, email, or message</label><input id="q" name="q" value="{{ request('q') }}"></div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option>@foreach(['NEW','IN_PROGRESS','RESOLVED','SPAM'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Filter</button></div></form>
    <table class="admin-table"><thead><tr><th>Received</th><th>Patient</th><th>Contact</th><th>Message</th><th>Manage</th></tr></thead><tbody>
    @forelse($inquiries as $inquiry)
        <tr><td>{{ $inquiry->created_at?->format('M j, Y') }}</td><td><strong>{{ $inquiry->name }}</strong><br>{{ $inquiry->subject ?: 'General inquiry' }}</td><td>{{ $inquiry->email }}<br>{{ $inquiry->phone ?: '—' }}</td><td>{{ $inquiry->message }}</td><td><form method="POST" action="{{ route('admin.inquiries.update', $inquiry) }}">@csrf @method('PATCH')<select name="status" aria-label="Inquiry status">@foreach(['NEW','IN_PROGRESS','RESOLVED','SPAM','CONTACTED','CLOSED'] as $status)<option value="{{ $status }}" @selected($inquiry->status === $status)>{{ $status }}</option>@endforeach</select><select name="assigned_to" aria-label="Assign inquiry"><option value="">Unassigned</option>@foreach($staff as $member)<option value="{{ $member->id }}" @selected($inquiry->assigned_to === $member->id)>{{ $member->name }}</option>@endforeach</select><textarea name="internal_notes" aria-label="Internal note" placeholder="Internal note…">{{ $inquiry->internal_notes }}</textarea><button class="btn btn-outline" style="margin-top:8px">Save</button></form></td></tr>
    @empty
        <tr><td colspan="5">No inquiries found.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endsection
