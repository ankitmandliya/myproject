<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead><tr><th>Financial Year</th><th>Closed By</th><th>Closed On</th><th>Processed</th><th>Carry Forward</th><th>Status</th><th class="text-end">Action</th></tr></thead>
        <tbody>
            @forelse($history as $item)
                <tr>
                    <td>{{ $item->financial_year }}</td>
                    <td>{{ $item->closedBy?->name ?? '-' }}</td>
                    <td>{{ $item->closed_at?->format('d M Y h:i A') ?? '-' }}</td>
                    <td>{{ $item->employees_processed }}</td>
                    <td>{{ $item->carry_forward_count }}</td>
                    <td><span class="badge bg-{{ $item->status === 'closed' ? 'success' : 'warning' }}">{{ ucfirst($item->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('hrms.financial-year.show', $item->id) }}" class="btn btn-sm btn-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No Report Data Found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-print-none">{{ $history->links() }}</div>
