@extends('Adminpanel.layout.mainlayout')
@section('content')
<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Add Leave Type</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Leave Type Form</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('leave-types.store') }}">
                            @csrf

                            <div class="form-group">
                                <label for="leaveName">Leave Name*</label>
                                <input type="text" id="leaveName" name="leave_name" value="{{ old('leave_name') }}"
                                    class="form-control" placeholder="Enter leave name">

                                @error('leave_name')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="leaveCode">Leave Code*</label>
                                <input type="text" id="leaveCode" name="leave_code" value="{{ old('leave_code') }}"
                                    class="form-control" placeholder="Enter leave code">

                                @error('leave_code')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="totalDays">Total Days*</label>
                                <input type="number" id="totalDays" name="total_days" value="{{ old('total_days') }}"
                                    class="form-control" placeholder="Enter total days">

                                @error('total_days')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="isPaid">Is Paid?</label>
                                <select class="form-control" id="isPaid" name="is_paid">
                                    <option value="">Select</option>
                                    <option value="1" {{ old('is_paid') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>No</option>
                                </select>

                                @error('is_paid')
                                <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection