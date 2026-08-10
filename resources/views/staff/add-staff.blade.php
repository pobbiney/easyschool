@php $pageName = "staff"; $subpageName = "add-staff"; @endphp

@extends('layouts.app')

@section('css')
<style>
    .staff-form-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
    }

    .staff-sidebar-card {
        border: 1px solid var(--neutral-200, #e5e7eb);
        position: sticky;
        top: 96px;
    }

    .staff-photo-box {
        width: 100%;
        max-width: 220px;
        aspect-ratio: 1;
        margin: 0 auto;
        border: 2px dashed var(--neutral-300, #d1d5db);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: var(--neutral-50, #f9fafb);
        transition: border-color 0.2s ease;
    }

    .staff-photo-box:hover {
        border-color: var(--primary-600, #25A194);
    }

    .staff-photo-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .staff-photo-placeholder {
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--neutral-500, #6b7280);
        padding: 16px;
        text-align: center;
    }

    .staff-photo-placeholder i {
        font-size: 36px;
        color: var(--primary-600, #25A194);
    }

    .staff-id-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 999px;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        font-weight: 600;
        font-size: 14px;
    }

    .section-title-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 161, 148, 0.1);
        color: var(--primary-600, #25A194);
        font-size: 18px;
    }

    .section-card-title {
        color: var(--primary-600, #25A194);
    }

    .screen-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        background: rgba(37, 161, 148, 0.08);
        color: var(--primary-600, #25A194);
        margin: 0 6px 6px 0;
    }

    .inherited-screens-box {
        min-height: 56px;
        background: var(--neutral-50, #f9fafb);
    }

    .system-access-fields.is-disabled {
        opacity: 0.55;
        pointer-events: none;
    }

    .staff-tip-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .staff-tip-list li {
        display: flex;
        gap: 10px;
        font-size: 13px;
        color: var(--neutral-600, #4b5563);
        margin-bottom: 10px;
    }

    .staff-tip-list li i {
        color: var(--primary-600, #25A194);
        margin-top: 2px;
    }

    .staff-form-actions {
        border: 1px solid var(--neutral-200, #e5e7eb);
        border-radius: 12px;
        background: var(--white, #fff);
        padding: 16px 20px;
    }
</style>
@endsection

@section('content')

<div class="dashboard-main-body">

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STAFF MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <a href="{{ route('list-staff') }}" class="text-secondary-light hover-text-primary hover-underline"> / Staff List</a>
                <span class="text-secondary-light"> / Add New Staff</span>
            </div>
        </div>
        <a href="{{ route('list-staff') }}" class="btn btn-outline-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-list-check"></i></span>
            View Staff List
        </a>
    </div>

    <form action="{{ route('add-staff-process') }}" enctype="multipart/form-data" method="POST">
        @csrf
        <input type="hidden" name="staff_number" value="{{ $staffCode }}">

        <div class="row gy-4">
            <div class="col-xl-4">
                <div class="staff-sidebar-card shadow-1 radius-12 bg-base p-24">
                    <label for="imageUpload" class="staff-photo-box d-block mb-20">
                        <div class="staff-photo-placeholder" id="photoPlaceholder">
                            <i class="ri-camera-line"></i>
                            <span class="text-sm fw-medium">Upload staff photo</span>
                            <span class="text-xs text-secondary-light">Click to choose image</span>
                        </div>
                        <img id="photoPreview" src="" alt="Staff photo preview" class="d-none">
                    </label>
                    <input type="file" name="image" accept="image/*" id="imageUpload" class="d-none">

                    <div class="text-center mb-20">
                        <span class="staff-id-badge">
                            <i class="ri-id-card-line"></i>
                            {{ $staffCode }}
                        </span>
                    </div>

                    <div class="border-top pt-20">
                        <h6 class="text-sm fw-semibold mb-12">Quick tips</h6>
                        <ul class="staff-tip-list">
                            <li><i class="ri-checkbox-circle-line"></i><span>Fill in all required fields marked with *.</span></li>
                            <li><i class="ri-checkbox-circle-line"></i><span>System login is optional — enable it only when needed.</span></li>
                            <li><i class="ri-checkbox-circle-line"></i><span>User category controls which screens they can access.</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden mb-24">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
                        <span class="section-title-icon"><i class="ri-user-3-line"></i></span>
                        <div>
                            <h6 class="text-lg fw-semibold mb-0 section-card-title">Personal Information</h6>
                            <p class="text-sm text-secondary-light mb-0">Basic identity and contact details.</p>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <div class="row gy-3">
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Title <span class="text-danger-600">*</span></label>
                                <select name="title" class="form-control form-select">
                                    <option value="" selected disabled>Select title</option>
                                    @foreach(['Mr', 'Miss', 'Mrs', 'Dr', 'Sir'] as $title)
                                        <option value="{{ $title }}" {{ old('title') == $title ? 'selected' : '' }}>{{ $title }}</option>
                                    @endforeach
                                </select>
                                @error('title') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">First Name <span class="text-danger-600">*</span></label>
                                <input type="text" name="firstname" class="form-control" value="{{ old('firstname') }}" placeholder="First name">
                                @error('firstname') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Other Name</label>
                                <input type="text" name="othername" class="form-control" value="{{ old('othername') }}" placeholder="Other name">
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Surname <span class="text-danger-600">*</span></label>
                                <input type="text" name="surname" class="form-control" value="{{ old('surname') }}" placeholder="Surname">
                                @error('surname') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="gender">
                                    <option value="" selected disabled>Select gender</option>
                                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                @error('gender') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date of Birth <span class="text-danger-600">*</span></label>
                                <input type="text" name="dob" class="form-control datepicker" placeholder="Select date" value="{{ old('dob') }}">
                                @error('dob') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Nationality <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="nationality">
                                    <option value="" selected disabled>Select nationality</option>
                                    @foreach($listcountry as $country)
                                        <option value="{{ $country->id }}" {{ old('nationality') == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('nationality') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Marital Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="marital_status">
                                    <option value="" selected disabled>Select status</option>
                                    @foreach(['Married', 'Single', 'Divorced'] as $status)
                                        <option value="{{ $status }}" {{ old('marital_status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('marital_status') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone <span class="text-danger-600">*</span></label>
                                <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="Phone number">
                                @error('phone') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-8 col-sm-12">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Email <span class="text-danger-600">*</span></label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Email address">
                                @error('email') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Residential Address <span class="text-danger-600">*</span></label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Residential address">
                                @error('address') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="staff-form-card shadow-1 radius-12 bg-base overflow-hidden">
                    <div class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center gap-12">
                        <span class="section-title-icon"><i class="ri-briefcase-4-line"></i></span>
                        <div>
                            <h6 class="text-lg fw-semibold mb-0 section-card-title">Employment Details</h6>
                            <p class="text-sm text-secondary-light mb-0">Role and employment status.</p>
                        </div>
                    </div>
                    <div class="card-body p-24">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Position <span class="text-danger-600">*</span></label>
                                <input type="text" name="position" class="form-control" value="{{ old('position') }}" placeholder="e.g. Mathematics Teacher">
                                @error('position') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status <span class="text-danger-600">*</span></label>
                                <select class="form-control form-select" name="status">
                                    <option value="" selected disabled>Select status</option>
                                    <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status') <small class="text-danger-600">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @include('staff.partials._section-system-access')
            </div>

            <div class="col-12">
                <div class="staff-form-actions d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="{{ route('list-staff') }}" class="text-secondary-light hover-text-danger-600">
                        Cancel and go back
                    </a>
                    <div class="d-flex flex-wrap gap-3">
                        <button type="reset" class="btn btn-outline-neutral-400 text-secondary-light">
                            Reset form
                        </button>
                        <button type="submit" class="btn btn-primary-600 d-flex align-items-center gap-6">
                            <i class="ri-save-line"></i>
                            Save Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
@include('staff.partials._extra-screens-scripts')
<script>
    (function () {
        const imageInput = document.getElementById('imageUpload');
        const photoPreview = document.getElementById('photoPreview');
        const photoPlaceholder = document.getElementById('photoPlaceholder');

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (event) {
                    photoPreview.src = event.target.result;
                    photoPreview.classList.remove('d-none');
                    photoPlaceholder.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            });
        }

        initStaffExtraScreens({
            accessToggle: document.getElementById('enable_system_access'),
            accessFields: document.getElementById('system-access-fields'),
            categorySelect: document.getElementById('staff_user_cat'),
            previewBox: document.getElementById('inherited-screens-preview'),
            categoryLinksUrl: @json(url('get-user-category-id')),
        });
    })();
</script>
@endsection
