<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\ContentSection;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Inquiry;
use App\Models\Location;
use App\Models\MediaAsset;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\AppointmentStatusChanged;
use App\Mail\InquiryReceived;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_security_headers_are_present_on_public_responses(): void
    {
        $this->get(route('home'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_unpublished_treatments_are_not_public(): void
    {
        $treatment = Treatment::create(['name' => 'Private draft', 'slug' => 'private-draft', 'short_description' => 'Draft', 'is_published' => false, 'is_demo' => true]);
        $this->get(route('treatments.show', $treatment))->assertNotFound();
    }

    public function test_public_booking_rejects_archived_treatments_and_private_locations(): void
    {
        $archivedTreatment = Treatment::create(['name' => 'Archived Care', 'slug' => 'archived-care', 'short_description' => 'Archived', 'is_published' => true, 'is_demo' => true, 'archived_at' => now()]);
        $privateLocation = Location::create(['name' => 'Private demo branch', 'slug' => 'private-demo-branch', 'timezone' => 'UTC', 'is_active' => true, 'public_visibility' => false, 'is_demo' => true]);

        $this->from(route('booking'))->post(route('booking.store'), [
            'full_name' => 'Archived Request', 'email' => 'archived-request@example.com', 'phone' => '+1 555 0195',
            'treatment_id' => $archivedTreatment->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1',
        ])->assertRedirect(route('booking'))->assertSessionHasErrors('treatment_id');

        $this->from(route('booking'))->post(route('booking.store'), [
            'full_name' => 'Private Location Request', 'email' => 'private-location@example.com', 'phone' => '+1 555 0194',
            'treatment_id' => Treatment::first()->id, 'location_id' => $privateLocation->id,
            'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1',
        ])->assertRedirect(route('booking'))->assertSessionHasErrors('location_id');
    }

    public function test_valid_appointment_request_is_persisted(): void
    {
        $response = $this->post(route('booking.store'), [
            'full_name' => 'Test Patient', 'email' => 'patient@example.com', 'phone' => '+1 555 0100',
            'treatment_id' => Treatment::first()->id, 'doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDay()->toDateString(), 'preferred_time' => '10:30', 'privacy_acknowledgement' => '1',
        ]);

        $response->assertRedirect(route('booking'));
        $this->assertDatabaseHas('appointment_requests', ['status' => AppointmentRequest::STATUS_REQUESTED, 'reference' => AppointmentRequest::query()->value('reference')]);
        $this->assertDatabaseCount('appointment_status_histories', 1);
    }

    public function test_admin_dashboard_requires_admin_role(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_doctor_location_relationship_is_validated(): void
    {
        $otherLocation = Location::create(['name' => 'Other demo location', 'slug' => 'other-demo-location', 'timezone' => 'UTC', 'is_active' => true, 'is_demo' => true]);
        $response = $this->from(route('booking'))->post(route('booking.store'), [
            'full_name' => 'Test Patient', 'email' => 'relationship@example.com', 'phone' => '+1 555 0101',
            'treatment_id' => Treatment::first()->id, 'doctor_id' => Doctor::first()->id, 'location_id' => $otherLocation->id,
            'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1',
        ]);

        $response->assertRedirect(route('booking'))->assertSessionHasErrors('doctor_id');
        $this->assertDatabaseCount('appointment_requests', 0);
    }

    public function test_confirmed_requests_cannot_share_a_doctor_date_and_time(): void
    {
        $payload = [
            'full_name' => 'Patient One', 'email' => 'one@example.com', 'phone' => '+1 555 0102',
            'treatment_id' => Treatment::first()->id, 'doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDays(2)->toDateString(), 'preferred_time' => '11:00', 'privacy_acknowledgement' => '1',
        ];
        $this->post(route('booking.store'), $payload);
        $second = $this->post(route('booking.store'), $payload + ['full_name' => 'Patient Two', 'email' => 'two@example.com']);
        $admin = User::create(['name' => 'Scheduler', 'email' => 'scheduler@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $firstRequest = AppointmentRequest::first();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $firstRequest), ['status' => AppointmentRequest::STATUS_CONFIRMED])->assertSessionHasNoErrors();
        $secondRequest = AppointmentRequest::latest('id')->first();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $secondRequest), ['status' => AppointmentRequest::STATUS_CONFIRMED])->assertSessionHasErrors('status');
    }

    public function test_admin_can_assign_and_reschedule_a_request(): void
    {
        $this->post(route('booking.store'), [
            'full_name' => 'Schedule Patient', 'email' => 'schedule@example.com', 'phone' => '+1 555 0103',
            'treatment_id' => Treatment::first()->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1',
        ]);
        $admin = User::create(['name' => 'Scheduler', 'email' => 'schedule-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $appointment = AppointmentRequest::first();

        $this->actingAs($admin)->patch(route('admin.appointments.details', $appointment), [
            'doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDays(3)->toDateString(), 'preferred_time' => '14:00',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('appointment_requests', ['id' => $appointment->id, 'doctor_id' => Doctor::first()->id, 'preferred_time' => '14:00']);
    }

    public function test_admin_can_edit_content_and_triage_inquiries(): void
    {
        $admin = User::create(['name' => 'Content Admin', 'email' => 'content-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $doctor = Doctor::first();
        $treatment = Treatment::first();
        $location = Location::first();
        $inquiry = Inquiry::create(['name' => 'Patient', 'email' => 'patient@example.com', 'message' => 'Please call me.', 'status' => Inquiry::STATUS_NEW]);

        $this->actingAs($admin)->patch(route('admin.doctors.update', $doctor), [
            'full_name' => 'Dr. Updated Carter', 'professional_title' => 'Senior Dentist', 'portrait_url' => '', 'is_active' => '1', 'is_published' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.treatments.update', $treatment), [
            'name' => 'Updated Care', 'short_description' => 'Updated description.', 'cover_image_url' => '', 'is_published' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.locations.update', $location), [
            'name' => 'Updated Clinic', 'phone' => '+1 555 0199', 'email' => 'updated@example.com', 'timezone' => 'UTC', 'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.inquiries.update', $inquiry), ['status' => 'CONTACTED'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'slug' => 'dr-updated-carter']);
        $this->assertDatabaseHas('treatments', ['id' => $treatment->id, 'slug' => 'updated-care']);
        $this->assertDatabaseHas('locations', ['id' => $location->id, 'slug' => 'updated-clinic']);
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id, 'status' => 'CONTACTED']);
    }

    public function test_admin_can_update_public_website_settings(): void
    {
        $admin = User::create(['name' => 'Settings Admin', 'email' => 'settings-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);

        $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'support_phone' => '+1 555 0123',
            'general_email' => 'care@example.com',
            'tagline' => 'Care that feels personal.',
            'footer_note' => 'Approved clinic details.',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'general_email', 'value' => 'care@example.com', 'is_public' => true]);
        $this->get(route('home'))->assertSee('care@example.com')->assertSee('Approved clinic details.');
        $this->assertDatabaseCount('audit_logs', 4);
    }

    public function test_patient_contact_can_be_anonymized_without_deleting_booking_history(): void
    {
        $this->post(route('booking.store'), [
            'full_name' => 'Retention Patient', 'email' => 'retention@example.com', 'phone' => '+1 555 0196',
            'treatment_id' => Treatment::first()->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1',
        ]);
        $patient = \App\Models\Patient::where('email', 'retention@example.com')->firstOrFail();
        $appointmentId = $patient->appointments()->value('id');
        $admin = User::create(['name' => 'Retention Admin', 'email' => 'retention-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);

        $this->actingAs($admin)->patch(route('admin.patients.anonymize', $patient))->assertRedirect(route('admin.patients'));

        $this->actingAs($admin)->get(route('admin.patients'))->assertOk()->assertSee('Anonymized patient');
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'full_name' => 'Anonymized patient', 'phone' => 'REDACTED']);
        $this->assertDatabaseHas('appointment_requests', ['id' => $appointmentId, 'patient_id' => $patient->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'patient.anonymized', 'auditable_id' => $patient->id]);
    }

    public function test_admin_can_create_doctor_treatment_and_location_relationships(): void
    {
        $admin = User::create(['name' => 'Operations Admin', 'email' => 'operations-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $doctorUser = User::create(['name' => 'New Doctor Login', 'email' => 'new-doctor@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'doctor')->value('id')]);
        $location = Location::first();
        $treatment = Treatment::first();

        $this->actingAs($admin)->post(route('admin.doctors.store'), [
            'full_name' => 'Dr. New Specialist', 'professional_title' => 'Specialist Dentist', 'specialties' => 'Endodontics, Restorative Care',
            'location_ids' => [$location->id], 'treatment_ids' => [$treatment->id], 'user_id' => $doctorUser->id,
        ])->assertSessionHasNoErrors();
        $doctor = Doctor::where('full_name', 'Dr. New Specialist')->firstOrFail();

        $this->assertDatabaseHas('doctor_location', ['doctor_id' => $doctor->id, 'location_id' => $location->id]);
        $this->assertDatabaseHas('doctor_treatment', ['doctor_id' => $doctor->id, 'treatment_id' => $treatment->id]);
        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'user_id' => $doctorUser->id]);
        $this->assertDatabaseHas('doctor_specialties', ['slug' => 'endodontics']);

        $this->actingAs($admin)->post(route('admin.treatments.store'), ['name' => 'New Treatment', 'short_description' => 'Approved description.', 'price_currency' => 'USD', 'consultation_required' => '1', 'doctor_ids' => [$doctor->id]])->assertSessionHasNoErrors();
        $newTreatment = Treatment::where('slug', 'new-treatment')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.locations.store'), ['name' => 'New Location', 'timezone' => 'America/New_York', 'treatment_ids' => [$newTreatment->id]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('treatments', ['slug' => 'new-treatment', 'is_published' => false]);
        $this->assertDatabaseHas('locations', ['slug' => 'new-location', 'is_active' => false]);
        $this->assertDatabaseHas('doctor_treatment', ['doctor_id' => $doctor->id, 'treatment_id' => $newTreatment->id]);
        $newLocation = Location::where('slug', 'new-location')->firstOrFail();
        $this->assertDatabaseHas('location_treatment', ['location_id' => $newLocation->id, 'treatment_id' => $newTreatment->id]);
    }

    public function test_admin_content_slugs_remain_unique_without_overwriting_records(): void
    {
        $admin = User::create(['name' => 'Slug Admin', 'email' => 'slug-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);

        $payload = ['name' => 'Same Treatment', 'short_description' => 'Approved description.', 'price_currency' => 'USD'];
        $this->actingAs($admin)->post(route('admin.treatments.store'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.treatments.store'), $payload)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('treatments', ['slug' => 'same-treatment']);
        $this->assertDatabaseHas('treatments', ['slug' => 'same-treatment-2']);
    }

    public function test_doctor_archive_preserves_history_and_restores_as_draft(): void
    {
        $admin = User::create(['name' => 'Archive Admin', 'email' => 'archive-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $doctor = Doctor::firstOrFail();
        $this->post(route('booking.store'), [
            'full_name' => 'Confirmed History Patient', 'email' => 'confirmed-history@example.com', 'phone' => '+1 555 0177',
            'treatment_id' => Treatment::first()->id, 'doctor_id' => $doctor->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDays(5)->toDateString(), 'privacy_acknowledgement' => '1',
        ]);
        $appointment = AppointmentRequest::firstOrFail();
        $appointment->update(['status' => AppointmentRequest::STATUS_CONFIRMED]);

        $this->actingAs($admin)->patch(route('admin.doctors.archive', $doctor))->assertSessionHasErrors('doctor');
        $this->assertDatabaseHas('doctors', ['id' => $doctor->id, 'archived_at' => null]);

        $appointment->update(['status' => AppointmentRequest::STATUS_CANCELLED]);
        $this->actingAs($admin)->patch(route('admin.doctors.archive', $doctor))->assertSessionHasNoErrors();
        $doctor->refresh();
        $this->assertNotNull($doctor->archived_at);
        $this->assertFalse($doctor->is_active);
        $this->assertDatabaseHas('appointment_requests', ['id' => $appointment->id, 'status' => AppointmentRequest::STATUS_CANCELLED]);

        $this->actingAs($admin)->patch(route('admin.doctors.archive', $doctor))->assertSessionHasNoErrors();
        $doctor->refresh();
        $this->assertNull($doctor->archived_at);
        $this->assertFalse($doctor->is_active);
        $this->assertFalse($doctor->is_published);
    }

    public function test_review_moderation_requires_permission_and_supports_anonymization(): void
    {
        $admin = User::create(['name' => 'Review Admin', 'email' => 'review-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $review = Review::create(['approved_name' => 'Patient initials', 'location_label' => 'Demo clinic', 'body' => 'Permissioned story.', 'rating' => 5, 'permission_granted' => false, 'is_published' => false, 'is_demo' => true]);

        $this->actingAs($admin)->patch(route('admin.reviews.toggle', $review))->assertSessionHasErrors('review');
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'is_published' => false]);
        $this->actingAs($admin)->patch(route('admin.reviews.update', $review), [
            'approved_name' => 'Patient initials', 'location_label' => 'Demo clinic', 'body' => 'Permissioned story.', 'rating' => 5,
            'permission_granted' => '1', 'is_published' => '1', 'source' => 'Written consent',
        ])->assertSessionHasNoErrors();
        $this->get(route('reviews'))->assertSee('Permissioned story.');

        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'approved_name' => 'Anonymous patient', 'is_published' => false, 'permission_granted' => false]);
    }

    public function test_availability_and_schedule_blocks_are_manageable_and_enforced(): void
    {
        $admin = User::create(['name' => 'Calendar Admin', 'email' => 'calendar-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $date = today()->addDays(4);
        DoctorAvailability::create(['doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id, 'weekday' => $date->dayOfWeek, 'starts_at' => '09:00', 'ends_at' => '12:00', 'timezone' => 'UTC', 'is_active' => true]);

        $this->post(route('booking.store'), ['full_name' => 'Hours Patient', 'email' => 'hours@example.com', 'phone' => '+1 555 0122', 'treatment_id' => Treatment::first()->id, 'doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id, 'preferred_date' => $date->toDateString(), 'preferred_time' => '13:00', 'privacy_acknowledgement' => '1']);
        $appointment = AppointmentRequest::first();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $appointment), ['status' => AppointmentRequest::STATUS_CONFIRMED])->assertSessionHasErrors('status');

        $this->actingAs($admin)->post(route('admin.availability.blocks.store'), ['doctor_id' => Doctor::first()->id, 'blocked_date' => $date->copy()->addDay()->toDateString(), 'reason' => 'Annual leave'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('schedule_blocks', ['doctor_id' => Doctor::first()->id, 'reason' => 'Annual leave']);
        $exceptionDate = today()->addDays(8);
        $this->actingAs($admin)->post(route('admin.availability.store'), ['doctor_id' => Doctor::first()->id, 'available_date' => $exceptionDate->toDateString(), 'weekday' => '', 'starts_at' => '10:00', 'ends_at' => '14:00', 'timezone' => 'UTC'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('doctor_availability', ['doctor_id' => Doctor::first()->id, 'available_date' => $exceptionDate->toDateString(), 'weekday' => $exceptionDate->dayOfWeek]);

        Location::first()->update(['closure_days' => [$date->toDateString()]]);
        $this->post(route('booking.store'), ['full_name' => 'Closed Day Patient', 'email' => 'closed@example.com', 'phone' => '+1 555 0123', 'treatment_id' => Treatment::first()->id, 'doctor_id' => Doctor::first()->id, 'location_id' => Location::first()->id, 'preferred_date' => $date->toDateString(), 'preferred_time' => '10:00', 'privacy_acknowledgement' => '1']);
        $closedAppointment = AppointmentRequest::latest('id')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $closedAppointment), ['status' => AppointmentRequest::STATUS_CONFIRMED])->assertSessionHasErrors('status');
    }

    public function test_media_upload_and_cms_publication_are_real_workflows(): void
    {
        Storage::fake('public');
        $admin = User::create(['name' => 'CMS Admin', 'email' => 'cms-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);

        $this->actingAs($admin)->post(route('admin.media.store'), ['images' => [UploadedFile::fake()->image('clinic.webp', 400, 300), UploadedFile::fake()->image('clinic-detail.png', 320, 240)], 'alt_text' => 'Clinic reception', 'license' => 'Clinic owned'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('media_assets', 2);
        $this->assertDatabaseHas('media_assets', ['filename' => 'clinic.webp', 'alt_text' => 'Clinic reception']);
        $asset = MediaAsset::firstOrFail();
        Storage::disk('public')->assertExists($asset->path);
        $oldPath = $asset->path;
        $linkedTreatment = Treatment::firstOrFail();
        $linkedTreatment->update(['cover_image_url' => Storage::disk('public')->url($oldPath)]);
        $this->actingAs($admin)->patch(route('admin.media.replace', $asset), ['image' => UploadedFile::fake()->image('clinic-replaced.png', 320, 240)])->assertSessionHasNoErrors();
        $asset->refresh();
        $this->assertNotSame($oldPath, $asset->path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($asset->path);
        $this->assertDatabaseHas('treatments', ['id' => $linkedTreatment->id, 'cover_image_url' => Storage::disk('public')->url($asset->path)]);

        $section = ContentSection::create(['key' => 'home.hero.title', 'title' => 'Draft title', 'body' => null, 'image_url' => Storage::disk('public')->url($asset->path), 'is_published' => false]);
        $this->actingAs($admin)->delete(route('admin.media.destroy', $asset))->assertSessionHasErrors('media');
        $this->assertDatabaseHas('media_assets', ['id' => $asset->id]);
        $this->actingAs($admin)->patch(route('admin.content.update', $section), ['title' => 'A More Personal Smile', 'body' => null, 'seo_description' => '', 'sort_order' => 1, 'is_published' => '1'])->assertSessionHasNoErrors();
        $this->get(route('home'))->assertSee('A More Personal Smile');

        $pageSection = ContentSection::create(['key' => 'treatments.page.title', 'title' => 'Care shaped around your goals', 'body' => null, 'is_published' => true, 'published_at' => now()]);
        $this->get(route('treatments.index'))->assertSee('Care shaped around your goals');
    }

    public function test_inquiry_notification_and_role_permissions_are_enforced(): void
    {
        Mail::fake();
        config(['aurelia.notifications.staff_email' => 'staff@example.com']);
        $this->post(route('contact.store'), ['name' => 'Inquiry Person', 'email' => 'inquiry@example.com', 'phone' => '+1 555 0109', 'message' => 'Please call me.'])->assertSessionHasNoErrors();
        Mail::assertQueued(InquiryReceived::class);

        $receptionistRole = Role::where('name', 'receptionist')->firstOrFail();
        $receptionist = User::create(['name' => 'Receptionist', 'email' => 'receptionist@example.com', 'password' => 'not-used', 'role_id' => $receptionistRole->id]);
        $this->actingAs($receptionist)->get(route('admin.patients'))->assertOk();
        $this->actingAs($receptionist)->get(route('admin.media'))->assertForbidden();
        $this->actingAs($receptionist)->get(route('admin.audit-logs'))->assertForbidden();
    }

    public function test_patient_write_actions_require_distinct_permissions(): void
    {
        $patient = Patient::create(['full_name' => 'Permission Patient', 'email' => 'permission@example.com', 'phone' => '+1 555 0199']);
        $receptionist = User::create([
            'name' => 'Patient Receptionist',
            'email' => 'patient-receptionist@example.com',
            'password' => 'not-used',
            'role_id' => Role::where('name', 'receptionist')->value('id'),
        ]);

        $this->actingAs($receptionist)
            ->patch(route('admin.patients.update', $patient), ['full_name' => 'Changed', 'email' => 'changed@example.com', 'phone' => '+1 555 0101'])
            ->assertForbidden();
        $this->actingAs($receptionist)
            ->patch(route('admin.patients.anonymize', $patient))
            ->assertForbidden();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'full_name' => 'Permission Patient']);

        $admin = User::create([
            'name' => 'Patient Admin',
            'email' => 'patient-admin@example.com',
            'password' => 'not-used',
            'role_id' => Role::where('name', 'admin')->value('id'),
        ]);
        $this->actingAs($admin)
            ->patch(route('admin.patients.update', $patient), ['full_name' => 'Updated Patient', 'email' => 'updated@example.com', 'phone' => '+1 555 0102'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'full_name' => 'Updated Patient', 'email' => 'updated@example.com']);

        $this->actingAs($admin)->patch(route('admin.patients.anonymize', $patient))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('patients', ['id' => $patient->id, 'full_name' => 'Anonymized patient', 'phone' => 'REDACTED']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'patient.anonymized', 'auditable_id' => $patient->id]);
    }

    public function test_invalid_appointment_state_transition_is_rejected(): void
    {
        $this->post(route('booking.store'), ['full_name' => 'Transition Patient', 'email' => 'transition@example.com', 'phone' => '+1 555 0110', 'treatment_id' => Treatment::first()->id, 'location_id' => Location::first()->id, 'preferred_date' => today()->addDay()->toDateString(), 'privacy_acknowledgement' => '1']);
        $admin = User::create(['name' => 'Transition Admin', 'email' => 'transition-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        $appointment = AppointmentRequest::first();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $appointment), ['status' => AppointmentRequest::STATUS_CANCELLED])->assertSessionHasNoErrors();
        $this->actingAs($admin)->patch(route('admin.appointments.status', $appointment), ['status' => AppointmentRequest::STATUS_CONFIRMED])->assertSessionHasErrors('status');
    }

    public function test_admin_operational_modules_render(): void
    {
        $admin = User::create(['name' => 'Module Admin', 'email' => 'module-admin@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'admin')->value('id')]);
        foreach ([
            'admin.calendar', 'admin.availability', 'admin.patients', 'admin.media', 'admin.content', 'admin.audit-logs', 'admin.settings',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_doctor_can_only_view_their_own_read_only_calendar(): void
    {
        $orphanDoctorUser = User::create(['name' => 'Unlinked Doctor', 'email' => 'unlinked-doctor@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'doctor')->value('id')]);
        $this->actingAs($orphanDoctorUser)->get(route('admin.calendar'))->assertForbidden();

        $doctorUser = User::create(['name' => 'Assigned Doctor', 'email' => 'assigned-doctor@example.com', 'password' => 'not-used', 'role_id' => Role::where('name', 'doctor')->value('id')]);
        $doctor = Doctor::firstOrFail();
        $doctor->update(['user_id' => $doctorUser->id]);

        $this->post(route('booking.store'), [
            'full_name' => 'Private Calendar Patient', 'email' => 'private-calendar@example.com', 'phone' => '+1 555 0198',
            'treatment_id' => Treatment::first()->id, 'doctor_id' => $doctor->id, 'location_id' => Location::first()->id,
            'preferred_date' => today()->addDay()->toDateString(), 'preferred_time' => '10:30', 'privacy_acknowledgement' => '1',
        ]);
        $appointment = AppointmentRequest::firstOrFail();
        $this->actingAs($doctorUser)->get(route('admin.dashboard'))->assertRedirect(route('admin.calendar'));
        $this->actingAs($doctorUser)->get(route('admin.calendar', ['view' => 'day', 'date' => $appointment->preferred_date->toDateString()]))
            ->assertOk()->assertSee($appointment->reference);
        $this->actingAs($doctorUser)->get(route('admin.appointments.show', $appointment))
            ->assertOk()->assertSee('Private Calendar Patient')->assertDontSee('private-calendar@example.com');
        $this->actingAs($doctorUser)->patch(route('admin.appointments.status', $appointment), ['status' => AppointmentRequest::STATUS_CONTACTED])
            ->assertForbidden();
        $this->actingAs($doctorUser)->get(route('admin.media'))->assertForbidden();
        $this->actingAs($doctorUser)->get(route('admin.doctors'))->assertForbidden();
    }
}
