@php($charts = $report['charts'] ?? [])
<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="card card-round h-100">
            <div class="card-header"><div class="card-title">Monthly Leave Trend</div></div>
            <div class="card-body"><canvas id="monthlyLeaveTrend" height="140"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-round h-100">
            <div class="card-header"><div class="card-title">Leave Type Distribution</div></div>
            <div class="card-body"><canvas id="leaveTypeDistribution" height="140"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-round h-100">
            <div class="card-header"><div class="card-title">Department Wise Leave</div></div>
            <div class="card-body"><canvas id="departmentWiseLeave" height="140"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card card-round h-100">
            <div class="card-header"><div class="card-title">Monthly Approval Trend</div></div>
            <div class="card-body"><canvas id="monthlyApprovalTrend" height="140"></canvas></div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const charts = @json($charts);
        const colors = ['#1572e8', '#31ce36', '#f25961', '#ffad46', '#6861ce', '#48abf7'];
        const makeChart = (id, config) => {
            const canvas = document.getElementById(id);
            if (canvas && window.Chart) {
                new Chart(canvas, config);
            }
        };

        makeChart('monthlyLeaveTrend', {
            type: 'line',
            data: { labels: charts.monthly_leave_trend?.labels || [], datasets: [{ label: 'Leave Days', data: charts.monthly_leave_trend?.data || [], borderColor: colors[0], backgroundColor: 'rgba(21,114,232,.12)', tension: .35, fill: true }] },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
        makeChart('leaveTypeDistribution', {
            type: 'doughnut',
            data: { labels: charts.leave_type_distribution?.labels || [], datasets: [{ data: charts.leave_type_distribution?.data || [], backgroundColor: colors }] },
            options: { responsive: true }
        });
        makeChart('departmentWiseLeave', {
            type: 'bar',
            data: { labels: charts.department_wise_leave?.labels || [], datasets: [{ label: 'Leave Days', data: charts.department_wise_leave?.data || [], backgroundColor: colors[3] }] },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });
        makeChart('monthlyApprovalTrend', {
            type: 'bar',
            data: { labels: charts.monthly_approval_trend?.labels || [], datasets: [{ label: 'Approved', data: charts.monthly_approval_trend?.approved || [], backgroundColor: colors[1] }, { label: 'Rejected', data: charts.monthly_approval_trend?.rejected || [], backgroundColor: colors[2] }] },
            options: { responsive: true }
        });
    });
</script>
