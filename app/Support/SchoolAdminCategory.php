<?php

namespace App\Support;

use App\Models\School;
use App\Models\User;
use App\Models\UserCat;

class SchoolAdminCategory
{
    public static function userIsAdmin(User $user, School $school): bool
    {
        if (strcasecmp(trim((string) $school->admin_email), trim((string) $user->email)) === 0) {
            return true;
        }

        $category = $user->category;

        if (! $category) {
            $category = UserCat::query()
                ->withoutGlobalScopes()
                ->find($user->user_cat);
        }

        $name = trim((string) ($category->cat_name ?? ''));

        return strcasecmp($name, 'Admin') === 0 || strcasecmp($name, 'Administrator') === 0;
    }
}
