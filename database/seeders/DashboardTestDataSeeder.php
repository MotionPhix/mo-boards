<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Billboard;
use App\Models\Company;
use App\Models\Contract;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a test company
        $company = Company::firstOrCreate([
            'name' => 'Demo Company',
        ], [
            'slug' => 'demo-billboard-company',
            'industry' => 'outdoor-advertising',
            'size' => '11-50',
            'subscription_plan' => 'free',
            'is_active' => true,
        ]);

        // Get or create a test user
        $user = User::firstOrCreate([
            'email' => 'demo@billboardfree.com',
        ], [
            'name' => 'Free User',
            'password' => bcrypt('password'),
            'current_company_id' => $company->id,
        ]);

        $user->assignRole('company_owner');

        // Attach user to company if not already attached
        if (! $company->users()->where('user_id', $user->id)->exists()) {
            $company->users()->attach($user->id, [
                'is_owner' => true,
                'role' => 'company_owner',
                'joined_at' => now(),
            ]);
        }

        // Create sample billboards
        $billboards = [
            [
                'name' => 'Highway Premium Billboard',
                'location' => 'I-95 North, Mile Marker 45',
                'width' => 48.0,
                'height' => 14.0,
                'monthly_rate' => 3500.00,
                'status' => 'active',
            ],
            [
                'name' => 'Downtown Digital Display',
                'location' => 'Main Street & 5th Avenue',
                'width' => 20.0,
                'height' => 10.0,
                'monthly_rate' => 5000.00,
                'status' => 'active',
            ],
            [
                'name' => 'Shopping Center Billboard',
                'location' => 'Westfield Mall Entrance',
                'width' => 24.0,
                'height' => 12.0,
                'monthly_rate' => 2500.00,
                'status' => 'active',
            ],
            [
                'name' => 'Airport Terminal Display',
                'location' => 'Terminal A, Gate 15',
                'width' => 12.0,
                'height' => 8.0,
                'monthly_rate' => 1800.00,
                'status' => 'maintenance',
            ],
            [
                'name' => 'Stadium Exterior',
                'location' => 'Sports Complex, North Wall',
                'width' => 60.0,
                'height' => 20.0,
                'monthly_rate' => 4500.00,
                'status' => 'active',
            ],
        ];

        foreach ($billboards as $billboardData) {
            Billboard::firstOrCreate([
                'company_id' => $company->id,
                'name' => $billboardData['name'],
            ], array_merge($billboardData, [
                'company_id' => $company->id,
                'description' => 'Sample billboard for testing dashboard functionality.',
            ]));
        }

        // Create sample contracts
        $contracts = [
            [
                'client_name' => 'Acme Corporation',
                'client_email' => 'marketing@acme.com',
                'client_phone' => '+1-555-0123',
                'client_company' => 'Acme Corp',
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->addMonths(4),
                'total_amount' => 21000.00,
                'monthly_amount' => 3500.00,
                'status' => 'active',
                'billboard_names' => ['Highway Premium Billboard'],
            ],
            [
                'client_name' => 'Tech Solutions Ltd',
                'client_email' => 'ads@techsolutions.com',
                'client_phone' => '+1-555-0456',
                'client_company' => 'Tech Solutions Ltd',
                'start_date' => Carbon::now()->subMonth(),
                'end_date' => Carbon::now()->addMonths(5),
                'total_amount' => 30000.00,
                'monthly_amount' => 5000.00,
                'status' => 'active',
                'billboard_names' => ['Downtown Digital Display'],
            ],
            [
                'client_name' => 'Green Energy Co',
                'client_email' => 'promo@greenenergy.com',
                'client_phone' => '+1-555-0789',
                'client_company' => 'Green Energy Co',
                'start_date' => Carbon::now()->subWeeks(3),
                'end_date' => Carbon::now()->addMonths(3),
                'total_amount' => 10000.00,
                'monthly_amount' => 2500.00,
                'status' => 'active',
                'billboard_names' => ['Shopping Center Billboard'],
            ],
            [
                'client_name' => 'Local Restaurant Chain',
                'client_email' => 'marketing@localchain.com',
                'client_phone' => '+1-555-0321',
                'client_company' => 'Local Restaurant Chain',
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addMonths(6),
                'total_amount' => 27000.00,
                'monthly_amount' => 4500.00,
                'status' => 'pending',
                'billboard_names' => ['Stadium Exterior'],
            ],
            [
                'client_name' => 'Fashion Retailer',
                'client_email' => 'ads@fashionstore.com',
                'client_phone' => '+1-555-0654',
                'client_company' => 'Fashion Store Inc',
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addDays(15), // Expiring soon
                'total_amount' => 12600.00,
                'monthly_amount' => 1800.00,
                'status' => 'active',
                'billboard_names' => ['Airport Terminal Display'],
            ],
        ];

        foreach ($contracts as $contractData) {
            $billboardNames = $contractData['billboard_names'];
            unset($contractData['billboard_names']);

            $contract = Contract::firstOrCreate([
                'company_id' => $company->id,
                'client_email' => $contractData['client_email'],
            ], array_merge($contractData, [
                'company_id' => $company->id,
                'created_by' => $user->id,
                'payment_terms' => 'monthly',
                'terms_and_conditions' => [
                    'Payment due within 30 days',
                    'Billboard maintenance included',
                    'Design changes allowed with 48hr notice',
                ],
            ]));

            // Attach billboards to contract
            foreach ($billboardNames as $billboardName) {
                $billboard = Billboard::where('company_id', $company->id)
                    ->where('name', $billboardName)
                    ->first();

                if ($billboard && ! $contract->billboards()->where('billboard_id', $billboard->id)->exists()) {
                    $contract->billboards()->attach($billboard->id, [
                        'rate' => $billboard->monthly_rate,
                        'notes' => 'Standard billboard rental agreement',
                    ]);
                }
            }
        }

        $this->command->info('Dashboard test data seeded successfully!');
        $this->command->info('Test user: demo@billboardfree.com / password');
        $this->command->info("Company: {$company->name}");
    }
}
