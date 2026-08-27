<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateLegacyTenant extends Command
{
    protected $signature = 'tenants:migrate-legacy
                            {--code=SCH-LEGACY-0001 : School code for existing data}
                            {--super-name=Super Admin : Super admin display name}
                            {--super-email= : Super admin email}
                            {--super-password= : Super admin password}';

    protected $description = 'Migrate existing single-school data into the first approved tenant';

    /** @var list<string> */
    protected array $tenantTables = [
        'school_settings',
        'users',
        'staff',
        'students',
        'academic_years',
        'academic_terms',
        'school_classes',
        'class_categories',
        'courses',
        'grading_schemes',
        'assessment_types',
        'timetable_periods',
        'houses',
        'dormitories',
        'billing_items',
        'category_bill_setups',
        'student_bills',
        'bill_payments',
        'bill_payment_transactions',
        'student_bill_credit_transactions',
        'hr_departments',
        'hr_positions',
        'hr_pay_grades',
        'hr_earning_types',
        'hr_deduction_types',
        'hr_leave_types',
        'hr_payroll_settings',
        'hr_payroll_runs',
        'pos_categories',
        'pos_products',
        'pos_sales',
        'pos_sale_transactions',
        'expense_categories',
        'expenses',
        'parent_accounts',
        'parent_messages',
        'parent_communication_logs',
        'sms_messages',
        'user_cat',
        'academic_assessments',
        'class_timetables',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('schools')) {
            $this->error('Run migrations first: php artisan migrate');

            return self::FAILURE;
        }

        if (School::query()->exists()) {
            $this->warn('Schools already exist. Skipping legacy migration.');

            return self::SUCCESS;
        }

        $code = strtoupper((string) $this->option('code'));
        $settings = SchoolSetting::query()->withoutGlobalScopes()->first();

        DB::transaction(function () use ($code, $settings) {
            $this->createSuperAdmin();

            $school = School::query()->create([
                'code' => $code,
                'name' => $settings?->name ?: 'Legacy School',
                'address' => $settings?->address,
                'phone' => $settings?->phone,
                'email' => $settings?->email,
                'website' => $settings?->website,
                'status' => School::STATUS_APPROVED,
                'admin_name' => 'School Administrator',
                'admin_email' => $settings?->email ?: 'admin@example.com',
                'admin_phone' => $settings?->phone,
                'admin_password' => bcrypt('ChangeMe123!'),
                'approved_at' => now(),
            ]);

            TenantContext::forceSchool($school->id, $code);

            foreach ($this->tenantTables as $table) {
                if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'school_id')) {
                    continue;
                }

                DB::table($table)->whereNull('school_id')->update(['school_id' => $school->id]);
            }

            TenantContext::forceSchool(null);
        });

        $this->info('Legacy tenant migration complete.');
        $this->line('School code: '.$code);
        $this->line('Super admin email: '.$this->option('super-email'));

        return self::SUCCESS;
    }

    protected function createSuperAdmin(): void
    {
        $email = $this->option('super-email') ?: env('SUPER_ADMIN_EMAIL', 'superadmin@easyschool.local');
        $password = $this->option('super-password') ?: env('SUPER_ADMIN_PASSWORD', 'SuperAdmin123!');
        $name = $this->option('super-name');

        SuperAdmin::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt($password),
                'status' => 'Active',
            ]
        );
    }
}
