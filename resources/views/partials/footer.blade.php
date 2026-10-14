@php
    $phone = $siteSettings->get('support_phone', '+1 (800) 452-2876');
    $phoneHref = preg_replace('/[^0-9+]/', '', $phone);
    $email = $siteSettings->get('general_email', 'hello@aureliadental.example');
    $footerNote = $siteSettings->get('footer_note', 'Demo content — replace with approved clinic details before launch.');
@endphp
<footer class="footer">
    <div class="container footer-main">
        <div class="footer-brand">
            <a class="brand" href="{{ route('home') }}">
                <svg class="brand-mark" viewBox="0 0 48 48" aria-hidden="true"><path d="M14.3 5.8C11 4.6 7.2 5.9 5.8 9.4c-1.7 4.3.5 8.7 2.1 12.6 1.3 3.2 2 6.9 3.6 9.2 1.2 1.6 3 2 4 .2 1.1-2 1-6.9 4.5-6.9s3.4 4.9 4.5 6.9c1 1.8 2.8 1.4 4-.2 1.6-2.3 2.3-6 3.6-9.2 1.6-3.9 3.8-8.3 2.1-12.6-1.4-3.5-5.2-4.8-8.5-3.6L20 7.5l-5.7-1.7Z" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M20 7.5 17.5 2M20 7.5 22.5 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <span class="brand-name">AURELIA<small>DENTAL CLINIC</small></span>
            </a>
            <p>Confident smiles start here. A thoughtful, patient-first dental experience with clear information and straightforward next steps.</p>
        </div>
        <div><h4>Explore</h4><div class="footer-links"><a href="{{ route('home') }}">Home</a><a href="{{ route('treatments.index') }}">Treatments</a><a href="{{ route('doctors.index') }}">Our doctors</a><a href="{{ route('reviews') }}">Patient stories</a></div></div>
        <div><h4>Visit</h4><div class="footer-links"><span>Clinic location to be confirmed</span><span>Opening hours configured in admin</span><a href="{{ route('contact') }}">Contact the clinic</a></div></div>
        <div><h4>Get in touch</h4><div class="footer-links"><a href="tel:{{ $phoneHref }}">{{ $phone }}</a><a href="mailto:{{ $email }}">{{ $email }}</a><a href="{{ route('booking') }}">Request an appointment</a></div></div>
    </div>
    <div class="container footer-bottom"><span>© {{ date('Y') }} Aurelia Dental Clinic. {{ $footerNote }}</span><span><a href="{{ route('privacy') }}">Privacy</a> · <a href="{{ route('terms') }}">Terms</a></span></div>
</footer>
