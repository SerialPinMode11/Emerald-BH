<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\ChangeRequest;
use App\Models\Property;
use App\Models\PropertyMedia;
use App\Models\RentalAgreement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\RentPaymentScheduleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmeraldSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@emerald.test'],
            ['name' => 'Alex Rivera', 'password' => $password, 'role' => UserRole::Customer, 'is_active' => true],
        );

        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@emerald.test'],
            ['name' => 'Julian Sterling', 'password' => $password, 'role' => UserRole::LandOwner, 'is_active' => true],
        );

        $community = User::query()->updateOrCreate(
            ['email' => 'community@emerald.test'],
            ['name' => 'Maya Chen', 'password' => $password, 'role' => UserRole::Community, 'is_active' => true],
        );

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@emerald.test'],
            ['name' => 'Elena Voss', 'password' => $password, 'role' => UserRole::SuperAdmin, 'is_active' => true],
        );

        User::query()->updateOrCreate(
            ['email' => 'dev@emerald.test'],
            ['name' => 'Sam Ortiz', 'password' => $password, 'role' => UserRole::DevAdmin, 'is_active' => true],
        );

        $images = [
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAW_XGaoKy_WHY08MWcIkUOU6kDDRWPBXOp5CfL69uXP_5xYWy3MJeGD3Cm9MH61t7KtwTxeKpxGNCO7fYdGIsBPCzGZLJl-6XAMJsKeZ1OLC41A2OO532GTrZffHmGLgiTu3BonifnELfkfIUpDPhKzojRZhp7pZU8Q8gaHPn881sPW4hpag9orvr4-YncWstJ4srLtTyTeoO10f4rd7TdxChYzzaE7fsKd9sn7D4KSMvcxLDfHYksuSCuoym3JnK5sB3HXUQ7Bi4',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuCl_renApMJugN9liMdLgFkZXzOlRxZh2Xa0Dk0EWMURHU-eyIdypIKEytAW06rD2o5cMqRRz10DrvEmJCN3Etxzdcdem3LNjYvEBMGl0UZQyOryrZgAo56bbHHKtTmj258iXGc-M4tluBOKCmeMHGydipIUF_qhzb6MgHUmSUFUJt-3tV9W5qF-BpsyPgekUCfCXv5V74dIORTObJUGfdBe_VLlOFDZVTxqNdF3ueHL2smH41rYINZTyrUMlgy02JX4bT-5ZNAWKY',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAG3mnw12-YJs5nInBiptepsOSysFSPtH8bExMK-Pad0-7bp7VFhDg8VcJUwFdu-JwlPGIBVHwSAgWYzrUWcxCoX7abcs38dsh-rO0eiOsUr_1iT2CbxATg6ujvzZBDAC7gLuqX8U4BpiPJLwHackIU5xzrkdpOSu8A5dOBL7qPMRI0DaD1BA7njKQu0al3SnDWsjQAtWqo6wMHaiL60ADmRXOHQkW68HfyOhPJZyYZi90TMSBh8d8OE55pPo3wxmEut8X73arccj8',
            'https://lh3.googleusercontent.com/aida-public/AB6AXuAcc-HTuIhCSf6FdMN7iRxbyiOGsGUNUOqCBojQlar0TF6szFXjHizaGYwmTzEWhOfFWifYrR-6Fw90gLPdf9p8MbYM2MUuZ65e56ISI565pAYV8JP43vEmPxRLbLjUEDe-_Fg1FGDIFYTy_uvqJ9MGRrKVtI-xnKbYzCO8iwHaCBvaedSoDQIDBLwgtI3TAorq5YsgpiIkag5ft6GfddzkFJadOoWsWSXke3mogbgSGY5ZE-lKDMU-NvVrtvRQg3GN8PKYBar3Llo',
        ];

        $approved = Property::query()->updateOrCreate(
            ['title' => 'Azure Skyline Penthouse', 'land_owner_id' => $owner->id],
            [
                'description' => 'Luxury penthouse with skyline views.',
                'address' => '100 Market St',
                'city' => 'San Francisco',
                'price_per_month' => 5400,
                'deposit' => 10800,
                'terms_of_rental' => 'Minimum 12 months. No pets.',
                'room_length_m' => 12,
                'room_width_m' => 8,
                'room_height_m' => 3,
                'status' => 'approved',
                'approved_by' => $superAdmin->id,
                'approved_at' => now(),
            ],
        );

        PropertyMedia::query()->updateOrCreate(
            ['property_id' => $approved->id, 'is_primary' => true],
            ['url' => $images[0], 'type' => 'image', 'sort_order' => 0],
        );

        $pending = Property::query()->updateOrCreate(
            ['title' => 'Foundry Loft A12', 'land_owner_id' => $owner->id],
            [
                'description' => 'Industrial chic urban loft.',
                'address' => '42 Foundry Ave',
                'city' => 'New York',
                'price_per_month' => 2850,
                'deposit' => 5700,
                'terms_of_rental' => 'Minimum 6 months.',
                'room_length_m' => 6,
                'room_width_m' => 5,
                'room_height_m' => 2.8,
                'status' => 'pending',
            ],
        );

        PropertyMedia::query()->updateOrCreate(
            ['property_id' => $pending->id, 'is_primary' => true],
            ['url' => $images[2], 'type' => 'image', 'sort_order' => 0],
        );

        foreach ([
            ['title' => 'Oakwood Estates', 'city' => 'Seattle', 'price' => 2800, 'status' => 'approved', 'image' => $images[1]],
            ['title' => 'Modern Forest Haven', 'city' => 'Portland', 'price' => 2100, 'status' => 'approved', 'image' => $images[3]],
            ['title' => 'Willow Creek Estate', 'city' => 'Austin', 'price' => 5100, 'status' => 'pending', 'image' => $images[3]],
        ] as $index => $data) {
            $property = Property::query()->updateOrCreate(
                ['title' => $data['title'], 'land_owner_id' => $owner->id],
                [
                    'description' => 'Premium rental property.',
                    'address' => '200 Emerald Way',
                    'city' => $data['city'],
                    'price_per_month' => $data['price'],
                    'deposit' => $data['price'],
                    'terms_of_rental' => 'Standard lease terms apply.',
                    'room_length_m' => 7 + $index,
                    'room_width_m' => 5,
                    'room_height_m' => 2.6,
                    'status' => $data['status'],
                    'approved_by' => $data['status'] === 'approved' ? $superAdmin->id : null,
                    'approved_at' => $data['status'] === 'approved' ? now() : null,
                ],
            );

            PropertyMedia::query()->updateOrCreate(
                ['property_id' => $property->id, 'is_primary' => true],
                ['url' => $data['image'], 'type' => 'image', 'sort_order' => $index],
            );
        }

        $agreement = RentalAgreement::query()->updateOrCreate(
            ['property_id' => $approved->id, 'customer_id' => $customer->id],
            [
                'community_id' => $community->id,
                'start_date' => now()->addMonth(),
                'end_date' => now()->addYear(),
                'total_rent' => 5400,
                'deposit_paid' => false,
                'status' => 'community_review',
                'community_notes' => 'Terms verified. Awaiting signatures.',
            ],
        );

        Transaction::query()->updateOrCreate(
            ['rental_agreement_id' => $agreement->id, 'type' => 'deposit'],
            [
                'amount' => 10800,
                'status' => 'pending',
                'payment_method' => 'card',
            ],
        );

        Transaction::query()->updateOrCreate(
            ['rental_agreement_id' => $agreement->id, 'type' => 'rent', 'billing_month' => null],
            [
                'amount' => 5400,
                'status' => 'disputed',
                'payment_method' => 'card',
                'disputed_by' => $customer->id,
                'resolution' => null,
            ],
        );

        $rented = Property::query()->updateOrCreate(
            ['title' => 'Harbor View Studio', 'land_owner_id' => $owner->id],
            [
                'description' => 'Compact studio with harbor views.',
                'address' => '12 Harbor Lane',
                'city' => 'Sample City',
                'price_per_month' => 150,
                'deposit' => 300,
                'terms_of_rental' => 'Month-to-month after first 3 months.',
                'room_length_m' => 4,
                'room_width_m' => 3.5,
                'room_height_m' => 2.4,
                'status' => 'rented',
                'approved_by' => $superAdmin->id,
                'approved_at' => now()->subMonths(3),
            ],
        );

        PropertyMedia::query()->updateOrCreate(
            ['property_id' => $rented->id, 'is_primary' => true],
            ['url' => $images[1], 'type' => 'image', 'sort_order' => 0],
        );

        $activeRental = RentalAgreement::query()->updateOrCreate(
            ['property_id' => $rented->id, 'customer_id' => $customer->id],
            [
                'community_id' => $community->id,
                'start_date' => now()->subMonths(2)->startOfMonth(),
                'end_date' => now()->addMonths(10)->startOfMonth(),
                'total_rent' => 150,
                'deposit_paid' => true,
                'status' => 'active',
                'community_notes' => 'Activated for demo payment tracking.',
                'signed_by_customer' => now()->subMonths(2),
                'signed_by_owner' => now()->subMonths(2),
            ],
        );

        $scheduleService = app(RentPaymentScheduleService::class);
        $scheduleService->sync($activeRental);

        $firstRent = Transaction::query()
            ->where('rental_agreement_id', $activeRental->id)
            ->where('type', 'rent')
            ->orderBy('billing_month')
            ->first();

        if ($firstRent) {
            $firstRent->update([
                'status' => 'completed',
                'paid_at' => now()->subMonths(2),
            ]);
        }

        ChangeRequest::query()->updateOrCreate(
            ['description' => 'Add monthly invoice PDF export for rental agreements'],
            [
                'requested_by' => $superAdmin->id,
                'request_type' => 'feature',
                'priority' => 'high',
                'status' => 'pending',
            ],
        );

        ChangeRequest::query()->updateOrCreate(
            ['description' => 'Fix property image upload timeout on slow connections'],
            [
                'requested_by' => $superAdmin->id,
                'request_type' => 'bugfix',
                'priority' => 'critical',
                'status' => 'in_progress',
                'dev_admin_note' => 'Investigating CDN timeout settings.',
            ],
        );
    }
}
