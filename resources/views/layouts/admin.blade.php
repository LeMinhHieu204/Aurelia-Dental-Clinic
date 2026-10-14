<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $title ?? 'Admin' }} | Aurelia Dental Clinic</title>@vite(['resources/css/aurelia.css','resources/js/aurelia.js'])</head>
<body class="admin-body"><div class="admin-shell"><div class="container admin-nav">
    <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-name">AURELIA<small>DENTAL CLINIC · ADMIN</small></span></a>
    <nav class="admin-nav-links">
        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
        @if(auth()->user()->hasPermission('appointments.manage'))<a class="{{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}" href="{{ route('admin.appointments.index') }}">Appointments</a>@endif
        @if(auth()->user()->hasPermission('calendar.manage') || auth()->user()->hasPermission('calendar.own'))<a class="{{ request()->routeIs('admin.calendar*') || request()->routeIs('admin.availability*') ? 'active' : '' }}" href="{{ route('admin.calendar') }}">Calendar</a>@endif
        @if(auth()->user()->hasPermission('doctors.manage'))<a class="{{ request()->routeIs('admin.doctors*') ? 'active' : '' }}" href="{{ route('admin.doctors') }}">Doctors</a>@endif
        @if(auth()->user()->hasPermission('treatments.manage'))<a class="{{ request()->routeIs('admin.treatments*') ? 'active' : '' }}" href="{{ route('admin.treatments') }}">Treatments</a>@endif
        @if(auth()->user()->hasPermission('locations.manage'))<a class="{{ request()->routeIs('admin.locations*') ? 'active' : '' }}" href="{{ route('admin.locations') }}">Locations</a>@endif
        @if(auth()->user()->hasPermission('patients.view'))<a class="{{ request()->routeIs('admin.patients*') ? 'active' : '' }}" href="{{ route('admin.patients') }}">Patients</a>@endif
        @if(auth()->user()->hasPermission('inquiries.manage'))<a class="{{ request()->routeIs('admin.inquiries*') ? 'active' : '' }}" href="{{ route('admin.inquiries') }}">Inquiries</a>@endif
        @if(auth()->user()->hasPermission('reviews.manage'))<a class="{{ request()->routeIs('admin.reviews*') ? 'active' : '' }}" href="{{ route('admin.reviews') }}">Reviews</a>@endif
        @if(auth()->user()->hasPermission('media.manage'))<a class="{{ request()->routeIs('admin.media*') ? 'active' : '' }}" href="{{ route('admin.media') }}">Media</a>@endif
        @if(auth()->user()->hasPermission('content.manage'))<a class="{{ request()->routeIs('admin.content*') ? 'active' : '' }}" href="{{ route('admin.content') }}">Content</a>@endif
        @if(auth()->user()->hasPermission('settings.manage'))<a class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">Settings</a>@endif
        @if(auth()->user()->hasPermission('audit.view'))<a class="{{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">Audit logs</a>@endif
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">@csrf<button class="btn-link" type="submit">Sign out</button></form>
    </nav>
</div><main class="admin-content"><div class="container">
    @if(session('success'))<div class="form-message success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="form-message error">{{ $errors->first() }}</div>@endif
    @yield('content')
</div></main></div></body></html>
