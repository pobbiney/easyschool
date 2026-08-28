<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SuperAdminSubscriptionController extends Controller
{
    public function index()
    {
        return view('super-admin.subscriptions.index', [
            'subscriptions' => Subscription::query()->orderBy('name')->get(),
            'editing' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        Subscription::query()->create($validated);

        return redirect()
            ->route('super-admin.subscriptions')
            ->with('message_success', 'Subscription saved.');
    }

    public function edit(Subscription $subscription)
    {
        return view('super-admin.subscriptions.index', [
            'subscriptions' => Subscription::query()->orderBy('name')->get(),
            'editing' => $subscription,
        ]);
    }

    public function update(Request $request, Subscription $subscription)
    {
        $validated = $this->validated($request, $subscription);

        $subscription->update($validated);

        return redirect()
            ->route('super-admin.subscriptions')
            ->with('message_success', 'Subscription updated.');
    }

    /**
     * @return array{name: string, amount: string}
     */
    private function validated(Request $request, ?Subscription $subscription = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subscriptions', 'name')->ignore($subscription?->id),
            ],
            'amount' => 'required|numeric|min:0|decimal:0,2',
        ]);

        return [
            'name' => trim($validated['name']),
            'amount' => $validated['amount'],
        ];
    }
}
