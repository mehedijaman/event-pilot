<?php

namespace Database\Seeders;

use App\Enums\StaffRole;
use App\Models\Event;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@event.test',
            'password' => Hash::make('password'),
            'role' => StaffRole::Admin,
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Scanner',
            'email' => 'scanner@event.test',
            'password' => Hash::make('password'),
            'role' => StaffRole::Scanner,
            'email_verified_at' => now(),
        ]);

        $event = Event::create([
            'name' => 'Annual Tech Conference 2026',
            'slug' => 'annual-tech-conference-2026',
            'description' => 'A premier technology conference featuring keynote speakers, workshops, and networking opportunities.',
            'event_date' => now()->addMonths(2),
            'venue' => 'Dhaka International Convention Center',
            'indoor_capacity' => 200,
            'outdoor_capacity' => 100,
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        Package::create([
            'event_id' => $event->id,
            'name' => 'Student Package',
            'slug' => 'student',
            'price' => 500.00,
            'requires_student_verification' => true,
            'description' => 'Discounted entry for students with valid student ID.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Package::create([
            'event_id' => $event->id,
            'name' => 'Normal Package',
            'slug' => 'normal',
            'price' => 1000.00,
            'requires_student_verification' => false,
            'description' => 'General admission for all attendees.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Setting::create([
            'site_name' => 'Event Ticket',
            'slogan' => 'Your Gateway to Amazing Events',
            'contact_email' => 'hello@event.test',
            'contact_phone' => '+8801700000000',
            'contact_address' => 'Dhaka, Bangladesh',
            'social_facebook' => 'https://facebook.com/event',
            'social_twitter' => 'https://twitter.com/event',
            'social_instagram' => 'https://instagram.com/event',
        ]);

        PaymentMethod::create([
            'name' => 'bKash',
            'slug' => 'bkash',
            'account_type' => 'mobile',
            'account_number' => '01XXXXXXXXX',
            'instructions' => 'Send Money to the number above. After payment, enter your bKash transaction ID in the form.',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Nagad',
            'slug' => 'nagad',
            'account_type' => 'mobile',
            'account_number' => '01XXXXXXXXX',
            'instructions' => 'Send Money to the number above. After payment, enter your Nagad transaction ID in the form.',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Rocket',
            'slug' => 'rocket',
            'account_type' => 'mobile',
            'account_number' => '01XXXXXXXXX',
            'instructions' => 'Send Money to the number above. After payment, enter your Rocket transaction ID in the form.',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Upay',
            'slug' => 'upay',
            'account_type' => 'mobile',
            'account_number' => '01XXXXXXXXX',
            'instructions' => 'Send Money to the number above. After payment, enter your Upay transaction ID in the form.',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Bank Transfer',
            'slug' => 'bank_transfer',
            'account_type' => 'bank',
            'account_number' => 'Account Name: XXXXX, A/C No: XXXX-XXXX-XXXX, Bank: XXXXX, Branch: XXXXX, Routing No: XXXXXXXX',
            'instructions' => 'Transfer the exact amount to the account above. After payment, enter your transaction reference or receipt number in the form.',
            'is_active' => true,
        ]);

        PaymentMethod::create([
            'name' => 'Cash Payment',
            'slug' => 'cash',
            'account_type' => null,
            'account_number' => null,
            'instructions' => 'Pay in cash at the event registration desk. Bring your registration confirmation.',
            'is_active' => true,
        ]);

        $this->command?->info('Admin login: admin@event.test / password');
        $this->command?->info('Scanner login: scanner@event.test / password');
    }
}
