<!-- page title -->
@php $pageName = "staff"; $subpageName = "profile"; @endphp
@php $activeTab = session('active_tab', '#pills-studentDetails'); @endphp
@php session()->forget('active_tab'); @endphp
@extends('layouts.app')
 
@section('content')

<div class="dashboard-main-body">

        <div class="page-header breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <div class="">
                <h1 class="fw-semibold mb-4 h6 text-primary-light">STAFF MANAGEMENT</h1>
                <div class="">
                <a href="#" class="text-secondary-light hover-text-primary hover-underline">Dashboard </a>
                <a href="#" class="text-secondary-light hover-text-primary hover-underline d-none"> /
                    Staff Management</a>
                <span class="text-secondary-light">/ View Staff Details</span>
                </div>
            </div>
            
        </div>

         <div class="mt-24">
            <div class="card h-100">
                <div class="card-body p-24">
                    <div class="d-flex gap-32 flex-md-row flex-column">
                        <div class="max-w-300-px w-100 text-center">

                               
                            <figure class="mb-24 w-120-px h-120-px mx-auto rounded-circle overflow-hidden">
                                @if(!empty($datas->picture))
                                 <img src="{{ $datas->picture }}" alt="Student Image" class="w-100 h-100 object-fit-cover">
                                 @elseif(strtolower($datas->gender) == 'male')
                                    <img src="{{ asset('assets/images/thumbs/edit-profile-img.png') }}"
                                        alt="Male Staff"
                                        class="flex-shrink-0 me-12 radius-8"  >
                                @else
                                    <img src="{{ asset('assets/images/thumbs/studnt-edit-profile-img.png') }}"
                                        alt="Female Staff"
                                        class="flex-shrink-0 me-12 radius-8"  >
                                @endif
                            </figure>
                            <h2 class="h6 text-primary-light mb-16 fw-semibold">{{ $datas->surname. ' '.$datas->firstname }}</h2>
                            <p class="mb-0">Staff No: <span class="text-primary-600 fw-semibold">{{ $datas->employee_id }}</span>
                            </p>
                             
                            
                        </div>
                        <div class="">
                            <span class="h-100 w-1-px bg-neutral-200"></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="pb-16 border-bottom d-flex align-items-center justify-content-between gap-20">
                                <h3 class="h6 text-primary-light text-lg mb-0 fw-semibold">Personal Info</h3>
                               
                                @if ($datas->status == "Active")
                                    <span class="bg-success-100 text-success-600 px-16 py-4 radius-4 fw-medium text-sm">{{ $datas->status }}</span>
                                    @elseif ($datas->status == "Inactive")
                                    <span class="bg-success-100 text-danger-600 px-16 py-4 radius-4 fw-medium text-sm">{{ $datas->status }}</span>
                                @endif
                                    
                            </div>
                            <div class="mt-16 d-flex flex-column gap-8">
                                 <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Gender</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->gender }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Email</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->email }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Mobile No</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->mobile }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Marital Status</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->marital_status }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Position</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->position }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Nationality</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->country->name }}</span>
                                </div>
                                <div class="d-flex gap-4">
                                    <span class="fw-semibold text-sm text-primary-light w-110-px">Residential Address</span>
                                    <span class="fw-normal text-sm text-secondary-light">: {{ $datas->residential_address }}</span>
                                </div>
                                
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="my-16">
                <ul class="nav nav-pills bordered-tab mb-3" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link d-flex align-items-center gap-8 text-secondary-light fw-medium text-sm text-hover-primary-600 text-capitalize bg-transparent px-20 py-12  active"
                            id="pills-studentDetails-tab" data-bs-toggle="pill" data-bs-target="#pills-studentDetails"
                            type="button" role="tab" aria-controls="pills-studentDetails" aria-selected="true">
                            <span class="d-flex tab-icon line-height-1 text-md">
                                <i class="ri-camera-line"></i>
                            </span>
                             Change Photo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link d-flex align-items-center gap-8 text-secondary-light fw-medium text-sm text-hover-primary-600 text-capitalize bg-transparent px-20 py-12 "
                            id="pills-attendance-tab" data-bs-toggle="pill" data-bs-target="#pills-attendance"
                            type="button" role="tab" aria-controls="pills-attendance" aria-selected="false">
                            <span class="d-flex tab-icon line-height-1 text-md">
                                   <i class="ri-lock-line"></i>
                            </span>
                           Change Password
                        </button>
                    </li>  
                </ul>


                <div class="tab-content" id="pills-tabContent">

                    <!-- Student Details tab start -->
                    <div class="tab-pane fade show active" id="pills-studentDetails" role="tabpanel"
                        aria-labelledby="pills-studentDetails-tab" tabindex="0">
                        <div class="row gy-4">
                            <div class="col-3"></div>
                            <div class="col-6">
                                <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
                                    <div
                                        class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between" >
                                        <h6 class="text-lg fw-semibold mb-0">Change Photo</h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="row" style="margin-left: 20px;margin-right:20px">
                                            
                                            <form enctype="multipart/form-data" method="POST" action="{{ route('update-photo-process')}}">
                                                @csrf
                                            
                                                <div class="avatar-upload mt-16" style="margin-bottom: 30px; width: 200px; margin-left: auto; margin-right: auto;">
                                                    <div class="avatar-preview style-two" style="height: 200px;width:200px">
                                                        <div id="previewImage1" style="height: 200px;width:200px"></div>
                                                    </div>
                                                </div><br/>
                                                <label for="imageUpload"
                                                    class="form-label fw-semibold text-secondary-light text-md mb-8">Upload Photo  </label>
                                                <input type="file" class="form-control radius-8" name="image" id="imageUpload">
                                                 @error('image') <small style="color:red;">{{$message}}</small>@enderror
                                                <br/>
                                               
                                                <button type="submit"  class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8" style="margin-bottom: 20px;"> Update</button>
                                            
                                                 <input type="hidden" name="staff_id" value="{{ $datas->id }}"/>
                                                 <br/>
                                                 <input type="hidden" name="active_tab" class="active_tab" value="{{ $activeTab }}">
                                            </form>
                                            </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="col-6"></div>
                           
                        </div>
                    </div>
                    <!-- Student Details tab end -->

                    <!-- Attendance tab start -->
                    <div class="tab-pane fade" id="pills-attendance" role="tabpanel"
                        aria-labelledby="pills-attendance-tab" tabindex="0">
                         <div class="row gy-4">
                             
                            <form enctype="multipart/form-data" method="POST" action="update-password-process">
                                 @csrf
                                
                                <div class="col-6" style="margin-left: auto; margin-right: auto;">
                                    <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
                                        <div
                                            class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                                            <h6 class="text-lg fw-semibold mb-0">Change Password</h6>
                                        </div>
                                        <div class="card-body p-0" style="margin-left: 20px;margin-right:20px">
                                            <div class="" style="margin-bottom: 15px;">
                                                <label for="guardianName"
                                                    class="form-label fw-semibold text-secondary-light text-md mb-8"> 
                                                    Current Password
                                                </label>
                                                 <input type="password" name="current_password" class="form-control"   placeholder="Current Password">
                                      
                                                @error('current_password') <small style="color:red"> {{ $message}}</small> @enderror
                                            </div>
                                            <div class="" style="margin-bottom: 15px;">
                                                <label for="guardianName"
                                                     class="form-label fw-semibold text-secondary-light text-md mb-8"> 
                                                    Current Password
                                                </label>
                                                 <input type="password" name="new_password" class="form-control"   placeholder="Current Password">
                                      
                                                 @error('new_password') <small style="color:red"> {{ $message}}</small> @enderror
                                            </div>
                                            <div class="" style="margin-bottom: 15px;">
                                                <label for="guardianName"
                                                     class="form-label fw-semibold text-secondary-light text-md mb-8""> 
                                                    Current Password
                                                </label>
                                                 <input type="password" name="confirm_password" class="form-control"   placeholder="Current Password">
                                      
                                                @error('confirm_password') <small style="color:red"> {{ $message}}</small> @enderror
                                            </div>
                                            <button type="submit"  class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8" style="margin-bottom: 20px;"> Change Password</button>
                                            
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="active_tab" class="active_tab" value="{{ $activeTab }}">
                                <input type="hidden" name="staff_id" value="{{ $datas->id }}"/>
                            </form>
                           
                        </div>
                    </div>
                    <!-- Attendance tab end -->
 
                </div>
            </div>
        </div>
            
          
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    let activeTab = "{{ $activeTab }}";

    if (activeTab) {
        let trigger = document.querySelector(
            'button[data-bs-toggle="tab"][data-bs-target="' + activeTab + '"]'
        );
        if (trigger) {
            new bootstrap.Tab(trigger).show();
        }
    }

    document.querySelectorAll('.active_tab').forEach(input => {
        input.value = activeTab;
    });

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (e) {
            document.querySelectorAll('.active_tab').forEach(input => {
                input.value = e.target.getAttribute('data-bs-target');
            });
        });
    });
});
</script>
 <script>
    // ================== Image Upload Js Start ===========================
    function readURL(input, previewElementId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#' + previewElementId).css('background-image', 'url(' + e.target.result + ')');
                $('#' + previewElementId).hide();
                $('#' + previewElementId).fadeIn(650);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#imageUpload").change(function () {
        readURL(this, 'previewImage1');
    });

    $("#imageUploadTwo").change(function () {
        readURL(this, 'previewImage2');
    });
    // ================== Image Upload Js End ===========================
</script>

@endsection