<div class="topbar"><div class="container topbar-inner"><span class="topbar-note">✦ Thoughtful dental care, designed around you</span><span class="topbar-links"><span>Clinic details editable in admin</span><span>Patient support</span></span></div></div>
<header class="site-header"><div class="container nav-inner">
    <a class="brand" href="{{ route('home') }}" aria-label="Aurelia Dental Clinic home">
        <svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true"><path d="M14.3 5.8C11 4.6 7.2 5.9 5.8 9.4c-1.7 4.3.5 8.7 2.1 12.6 1.3 3.2 2 6.9 3.6 9.2 1.2 1.6 3 2 4 .2 1.1-2 1-6.9 4.5-6.9s3.4 4.9 4.5 6.9c1 1.8 2.8 1.4 4-.2 1.6-2.3 2.3-6 3.6-9.2 1.6-3.9 3.8-8.3 2.1-12.6-1.4-3.5-5.2-4.8-8.5-3.6L20 7.5l-5.7-1.7Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M20 7.5 17.5 2M20 7.5 22.5 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
        <span class="brand-name">AURELIA<small>DENTAL CLINIC</small></span>
    </a>
    <nav class="main-nav" id="main-nav" aria-label="Primary navigation">
        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
        <a class="nav-link" href="{{ route('home') }}#about">About</a>
        <a class="nav-link {{ request()->routeIs('treatments.*') ? 'active' : '' }}" href="{{ route('treatments.index') }}">Treatments <span class="chevron">⌄</span></a>
        <a class="nav-link {{ request()->routeIs('doctors.*') ? 'active' : '' }}" href="{{ route('doctors.index') }}">Doctors</a>
        <a class="nav-link {{ request()->routeIs('reviews') ? 'active' : '' }}" href="{{ route('reviews') }}">Reviews</a>
        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
    </nav>
    <div class="header-actions"><a class="btn btn-primary" href="{{ route('booking') }}">Book Appointment <span class="arrow">→</span></a><button class="menu-toggle" id="menu-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Open menu">☰</button></div>
</div></header>
