@php($rows = $report['rows'] ?? null)
@if($rows && $rows->count())
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    @foreach($report['columns'] ?? [] as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                    @if(($report['type'] ?? '') === 'employee')
                        <th class="text-end">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr class="{{ !empty($row['low_balance']) ? 'table-warning' : '' }}">
                        @foreach($report['columns'] ?? [] as $key => $label)
                            <td>
                                @if($key === 'photo')
                                    <img src="{{ $row[$key] ?? asset('assets/images/users/avatar-1.jpg') }}" alt="Employee" class="rounded-circle" width="36" height="36">
                                @else
                                    {{ $row[$key] ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                        @if(($report['type'] ?? '') === 'employee')
                            <td class="text-end">
                                <a href="{{ $row['details_url'] ?? '#' }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-print-none">
        {{ $rows->links() }}
    </div>
@else
    <div class="text-center text-muted py-5">No Report Data Found</div>
@endif
