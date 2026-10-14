@extends('layouts.app', ['title' => 'Book an Appointment | Aurelia Dental Clinic'])
@section('content')
@php
    $hero = $sections->get('booking.page');
    $heroImage = $hero?->image_url ?: asset('images/aurelia-demo-hero.png');
    $heroEyebrow = $hero?->title ?: 'Book appointment';
    $heroTitle = $sections->get('booking.page.title')?->title ?: "Let's find the right next step";
    $heroDescription = $sections->get('booking.page.description')?->body ?: 'Send an appointment request and the Aurelia team will follow up. Your preferred date is a request only until the clinic confirms availability.';
    $formTitle = $sections->get('booking.form.title')?->title ?: 'Tell us how we can help';
    $formDescription = $sections->get('booking.form.description')?->body ?: 'Share only what is needed for an initial enquiry. This form does not collect medical history or clinical records.';
@endphp
<section class="hero hero-page"><img class="hero-photo" src="{{ $heroImage }}" alt="Bright modern treatment room"><div class="hero-content"><div class="hero-copy"><div class="breadcrumbs"><a href="{{ route('home') }}">Home</a><span>&rsaquo;</span><span>Book appointment</span></div><p class="eyebrow light">{{ $heroEyebrow }}</p><h1>{{ $heroTitle }}</h1><p class="lede">{{ $heroDescription }}</p></div></div></section>
<section class="section section-cream"><div class="container" style="max-width:920px"><div class="panel"><p class="eyebrow">Start with a simple request</p><h2>{{ $formTitle }}</h2><p class="section-intro">{{ $formDescription }}</p>@include('partials.booking-form')</div></div></section>
@endsection
