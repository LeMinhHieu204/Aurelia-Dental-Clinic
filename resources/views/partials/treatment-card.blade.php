@php($image = $treatment->cover_image_url && ! str_contains($treatment->cover_image_url, 'images.unsplash.com') ? $treatment->cover_image_url : asset('images/aurelia-demo-treatment.png'))
<article class="{{ $catalog ?? false ? 'catalog-card' : 'treatment-card' }}">
    <img class="{{ $catalog ?? false ? '' : 'treatment-image' }}" src="{{ $image }}" alt="{{ $treatment->name }}" loading="lazy">
    <div class="{{ $catalog ?? false ? 'catalog-body' : 'treatment-card-body' }}">
        @if($catalog ?? false)<span class="catalog-label">{{ $treatment->consultation_required ? 'Consultation-led care' : 'Everyday care' }}</span>@endif
        <h3>{{ $treatment->name }}</h3><p>{{ $treatment->short_description }}</p>
        @if(!empty($treatment->duration_label))<div class="treatment-meta"><span>◷ {{ $treatment->duration_label }}</span></div>@endif
        <a class="btn-link" href="{{ route('treatments.show', $treatment) }}">{{ $catalog ?? false ? 'Explore treatment' : 'Learn more' }} <span class="arrow">→</span></a>
    </div>
</article>
