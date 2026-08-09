<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\UserCat;
use App\Models\UserCatLink;
use App\Models\UserLink;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function getUserCategoriesView()
    {
        $categories = UserCat::orderBy('cat_name')->get();

        foreach ($categories as $category) {
            $category->assigned_links = UserCatLink::where('cat_id', $category->cat_id)
                ->join('user_links', 'user_cat_links.link_id', '=', 'user_links.link_id')
                ->where('user_links.link_url', '!=', '#')
                ->pluck('user_links.link_name')
                ->toArray();
        }

        $parentLinks = UserLink::where('link_parent', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        $childLinks = UserLink::where('link_parent', '>', 0)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get();

        return view('user-management.user-categories', compact('categories', 'parentLinks', 'childLinks'));
    }

    public function addUserCategory(Request $request)
    {
        $request->validate([
            'cat_name' => 'required|string|max:100',
            'status' => 'required',
            'link_ids' => 'nullable|array',
        ]);

        if (UserCat::where('cat_name', trim($request->cat_name))->count() > 0) {
            return back()->with('message_error', 'This category name already exists.');
        }

        $category = new UserCat();
        $category->cat_name = trim($request->cat_name);
        $category->status = trim($request->status);
        $category->save();

        $this->saveCategoryLinks($category->cat_id, $request->link_ids ?? []);

        return back()->with('message_success', 'User category added successfully.');
    }

    public function getUserCategoryId($id)
    {
        $category = UserCat::findOrFail($id);

        $assignedLinkIds = UserCatLink::where('cat_id', $id)
            ->pluck('link_id')
            ->toArray();

        return response()->json([
            'cat_id' => $category->cat_id,
            'cat_name' => $category->cat_name,
            'status' => $category->status,
            'link_ids' => $assignedLinkIds,
        ]);
    }

    public function updateUserCategory(Request $request)
    {
        $request->validate([
            'cat_id' => 'required',
            'cat_name' => 'required|string|max:100',
            'status' => 'required',
            'link_ids' => 'nullable|array',
        ]);

        $category = UserCat::findOrFail($request->cat_id);

        $nameExists = UserCat::where('cat_name', trim($request->cat_name))
            ->where('cat_id', '!=', $request->cat_id)
            ->count();

        if ($nameExists > 0) {
            return back()->with('message_error', 'This category name already exists.');
        }

        $category->cat_name = trim($request->cat_name);
        $category->status = trim($request->status);
        $category->save();

        $this->saveCategoryLinks($category->cat_id, $request->link_ids ?? []);

        return back()->with('message_success', 'User category updated successfully.');
    }

    public function deleteUserCategory(Request $request)
    {
        $request->validate([
            'cat_id' => 'required',
        ]);

        $usersCount = User::where('user_cat', $request->cat_id)->count();

        if ($usersCount > 0) {
            return back()->with('message_error', 'Cannot delete. This category is assigned to ' . $usersCount . ' user(s).');
        }

        UserCatLink::where('cat_id', $request->cat_id)->delete();
        UserCat::where('cat_id', $request->cat_id)->delete();

        return back()->with('message_success', 'User category deleted successfully.');
    }

    private function saveCategoryLinks($catId, $selectedLinkIds)
    {
        UserCatLink::where('cat_id', $catId)->delete();

        $linkIdsToSave = [];

        foreach ($selectedLinkIds as $linkId) {
            $linkIdsToSave[] = (int) $linkId;

            $link = UserLink::find($linkId);

            if ($link && $link->link_parent > 0) {
                $linkIdsToSave[] = $link->link_parent;
            }
        }

        $linkIdsToSave = array_unique($linkIdsToSave);

        foreach ($linkIdsToSave as $linkId) {
            UserCatLink::create([
                'cat_id' => $catId,
                'link_id' => $linkId,
            ]);
        }
    }
}
