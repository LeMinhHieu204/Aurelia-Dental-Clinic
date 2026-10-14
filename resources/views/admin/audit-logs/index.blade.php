@extends('layouts.admin', ['title' => 'Audit Logs'])

@section('content')
<div class="admin-head"><div><p class="eyebrow">Security</p><h1>Audit logs</h1><p class="muted">Read-only activity history. Patient messages, passwords, and session tokens are not recorded here.</p></div></div>
<div class="admin-panel">
    <form class="admin-form" method="GET"><div class="field"><label for="q">Action</label><input id="q" name="q" value="{{ request('q') }}" placeholder="appointment.status_changed"></div><div class="field"><label for="user_id">Actor</label><select id="user_id" name="user_id"><option value="">All actors</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select></div><div class="field"><label for="date">Date</label><input id="date" type="date" name="date" value="{{ request('date') }}"></div><div class="field" style="justify-content:end"><button class="btn btn-outline">Filter</button></div></form>
    <table class="admin-table"><thead><tr><th>When</th><th>Actor</th><th>Action</th><th>Entity</th><th>Metadata</th></tr></thead><tbody>
    @forelse($logs as $log)
        <tr><td>{{ $log->created_at->format('d M Y H:i') }}</td><td>{{ $log->user?->email ?: 'System' }}</td><td>{{ $log->action }}</td><td>{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td><td>{{ collect($log->metadata ?: [])->except(['message', 'password', 'token'])->toJson() }}</td></tr>
    @empty
        <tr><td colspan="5">No audit events found.</td></tr>
    @endforelse
    </tbody></table>
    {{ $logs->links() }}
</div>
@endsection
