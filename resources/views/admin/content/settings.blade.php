@extends('layouts.admin', ['title' => 'Website settings'])

@section('content')
<div class="admin-head">
    <div><p class="eyebrow">Website CMS</p><h1>Website settings</h1></div>
</div>

<div class="admin-panel">
    <p class="section-intro">These approved public values are used by the site shell and footer. Keep patient or clinical information out of this area.</p>
    <form class="admin-form" method="POST" action="{{ route('admin.settings.update') }}" style="margin-top:22px">
        @csrf @method('PATCH')
        <div class="field"><label for="support_phone">Support phone</label><input id="support_phone" name="support_phone" value="{{ $settings['support_phone'] }}" required></div>
        <div class="field"><label for="general_email">General email</label><input id="general_email" type="email" name="general_email" value="{{ $settings['general_email'] }}" required></div>
        <div class="field"><label for="tagline">Public tagline</label><input id="tagline" name="tagline" value="{{ $settings['tagline'] }}" required></div>
        <div class="field field-full"><label for="footer_note">Footer note</label><textarea id="footer_note" name="footer_note" required>{{ $settings['footer_note'] }}</textarea></div>
        <div class="field field-full" style="justify-content:end"><button class="btn btn-primary">Save public settings</button></div>
    </form>
</div>
@endsection
