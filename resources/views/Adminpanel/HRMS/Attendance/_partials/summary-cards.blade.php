<div class="row">
    @foreach([
        ['key' => 'total_employees', 'label' => 'Total Employees', 'icon' => 'fa-users', 'color' => 'primary'],
        ['key' => 'present', 'label' => 'Present Today', 'icon' => 'fa-user-check', 'color' => 'success'],
        ['key' => 'absent', 'label' => 'Absent Today', 'icon' => 'fa-user-times', 'color' => 'danger'],
        ['key' => 'late', 'label' => 'Late Today', 'icon' => 'fa-clock', 'color' => 'warning'],
        ['key' => 'half_day', 'label' => 'Half Day', 'icon' => 'fa-adjust', 'color' => 'info'],
        ['key' => 'leave', 'label' => 'Leave Today', 'icon' => 'fa-calendar-minus', 'color' => 'secondary'],
    ] as $card)
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-{{ $card['color'] }} bubble-shadow-small">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">{{ $card['label'] }}</p>
                                <h4 class="card-title">{{ $summary[$card['key']] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
