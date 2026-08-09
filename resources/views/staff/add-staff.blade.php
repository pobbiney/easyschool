<!-- page title -->
@php $pageName = "staff"; $subpageName = "add-staff"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div class="">
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STAFF MANAGEMENT</h1>
            <div class="">
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('list-staff') }}" class="text-secondary-light hover-text-primary hover-underline"> / Staff List</a>
                <span class="text-secondary-light"> / Add New Staff</span>
            </div>
        </div>
        <a href="{{ route('list-staff') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md">
                <i class="ri-list-check"></i>
            </span>
            View Staff List
        </a>
    </div>

    <form action="{{ route('add-staff-process') }}" enctype="multipart/form-data" method="POST" class="mt-24">
        @csrf
        <div class="row gy-3">
            <div class="col-xl-12">
                <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                        <h6 class="text-lg fw-semibold mb-0">Personal Info</h6>
                    </div>
                    <div class="card-body p-20">
                        <div class="row gy-3">
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Staff ID</label>
                                <input type="text" name="staff_number" class="form-control" value="{{ $staffCode }}" readonly>
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Title <span class="text-danger-600">*</span></label>
                                <select name="title" class="form-control form-select">
                                    <option value="" selected disabled>Select Title</option>
                                    <option value="Mr" {{ old('title') == 'Mr' ? 'selected' : '' }}>Mr</option>
                                    <option value="Miss" {{ old('title') == 'Miss' ? 'selected' : '' }}>Miss</option>
                                    <option value="Mrs" {{ old('title') == 'Mrs' ? 'selected' : '' }}>Mrs</option>
                                    <option value="Dr" {{ old('title') == 'Dr' ? 'selected' : '' }}>Dr</option>
                                    <option value="Sir" {{ old('title') == 'Sir' ? 'selected' : '' }}>Sir</option>
                                </select>
                                @error('title') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">First Name <span class="text-danger-600">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}" placeholder="Enter first name">
                                @error('firstname') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Other Name</label>
                                <input type="text" name="othername" class="form-control" value="{{ old('othername') }}" placeholder="Enter other name">
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Surname <span class="text-danger-600">*</span></label>
                                <input type="text" name="surname" class="form-control" value="{{ old('surname') }}" placeholder="Enter surname">
                                @error('surname') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Nationality <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="nationality">
                                    <option value="" selected disabled>Select Nationality</option>
                                    @foreach($listcountry as $country)
                                        <option value="{{ $country->id }}" {{ old('nationality') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="gender">
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date of Birth <span class="text-danger-600">*</span></label>
                                <input type="text" name="dob" class="form-control datepicker" placeholder="Enter date of birth" value="{{ old('dob') }}">
                                @error('dob') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Marital Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="marital_status">
                                    <option value="" selected disabled>Select Marital Status</option>
                                    <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                </select>
                                @error('marital_status') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Enter phone number">
                                @error('phone') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Email <span class="text-danger-600">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter email address">
                                @error('email') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-6 col-xl-8 col-sm-12">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Address <span class="text-danger-600">*</span></label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Enter residential address">
                                @error('address') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Staff Photo</label>
                                <input type="file" name="image" class="form-control" accept="image/*" id="imageUpload">
                            </div>
                        </div>
                    </div>

                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                        <h6 class="text-lg fw-semibold mb-0">Employee Details</h6>
                    </div>
                    <div class="card-body p-20">
                        <div class="row gy-3">
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Position <span class="text-danger-600">*</span></label>
                                <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="Enter position">
                                @error('position') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-xxl-3 col-xl-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="status">
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <small style="color:red;">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                    <a href="{{ route('list-staff') }}"
                        class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                        Cancel
                    </a>
                    <button type="reset"
                        class="border border-neutral-400 bg-hover-neutral-200 text-secondary-light text-md px-50 py-11 radius-8">
                        Reset
                    </button>
                    <button type="submit"
                        class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">
                        Save Staff
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection
