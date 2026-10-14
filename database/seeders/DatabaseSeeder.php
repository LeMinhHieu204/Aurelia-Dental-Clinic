<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorSpecialty;
use App\Models\Location;
use App\Models\Review;
use App\Models\Role;
use App\Models\Treatment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'admin'], ['description' => 'Full operational access']);
        Role::firstOrCreate(['name' => 'staff'], ['description' => 'Clinic operations access']);

        $location = Location::updateOrCreate(['slug' => 'aurelia-demo-clinic'], [
            'name' => 'Aurelia Dental Clinic (Demo)', 'timezone' => 'UTC', 'phone' => '+1 (800) 452-2876',
            'email' => 'hello@aureliadental.example', 'is_active' => true, 'is_demo' => true,
            'opening_hours' => ['monday_friday' => 'Configured in admin', 'saturday' => 'Configured in admin', 'sunday' => 'Closed'],
        ]);

        $treatmentData = [
            ['name' => 'Clear Aligners', 'short_description' => 'A discreet, consultation-led way to explore a straighter smile.', 'cover_image_url' => '/images/aurelia-demo-treatment.png'],
            ['name' => 'Dental Implants', 'short_description' => 'A considered option for replacing missing teeth and restoring function.', 'cover_image_url' => '/images/aurelia-demo-treatment.png'],
            ['name' => 'Teeth Whitening', 'short_description' => 'Professional guidance for a brighter smile, with safety at the centre.', 'cover_image_url' => '/images/aurelia-demo-treatment.png'],
            ['name' => 'General Dentistry', 'short_description' => 'Everyday dental care for individuals and families, from check-ups onward.', 'cover_image_url' => '/images/aurelia-demo-treatment.png'],
            ['name' => 'Preventive Care', 'short_description' => 'Practical support to help you look after your teeth and gums over time.', 'cover_image_url' => '/images/aurelia-demo-treatment.png'],
        ];
        $treatments = collect($treatmentData)->map(fn (array $data, int $order) => Treatment::updateOrCreate(['slug' => Str::slug($data['name'])], $data + ['is_published' => true, 'is_demo' => true, 'display_order' => $order]))->all();
        $location->treatments()->syncWithoutDetaching(collect($treatments)->pluck('id')->all());

        $specialty = DoctorSpecialty::firstOrCreate(['slug' => 'general-dentistry'], ['name' => 'General Dentistry']);
        $doctorData = [
            ['name' => 'Dr. Emily Carter', 'role' => 'Cosmetic & General Dentistry'],
            ['name' => 'Dr. James Miller', 'role' => 'Restorative & Implant Dentistry'],
            ['name' => 'Dr. Sophia Lee', 'role' => 'Aligners & Aesthetic Dentistry'],
        ];
        foreach ($doctorData as $order => $data) {
            $doctor = Doctor::updateOrCreate(['slug' => Str::slug($data['name'])], ['full_name' => $data['name'], 'professional_title' => $data['role'], 'portrait_url' => '/images/aurelia-demo-doctor.png', 'bio' => 'Demo profile — replace with a verified professional biography before publication.', 'is_active' => true, 'is_published' => true, 'is_demo' => true, 'display_order' => $order]);
            $doctor->specialties()->syncWithoutDetaching([$specialty->id]);
            $doctor->locations()->syncWithoutDetaching([$location->id]);
            $doctor->treatments()->syncWithoutDetaching(collect($treatments)->pluck('id')->all());
        }

        Review::updateOrCreate(['approved_name' => 'Demo patient story'], ['location_label' => 'Approval required', 'body' => 'Demo content only. Replace with a permissioned patient review before publishing.', 'rating' => 5, 'permission_granted' => true, 'is_published' => true, 'is_demo' => true]);
    }
}
