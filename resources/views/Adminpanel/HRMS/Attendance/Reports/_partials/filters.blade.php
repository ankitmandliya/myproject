<div class="card card-round"><div class="card-header"><div class="card-title">Report Filters</div></div><div class="card-body">
<form method="GET" action="{{ url()->current() }}"><div class="row">
@foreach(['name'=>'Employee','emp_code'=>'Employee Code','department'=>'Department','designation'=>'Designation'] as $key=>$label)
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="{{ $key }}">{{ $label }}</label><input class="form-control" id="{{ $key }}" name="{{ $key }}" value="{{ $filters[$key] ?? '' }}"></div></div>
@endforeach
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="status">Attendance Status</label><select class="form-control" id="status" name="status"><option value="">All Statuses</option>@foreach($statusList ?? ['Present','Late','Half Day','Leave','Holiday','Weekly Off','Absent'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '')===$status)>{{ $status }}</option>@endforeach</select></div></div>
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="from_date">From Date</label><input type="date" class="form-control" id="from_date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"></div></div>
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="to_date">To Date</label><input type="date" class="form-control" id="to_date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"></div></div>
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="month">Month</label><input type="month" class="form-control" id="month" name="month" value="{{ $filters['month'] ?? '' }}"></div></div>
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="year">Year</label><input type="number" class="form-control" id="year" name="year" value="{{ $filters['year'] ?? '' }}"></div></div>
<div class="col-sm-6 col-lg-3"><div class="form-group"><label for="per_page">Per Page</label><select class="form-control" id="per_page" name="per_page">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? request('per_page',10))===$size)>{{ $size }}</option>@endforeach</select></div></div>
<div class="col-12"><button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Apply Filters</button><a href="{{ url()->current() }}" class="btn btn-light ms-2">Reset</a></div>
</div></form></div></div>
