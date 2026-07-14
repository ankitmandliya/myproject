<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Employee</th><th>Department</th><th>CL Current</th><th>SL Current</th><th>EL Current</th><th>Carry Forward EL</th><th>New CL</th><th>New SL</th><th>New EL</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? [] as $row)
                <tr>
                    <td>{{ $row['employee'] }}</td>
                    <td>{{ $row['department'] }}</td>
                    <td>{{ $row['cl_current'] }}</td>
                    <td>{{ $row['sl_current'] }}</td>
                    <td>{{ $row['el_current'] }}</td>
                    <td>{{ $row['carry_forward_el'] }}</td>
                    <td>{{ $row['new_cl'] }}</td>
                    <td>{{ $row['new_sl'] }}</td>
                    <td>{{ $row['new_el'] }}</td>
                    <td><span class="badge bg-{{ $row['status'] === 'Ready' ? 'success' : 'secondary' }}">{{ $row['status'] }}</span></td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center text-muted py-4">No Report Data Found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
