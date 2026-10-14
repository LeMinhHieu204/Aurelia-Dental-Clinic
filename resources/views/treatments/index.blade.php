@extends('layouts.app', ['title' => 'Treatments | Aurelia Dental Clinic'])
@section('content')
@php
    $hero = $sections->get('treatments.page');
    $heroImage = $hero?->image_url ?: asset('images/aurelia-demo-hero.png');
    $heroEyebrow = $hero?->title ?: 'Our treatments';
    $heroTitle = $sections->get('treatments.page.title')?->title ?: 'Advanced care for healthier, brighter smiles';
    $heroDescription = $sections->get('treatments.page.description')?->body ?: 'Explore dental treatment options and learn what to expect from consultation through ongoing care.';
    $catalogTitle = $sections->get('treatments.catalog.title')?->title ?: 'Thoughtful options, clearly explained';
    $catalogDescription = $sections->get('treatments.catalog.description')?->body ?: 'Every treatment begins with a conversation and a clinical assessment. Treatment suitability, timing, cost, risks, and alternatives are discussed with you before you decide.';
@endphp
<section class="hero hero-page"><img class="hero-photo" src="{{ $heroImage }}" alt="Dentist consulting with a patient"><div class="hero-content"><div class="hero-copy"><div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>&rsaquo;</span><span>Treatments</span></div><p class="eyebrow light">{{ $heroEyebrow }}</p><h1>{{ $heroTitle }}</h1><p class="lede">{{ $heroDescription }}</p><div class="hero-actions"><a class="btn btn-primary" href="{{ route('booking') }}">Book a consultation <span class="arrow">&rarr;</span></a><a class="btn btn-outline" href="{{ route('contact') }}">Speak with our team</a></div></div></div></section>
<section class="section"><div class="container"><div class="section-head"><div><p class="eyebrow">Our treatments</p><h2>{{ $catalogTitle }}</h2><p class="section-intro">{{ $catalogDescription }}</p></div></div><div class="catalog-grid">@forelse($treatments as $treatment)@include('partials.treatment-card', ['treatment' => $treatment, 'catalog' => true])@empty<div class="notice">No published treatments are available yet.</div>@endforelse</div></div></section>
<section class="section section-cream"><div class="container"><div class="approach-grid"><div class="approach-intro"><p class="eyebrow">The Aurelia approach</p><h2>Modern dentistry.<br>A more personal experience.</h2><p>We make space for questions and keep the next step simple, so you can make informed decisions about your care.</p></div><div class="approach-item"><span class="icon-lg">&#10022;</span><h3>Clear plans</h3><p>Understand what happens next.</p></div><div class="approach-item"><span class="icon-lg">&#9825;</span><h3>Gentle care</h3><p>Respectful, comfort-minded visits.</p></div><div class="approach-item"><span class="icon-lg">&#9633;</span><h3>Easy requests</h3><p>Start with a simple enquiry.</p></div><div class="approach-item"><span class="icon-lg">&#9827;</span><h3>Privacy aware</h3><p>We ask only what we need.</p></div></div></div></section>
@endsection
