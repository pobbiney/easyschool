<!-- page title -->
@php $pageName = "staff"; $subpageName = "add-staff"; @endphp

@extends('layouts.app')
<style>
.upload-container{
    display:flex;
    justify-content:center;
    margin-top:20px;
}

.upload-box{
    width:250px;
    height:250px;
    border:2px dashed #ccc;
    border-radius:10px;
    cursor:pointer;
    text-align:center;
    overflow:hidden;
    position:relative;
}

.upload-box img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.upload-text{
    position:absolute;
    bottom:5px;
    width:100%;
    font-size:12px;
    background:rgba(0,0,0,0.5);
    color:white;
}
</style>
@section('content')

<div class="dashboard-main-body">

        <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <div class="">
                <h1 class="fw-semibold mb-4 h6 text-primary-light">STAFF MANAGEMENT</h1>
                <div class="">
                <a href="#" class="text-secondary-light hover-text-primary hover-underline">Dashboard </a>
                <a href="#" class="text-secondary-light hover-text-primary hover-underline d-none"> /
                    Staff Management</a>
                <span class="text-secondary-light">/ Update Staff</span>
                </div>
            </div>
            
        </div>
            
         <form action="{{ route('update-staff-process',$id)}}" enctype="multipart/form-data" method="POST" class="mt-24">
            @csrf
            <div class="row gy-3">
                
                <div class="col-xl-12">
                    <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
                        <div
                            class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                            <h6 class="text-lg fw-semibold mb-0">Personal Info</h6>
                        </div>
                        <div class="row">
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                 <div class="upload-container">

                                        <label for="imageUpload" class="upload-box">
                                            @if(!empty($datas->picture))
                                                <img id="preview" src="{{ $datas->picture }}" alt="Profile Preview">
                                            
                                            @else
                                            <img id="preview"src="{{ asset('assets/images/thumbs/teacher-details-img.png')}}" alt="Profile Preview">
                                            @endif
                                            <div class="upload-text">Click to upload photo</div>
                                        </label>

                                        <input type="file" id="imageUpload" name="image" accept="image/*" hidden>

                                    </div>
                                    
                            </div>
                            <div class="col-md-4"></div>
                        </div>
                        <div class="card-body p-20">
                            <div class="row gy-3">
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="guardianType"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Title
                                        </label>
                                        <select name="title" class="form-control form-select">
                                            <option value="{{ $datas->title  }}"    >{{ $datas->title  }}</option>
                                            <option value="Mr">Mr</option>
                                            <option value="Miss">Miss</option>
                                            <option value="Mrs">Mrs</option>
                                            <option value="Dr">Dr</option>
                                            <option value="Sir">Sir</option>
                                        </select>
                                        @error('title') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="guardianName"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8"> 
                                            First Name
                                        </label>
                                        <input type="text"  name="firstname" class="form-control" value="{{ $datas->firstname  }}"
                                            placeholder="Enter First name">
                                            @error('firstname') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="phoneNumber"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Othername
                                        </label>
                                        <input type="text"  name="othername" class="form-control" value="{{ $datas->othername  }}"
                                            placeholder="Enter Othername">
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="occupation"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Surname
                                        </label>
                                        <input type="text" name="surname" class="form-control"
                                            placeholder="Enter Surname" value="{{ $datas->surname  }}">
                                            @error('surname') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-4 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Nationality
                                        </label>
                                        <select class="form-control" name="nationality">
                                            <option value="" selected disabled>Select Nationality</option>
                                             @foreach($listcountry as $country)
                                            <option @if ($datas->nationality == $country->id) selected @endif value="{{$country->id}}">{{$country->name}}</option>
                                            @endforeach
                                        </select>
                                         @error('nationality') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-2 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender
                                        </label>
                                        <select class="form-control" name="gender">
                                            <option value="{{ $datas->gender  }}"    >{{ $datas->gender  }}</option>
                                             <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                            
                                        </select>
                                         @error('gender') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Date of Bith</label>
                                               
                                        <input type="text" name="dob"   class="form-control datepicker"
                                            placeholder="Enter Date of bith" value="{{ $datas->dob  }}">
                                            @error('dob') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Marital Status
                                        </label>
                                        <select class="form-control" name="marital_status">
                                            <option value="{{ $datas->marital_status  }}"    >{{ $datas->marital_status  }}</option>
                                            <option value="Married" {{ old('marital_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                            <option value="Single" {{ old('marital_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                            <option value="Divorced" {{ old('marital_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                        </select>
                                         @error('marital_status') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Email
                                        </label>
                                        <input type="text" name="email"   class="form-control "
                                            placeholder="Enter Email" value="{{ $datas->email  }}">
                                            @error('email') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                <div class="col-xl-6 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Address
                                        </label>
                                        <input type="text" name="address"   class="form-control "
                                            placeholder="Enter Address" value="{{ $datas->residential_address  }}">
                                            @error('address') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                                 <div class="col-xl-3 col-sm-6">
                                    <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Phone
                                        </label>
                                        <input type="number" name="phone"   class="form-control "
                                            placeholder="Enter Phone" value="{{ $datas->mobile  }}">
                                            @error('phone') <small style="color:red;">{{$message}}</small>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div
                            class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                            <h6 class="text-lg fw-semibold mb-0">Employee  Details</h6>
                        </div>
                        <div class="card-body p-20">
                            <div class="row gy-3">
                                <div class="col-md-4">
                                     <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Staff ID
                                        </label>
                                        <input type="text" name="staff_number" class="form-control" value="{{ $datas->employee_id}}" readonly/>
                                     </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Position 
                                        </label>
                                        <input type="text" name="position" class="form-control" value="{{ $datas->position  }}"  />
                                     </div>
                                </div>
                                <div class="col-md-4">
                                     <div class="">
                                        <label for="guardianAddress"
                                            class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status 
                                        </label>
                                        <select class="form-control" name="status">
                                            <option value="{{ $datas->status  }}"    >{{ $datas->status  }}</option>
                                             <option value="Active" {{ old('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                            
                                        </select>
                                         @error('status') <small style="color:red;">{{$message}}</small>@enderror
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
               
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-center gap-3 mt-8">
                        <button type="reset"
                            class="border border-danger-600 bg-hover-danger-200 text-danger-600 text-md px-50 py-11 radius-8">
                            Reset
                        </button>
                        <button type="submit"
                            class="btn btn-primary-600 border border-primary-600 text-md px-28 py-12 radius-8">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
</div>
@endsection

@section('scripts')
    <script>
document.getElementById("imageUpload").addEventListener("change", function(event){

    const file = event.target.files[0];

    if(file){
        const reader = new FileReader();

        reader.onload = function(e){
            document.getElementById("preview").src = e.target.result;
        }

        reader.readAsDataURL(file);
    }

});
</script>
@endsection