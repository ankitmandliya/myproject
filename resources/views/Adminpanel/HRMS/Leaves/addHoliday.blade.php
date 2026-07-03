@extends('Adminpanel.layout.mainlayout')
@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Add Holiday</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Holiday Form</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('holidays.store') }}">
                            @csrf

                            <div class="form-group">
                                <label for="holidayName">Holiday Name*</label>
                                <input type="text" id="holidayName" name="name" value="{{ old('name') }}"
                                    class="form-control" placeholder="Enter holiday name">

                                @error('name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                                <div class="form-group">
                                    <label for="fromDate">From Date*</label>
                                    <input type="date" id="fromDate" name="from_date" value="{{ old('from_date') }}"
                                        class="form-control">

                                    @error('from_date')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="toDate">To Date*</label>
                                    <input type="date" id="toDate" name="to_date" value="{{ old('to_date') }}"
                                        class="form-control">

                                    @error('to_date')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Status*</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Select</option>
                                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>

                                    @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection