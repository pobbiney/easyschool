@extends('layouts.super-admin')

@section('title', 'Subscription')

@section('content')
<div class="sa-page-head">
  <h1>Subscription</h1>
  <p>Create and update subscription plans with a name and amount.</p>
</div>

<div class="sa-panel">
  <div class="sa-panel-head">
    <h2>
      <i class="{{ $editing ? 'ri-pencil-line' : 'ri-add-line' }}"></i>
      {{ $editing ? 'Edit subscription' : 'Add subscription' }}
    </h2>
  </div>
  <div class="sa-panel-body">
    <form method="POST" action="{{ $editing ? route('super-admin.subscriptions.update', $editing) : route('super-admin.subscriptions.store') }}">
      @csrf
      <div class="sa-form-grid">
        <div class="sa-field">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" class="sa-input" value="{{ old('name', $editing->name ?? '') }}" required placeholder="e.g. Basic, Standard, Premium">
          @error('name')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="sa-field">
          <label for="amount">Amount (GHS)</label>
          <input type="number" id="amount" name="amount" class="sa-input" value="{{ old('amount', $editing->amount ?? '') }}" required min="0" step="0.01" placeholder="0.00">
          @error('amount')<span class="sa-field-error">{{ $message }}</span>@enderror
        </div>
      </div>
      <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
        <button type="submit" class="sa-btn sa-btn-success">
          <i class="ri-save-line"></i>
          {{ $editing ? 'Update subscription' : 'Save subscription' }}
        </button>
        @if($editing)
          <a href="{{ route('super-admin.subscriptions') }}" class="sa-btn sa-btn-danger-outline">
            <i class="ri-close-line"></i> Cancel
          </a>
        @endif
      </div>
    </form>
  </div>
</div>

<div class="sa-panel" style="margin-top:24px;">
  <div class="sa-panel-head">
    <h2><i class="ri-list-check-2"></i> All subscriptions</h2>
    <span style="font-size:12px;color:#83807A;">{{ $subscriptions->count() }} plan(s)</span>
  </div>
  <div class="sa-table-wrap">
    <table class="sa-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Amount</th>
          <th>Created</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($subscriptions as $subscription)
          <tr>
            <td>
              <strong style="color:#F5F3EE;">{{ $subscription->name }}</strong>
            </td>
            <td>{{ \App\Support\Money::ghs($subscription->amount) }}</td>
            <td style="font-size:12.5px;color:#83807A;">
              {{ $subscription->created_at?->format('d M Y') }}
            </td>
            <td>
              <div class="sa-actions">
                <a href="{{ route('super-admin.subscriptions.edit', $subscription) }}" class="sa-btn sa-btn-primary">
                  <i class="ri-pencil-line"></i> Edit
                </a>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" style="text-align:center;padding:36px 16px;color:#83807A;">
              No subscriptions yet. Add a name and amount above to create the first plan.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
