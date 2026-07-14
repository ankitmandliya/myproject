<div class="border rounded p-3 h-100">
    <div class="fw-bold mb-3">Approval Timeline</div>
    <div class="d-flex flex-column gap-3">
        @forelse (($leave->timeline_items ?? []) as $item)
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <span class="badge badge-{{ $item['badge'] ?? 'secondary' }} rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fas {{ ($item['badge'] ?? '') === 'danger' ? 'fa-times' : ((($item['badge'] ?? '') === 'secondary') ? 'fa-ban' : 'fa-check') }}" aria-hidden="true"></i>
                    </span>
                </div>
                <div class="flex-fill pb-3 {{ ! $loop->last ? 'border-bottom' : '' }}">
                    <div class="fw-bold">{{ $item['action'] ?? 'Workflow Update' }}</div>
                    <div class="text-muted small">{{ $item['date'] ?? '-' }} {{ $item['time'] ?? '' }} @if(! empty($item['user'])) by {{ $item['user'] }} @endif</div>
                    <div class="small text-muted">{{ $item['role'] ?? '-' }}</div>
                    @if(! empty($item['remarks']) && $item['remarks'] !== '-')
                        <div class="small mt-1 text-break">{{ $item['remarks'] }}</div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-muted small">No timeline entries available.</div>
        @endforelse
    </div>
</div>