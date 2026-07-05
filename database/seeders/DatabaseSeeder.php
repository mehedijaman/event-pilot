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
            'name' => 'FIFA World Cup 2026 - Live Match Viewing',
            'slug' => 'fifa-world-cup-2026-live-match',
            'description' => 'Experience the thrill of the FIFA World Cup 2026 on the big screen! Join thousands of football fans for a live match screening with surround sound, live commentary, and an electrifying stadium atmosphere. Food and beverages available on-site.',
            'event_date' => '2026-07-19 20:00:00',
            'venue' => 'Bashundhara Kings Arena, Dhaka',
            'indoor_capacity' => 500,
            'outdoor_capacity' => 300,
            'registration_opens_at' => now()->subDay(),
            'registration_closes_at' => now()->addMonths(2),
            'is_active' => true,
        ]);

        Package::create([
            'event_id' => $event->id,
            'name' => 'General Stand',
            'slug' => 'general-stand',
            'price' => 500.00,
            'requires_student_verification' => false,
            'description' => 'Open-air outdoor seating with big screen viewing.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Package::create([
            'event_id' => $event->id,
            'name' => 'VIP Lounge',
            'slug' => 'vip-lounge',
            'price' => 1500.00,
            'requires_student_verification' => false,
            'description' => 'Air-conditioned indoor seating with premium view, complimentary snacks and beverages.',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Package::create([
            'event_id' => $event->id,
            'name' => 'Student Pass',
            'slug' => 'student-pass',
            'price' => 300.00,
            'requires_student_verification' => true,
            'description' => 'Discounted entry for students. Valid student ID required at entry.',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Setting::create([
            'site_name' => 'Match Day Live',
            'slogan' => 'Feel the roar of the World Cup',
            'contact_email' => 'info@matchdaylive.test',
            'contact_phone' => '+8801700000000',
            'contact_address' => 'Bashundhara Kings Arena, Dhaka, Bangladesh',
            'social_facebook' => 'https://facebook.com/matchdaylive',
            'social_twitter' => 'https://twitter.com/matchdaylive',
            'social_instagram' => 'https://instagram.com/matchdaylive',
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
