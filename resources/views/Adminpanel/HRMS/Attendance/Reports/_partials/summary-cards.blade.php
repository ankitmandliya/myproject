<div class="row">
@foreach([
 ['total_employees','Total Employees','primary'],['present','Present Today','success'],['absent','Absent Today','danger'],['late','Late Today','warning'],
 ['half_day','Half Day','info'],['leave','Leave Today','primary'],['holidays','Holidays This Month','secondary'],['weekly_offs','Weekly Offs This Month','dark']
] as [$key,$label,$color])
<div class="col-6 col-md-3"><div class="card card-stats card-round"><div class="card-body text-center"><p class="card-category">{{ $label }}</p><h4 class="card-title text-{{ $color }}">{{ $summary[$key] ?? 0 }}</h4></div></div></div>
@endforeach
</div>
