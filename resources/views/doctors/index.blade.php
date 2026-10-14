@extends('layouts.app', ['title' => 'Doctors | Aurelia Dental Clinic'])
@section('content')
@php
    $hero = $sections->get('doctors.page');
    $heroImage = $hero?->image_url ?: asset('images/aurelia-demo-reception.png');
    $heroEyebrow = $hero?->title ?: 'Our doctors';
    $heroTitle = $sections->get('doctors.page.title')?->title ?: 'Care that begins with listening';
    $heroDescription = $sections->get('doctors.page.description')?->body ?: 'Meet the team behind the Aurelia experience. Public profile details should be verified by the clinic before launch.';
    $catalogTitle = $sections->get('doctors.catalog.title')?->title ?: 'A thoughtful team for every stage of care';
    $catalogDescription = $sections->get('doctors.catalog.description')?->body ?: 'These are demonstration profiles for the interface. Add verified credentials, registrations, languages, and locations in Admin before publishing.';
@endphp
<section class="hero hero-page"><img class="hero-photo" src="{{ $heroImage }}" alt="Bright dental clinic interior"><div class="hero-content"><div class="hero-copy"><div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>&rsaquo;</span><span>Doctors</span></div><p class="eyebrow light">{{ $heroEyebrow }}</p><h1>{{ $heroTitle }}</h1><p class="lede">{{ $heroDescription }}</p><div class="hero-actions"><a class="btn btn-primary" href="{{ route('booking') }}">Request an appointment <span class="arrow">&rarr;</span></a></div></div></div></section>
<section class="section"><div class="container"><div class="section-head"><div><p class="eyebrow">Meet our specialists</p><h2>{{ $catalogTitle }}</h2><p class="section-intro">{{ $catalogDescription }}</p></div></div><div class="doctor-grid">@forelse($doctors as $doctor)@include('partials.doctor-card', ['doctor' => $doctor, 'wide' => true])@empty<div class="notice">No public doctor profiles are available yet.</div>@endforelse</div></div></section>
@endsection
