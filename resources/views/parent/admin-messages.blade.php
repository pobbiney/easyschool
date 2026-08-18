@php $pageName = 'sms'; $subpageName = 'parent-messages'; @endphp
@extends('layouts.app')

@section('content')
<div class="card radius-12 border-0">
    <div class="card-body p-24">
        <h5 class="fw-bold mb-3">Parent Messages</h5>
        <p class="text-muted">Messages sent by parents through the parent portal.</p>

        @if(session('message_success'))
            <div class="alert alert-success">{{ session('message_success') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Parent</th>
                        <th>Student</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr class="{{ $message->status === 'new' ? 'table-warning' : '' }}">
                            <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                            <td>{{ $message->parentAccount?->guardian_name ?? $message->parentAccount?->phone }}</td>
                            <td>{{ $message->student?->full_name ?? '—' }}</td>
                            <td style="max-width:320px;">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($message->status) }}</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#msgModal{{ $message->id }}">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No parent messages yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $messages->links() }}
    </div>
</div>

@foreach($messages as $message)
<div class="modal fade" id="msgModal{{ $message->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message from parent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Parent:</strong> {{ $message->parentAccount?->guardian_name ?? $message->parentAccount?->phone }}</p>
                <p><strong>Student:</strong> {{ $message->student?->full_name ?? 'General' }}</p>
                <p class="mb-3">{{ $message->message }}</p>
                @if($message->admin_reply)
                    <div class="alert alert-light"><strong>Reply note:</strong> {{ $message->admin_reply }}</div>
                @endif
                <form method="POST" action="{{ route('parent-messages-read', $message) }}">
                    @csrf
                    <label class="form-label">Internal reply note (optional)</label>
                    <textarea name="admin_reply" class="form-control mb-3" rows="3">{{ old('admin_reply', $message->admin_reply) }}</textarea>
                    <button type="submit" class="btn btn-success">Mark read / save reply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
