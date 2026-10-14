<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $metaDescription ?? 'Aurelia Dental Clinic — thoughtful, patient-first dental care.' }}">
    <meta name="theme-color" content="#14263E">
    <title>{{ $title ?? 'Aurelia Dental Clinic' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/aurelia.css', 'resources/js/aurelia.js'])
</head>
<body>
    @include('partials.header')
    @if(session('success'))<div class="container"><div class="form-message success" role="status">{{ session('success') }}</div></div>@endif
    @if($errors->any())<div class="container"><div class="form-message error" role="alert">{{ $errors->first() }}</div></div>@endif
    @yield('content')
    @include('partials.footer')
    <button class="back-top" id="back-top" aria-label="Back to top">↑</button>
</body>
</html>
