<div class="row row-cols-2 row-cols-md-4 g-3 mb-3">
    @foreach([
        ['total_employees', 'Total Employees', 'primary'],
        ['present', 'Present Today', 'success'],
        ['absent', 'Absent Today', 'danger'],
        ['late', 'Late Today', 'warning'],
        ['half_day', 'Half Day', 'info'],
        ['leave', 'Leave Today', 'primary'],
        ['holidays', 'Holidays This Month', 'secondary'],
        ['weekly_offs', 'Weekly Offs This Month', 'dark'],
    ] as [$key, $label, $color])
        <div class="col">
            <div class="card card-stats card-round h-100 mb-0">
                <div class="card-body text-center py-3">
                    <p class="card-category mb-1">{{ $label }}</p>
                    <h4 class="card-title text-{{ $color }} mb-0">{{ $summary[$key] ?? 0 }}</h4>
                </div>
            </div>
        </div>
    @endforeach
</div>
