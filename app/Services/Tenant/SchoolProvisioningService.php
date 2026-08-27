<?php

namespace App\Services\Tenant;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolSetting;
use App\Models\SuperAdmin;
use App\Models\User;
use App\Models\UserAccessLink;
use App\Models\UserCat;
use App\Models\UserCatLink;
use App\Support\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SchoolProvisioningService
{
    public function __construct(
        protected SchoolCodeGenerator $codeGenerator,
        protected SchoolActivityLogger $activityLogger,
    ) {}

    public function approve(School $school, SuperAdmin $approver): School
    {
        if (! $school->isPending()) {
            throw new \InvalidArgumentException('Only pending schools can be approved.');
        }

        return DB::transaction(function () use ($school, $approver) {
            $code = $this->codeGenerator->generate();

            $school->update([
                'code' => $code,
                'status' => School::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => $approver->id,
            ]);

            TenantContext::forceSchool($school->id, $code);

            $settings = SchoolSetting::query()->create([
                'school_id' => $school->id,
                'name' => $school->name,
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email,
                'website' => $school->website,
            ]);

            [$year, $term] = $this->seedAcademicDefaults($school, $settings);
            $adminCategoryId = $this->seedUserCategories($school);

            $adminUser = User::query()->create([
                'school_id' => $school->id,
                'name' => $school->admin_name,
                'email' => $school->admin_email,
                'phone' => $school->admin_phone,
                'password' => (string) $school->getAttributes()['admin_password'],
                'user_cat' => $adminCategoryId,
                'cat_id' => $adminCategoryId,
                'status' => 'Active',
            ]);

            $this->grantAdminAccessLinks($adminUser, $adminCategoryId);

            TenantContext::forceSchool(null);

            $this->activityLogger->log(
                action: 'school.approved',
                description: "School approved with code {$code}",
                payload: [
                    'school_id' => $school->id,
                    'admin_user_id' => $adminUser->id,
                ],
                schoolId: $school->id,
                schoolCode: $code,
                actorType: 'super_admin',
                actorId: $approver->id,
            );

            return $school->fresh(['settings']);
        });
    }

    public function reject(School $school, SuperAdmin $approver, string $reason): School
    {
        $school->update([
            'status' => School::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'approved_by' => $approver->id,
        ]);

        $this->activityLogger->log(
            action: 'school.rejected',
            description: $reason,
            payload: ['school_id' => $school->id],
            schoolId: $school->id,
            schoolCode: $school->code,
            actorType: 'super_admin',
            actorId: $approver->id,
        );

        return $school->fresh();
    }

    protected function seedAcademicDefaults(School $school, SchoolSetting $settings): array
    {
        $yearLabel = now()->format('Y').'/'.now()->addYear()->format('Y');

        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => $yearLabel,
            'status' => 'Active',
            'created_by' => 0,
        ]);

        $term = AcademicTerm::query()->create([
            'school_id' => $school->id,
            'name' => 'First Term',
            'status' => 'Active',
            'sort_order' => 1,
            'created_by' => 0,
        ]);

        $settings->update([
            'default_academic_year_id' => $year->id,
            'default_academic_term_id' => $term->id,
        ]);

        return [$year, $term];
    }

    protected function seedUserCategories(School $school): int
    {
        $templateSchoolId = School::query()
            ->where('status', School::STATUS_APPROVED)
            ->where('id', '!=', $school->id)
            ->orderBy('id')
            ->value('id');

        if ($templateSchoolId) {
            return $this->copyCategoriesFromTemplate($school, (int) $templateSchoolId);
        }

        $admin = UserCat::query()->create([
            'school_id' => $school->id,
            'cat_name' => 'Admin',
            'status' => 'Active',
        ]);

        UserCat::query()->create([
            'school_id' => $school->id,
            'cat_name' => 'Teacher',
            'status' => 'Active',
        ]);

        return (int) $admin->cat_id;
    }

    protected function copyCategoriesFromTemplate(School $school, int $templateSchoolId): int
    {
        $adminCategoryId = null;
        $map = [];

        $categories = UserCat::query()
            ->withoutGlobalScopes()
            ->where('school_id', $templateSchoolId)
            ->get();

        foreach ($categories as $category) {
            $copy = UserCat::query()->create([
                'school_id' => $school->id,
                'cat_name' => $category->cat_name,
                'status' => $category->status,
            ]);

            $map[(int) $category->cat_id] = (int) $copy->cat_id;

            if (strcasecmp($category->cat_name, 'Admin') === 0 || strcasecmp($category->cat_name, 'Administrator') === 0) {
                $adminCategoryId = (int) $copy->cat_id;
            }
        }

        $links = UserCatLink::query()
            ->whereIn('cat_id', array_keys($map))
            ->get();

        foreach ($links as $link) {
            UserCatLink::query()->create([
                'cat_id' => $map[(int) $link->cat_id],
                'link_id' => $link->link_id,
            ]);
        }

        return $adminCategoryId ?? (int) reset($map);
    }

    protected function grantAdminAccessLinks(User $adminUser, int $adminCategoryId): void
    {
        $linkIds = UserCatLink::query()
            ->where('cat_id', $adminCategoryId)
            ->pluck('link_id');

        foreach ($linkIds as $linkId) {
            UserAccessLink::query()->firstOrCreate([
                'user_id' => $adminUser->id,
                'link_id' => $linkId,
            ]);
        }
    }
}
