@php
    $summary = $summary ?? [];
    $cards = [
        ['label' => 'Total Leave Balance', 'value' => $summary['total_balance'] ?? $summary['total_leave_balance'] ?? 0, 'icon' => 'fa-clipboard-list', 'class' => 'primary'],
        ['label' => 'Used Leave', 'value' => $summary['used_leave'] ?? $summary['total_leave_days'] ?? 0, 'icon' => 'fa-calendar-minus', 'class' => 'info'],
        ['label' => 'Remaining Leave', 'value' => $summary['remaining_leave'] ?? 0, 'icon' => 'fa-calendar-check', 'class' => 'success'],
        ['label' => 'Pending Requests', 'value' => $summary['pending'] ?? 0, 'icon' => 'fa-hourglass-half', 'class' => 'warning'],
        ['label' => 'Approved Leaves', 'value' => $summary['approved'] ?? 0, 'icon' => 'fa-check-circle', 'class' => 'success'],
        ['label' => 'Rejected Leaves', 'value' => $summary['rejected'] ?? 0, 'icon' => 'fa-times-circle', 'class' => 'danger'],
    ];
@endphp

<div class="row">
    @foreach ($cards as $card)
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-{{ $card['class'] }} bubble-shadow-small">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">{{ $card['label'] }}</p>
                                <h4 class="card-title">{{ $card['value'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
