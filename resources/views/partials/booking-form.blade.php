<form method="POST" action="{{ route('booking.store') }}" data-booking-form novalidate>
    @csrf
    <div class="form-grid">
        <div class="field"><label for="full-name">Full name <span>*</span></label><input id="full-name" name="full_name" value="{{ old('full_name') }}" autocomplete="name" placeholder="Enter your full name" required></div>
        <div class="field"><label for="email">Email address <span>*</span></label><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required></div>
        <div class="field"><label for="phone">Phone number <span>*</span></label><input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="+1 (000) 000-0000" required></div>
        <div class="field"><label for="location">Preferred location <span>*</span></label><select id="location" name="location_id" required><option value="">Select a location</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id', request('location')) == $location->id)>{{ $location->name }}{{ $location->is_demo ? ' · Demo' : '' }}</option>@endforeach</select></div>
        <div class="field"><label for="date">Preferred date <span>*</span></label><input id="date" type="date" name="preferred_date" value="{{ old('preferred_date') }}" min="{{ now()->toDateString() }}" required></div>
        <div class="field"><label for="time">Preferred time</label><input id="time" type="time" name="preferred_time" value="{{ old('preferred_time') }}"></div>
        <div class="field"><label for="interest">Treatment interest <span>*</span></label><select id="interest" name="treatment_id" required><option value="">Select a treatment</option>@foreach($treatments as $treatment)<option value="{{ $treatment->id }}" @selected(old('treatment_id', request('treatment')) == $treatment->id)>{{ $treatment->name }}</option>@endforeach</select></div>
        <div class="field"><label for="doctor">Preferred dentist</label><select id="doctor" name="doctor_id"><option value="">No preference</option>@foreach($doctors as $doctor)<option value="{{ $doctor->id }}" @selected(old('doctor_id', request('doctor')) == $doctor->id)>{{ $doctor->full_name }}</option>@endforeach</select></div>
        <div class="field field-full"><label for="message">Message <small>(optional)</small></label><textarea id="message" name="message" placeholder="Tell us how we can help you...">{{ old('message') }}</textarea></div>
        <div class="field" style="display:none"><label for="website">Website</label><input id="website" name="website" tabindex="-1" autocomplete="off"></div>
        <label class="checkbox-field"><input type="checkbox" name="privacy_acknowledgement" value="1" @checked(old('privacy_acknowledgement')) required><span>I understand this is an appointment request, not a confirmed booking, and agree to be contacted about my enquiry. <span>*</span></span></label>
        <div class="field-full"><button class="btn btn-primary" type="submit">Request appointment <span class="arrow">→</span></button><p class="form-note">A team member will follow up to discuss availability. No appointment is confirmed by this form.</p></div>
    </div>
</form>
