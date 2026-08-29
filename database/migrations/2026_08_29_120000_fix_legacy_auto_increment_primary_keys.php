<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> table => primary key column */
    protected array $tables = [
        'staff' => 'id',
        'students' => 'id',
        'student_docs' => 'id',
        'academic_years' => 'id',
        'academic_terms' => 'id',
        'school_classes' => 'id',
        'class_categories' => 'id',
        'courses' => 'id',
        'grading_schemes' => 'id',
        'assessment_types' => 'id',
        'timetable_periods' => 'id',
        'houses' => 'id',
        'dormitories' => 'id',
        'billing_items' => 'id',
        'category_bill_setups' => 'id',
        'student_bills' => 'id',
        'bill_payments' => 'id',
        'bill_payment_transactions' => 'id',
        'student_bill_credit_transactions' => 'id',
        'hr_departments' => 'id',
        'hr_positions' => 'id',
        'hr_pay_grades' => 'id',
        'hr_earning_types' => 'id',
        'hr_deduction_types' => 'id',
        'hr_leave_types' => 'id',
        'hr_payroll_settings' => 'id',
        'hr_payroll_runs' => 'id',
        'pos_categories' => 'id',
        'pos_products' => 'id',
        'pos_sales' => 'id',
        'pos_sale_transactions' => 'id',
        'expense_categories' => 'id',
        'expenses' => 'id',
        'parent_accounts' => 'id',
        'parent_messages' => 'id',
        'parent_communication_logs' => 'id',
        'sms_messages' => 'id',
        'user_cat' => 'cat_id',
        'users' => 'id',
        'school_settings' => 'id',
        'academic_assessments' => 'id',
        'class_timetables' => 'id',
        'usr_user_logs' => 'id',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table => $column) {
            $this->ensureAutoIncrement($table, $column);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $definition = $this->columnDefinition($table, $column);

            if ($definition === null) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` %s NOT NULL',
                $table,
                $column,
                $definition
            ));
        }
    }

    private function ensureAutoIncrement(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $extra = DB::selectOne('SHOW COLUMNS FROM `'.$table.'` WHERE Field = ?', [$column]);

        if ($extra && str_contains((string) ($extra->Extra ?? ''), 'auto_increment')) {
            return;
        }

        $definition = $this->columnDefinition($table, $column);

        if ($definition === null) {
            return;
        }

        $maxId = (int) DB::table($table)->max($column);
        $next = max(1, $maxId + 1);

        DB::statement(sprintf(
            'ALTER TABLE `%s` MODIFY `%s` %s NOT NULL AUTO_INCREMENT',
            $table,
            $column,
            $definition
        ));
        DB::statement('ALTER TABLE `'.$table.'` AUTO_INCREMENT = '.$next);
    }

    private function columnDefinition(string $table, string $column): ?string
    {
        $info = DB::selectOne('SHOW COLUMNS FROM `'.$table.'` WHERE Field = ?', [$column]);

        if (! $info) {
            return null;
        }

        $type = strtoupper((string) ($info->Type ?? ''));

        if (str_contains($type, 'BIGINT')) {
            return 'BIGINT UNSIGNED';
        }

        if (str_contains($type, 'INT')) {
            return 'INT UNSIGNED';
        }

        return null;
    }
};
