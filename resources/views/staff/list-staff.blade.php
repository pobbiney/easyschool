<!-- page title -->
@php $pageName = "staff"; $subpageName = "list-staff"; @endphp

@extends('layouts.app')
 
@section('content')

<div class="dashboard-main-body">

        <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <div class="">
                <h1 class="fw-semibold mb-4 h6 text-primary-light">STAFF MANAGEMENT</h1>
                <div class="">
                <a href="index.html" class="text-secondary-light hover-text-primary hover-underline">Dashboard </a>
                <a href="guardian-list.html" class="text-secondary-light hover-text-primary hover-underline d-none"> /
                    Staff Management</a>
                <span class="text-secondary-light">/ List Staff</span>
                </div>
            </div>
           
        </div>
              
                          
        <div class="mt-24">
             
            <div class="card h-100">
                
                <div class="card-body p-0 dataTable-wrapper">

                    <div
                        class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                        <div class="d-flex flex-wrap align-items-center gap-16">
                             
                            <form class="navbar-search dt-search m-0">
                                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable"
                                    name="search" placeholder="Search...">
                                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                            </form>
                            <div class="dropdown">
                                <button type="button"
                                    class="px-12 py-5-px border border-neutral-300 radius-8 d-flex align-items-center gap-20"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="d-flex align-items-center gap-1 text-secondary-light text-sm">
                                        Filter
                                    </span>
                                    <span class="">
                                        <i class="ri-arrow-down-s-line"></i>
                                    </span>
                                </button>
                                <div class="dropdown-menu border bg-base shadow dropdown-menu-lg p-0">
                                    <div
                                        class="d-flex align-items-center justify-content-between border-bottom py-8 px-16">
                                        <span class="fw-semibold text-lg text-primary-light">Filter</span>
                                        <button type="button">
                                            <i class="ri-close-large-line"></i>
                                        </button>
                                    </div>

                                    <form action="#" class="p-16">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="subject"
                                                    class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Gender</label>
                                                <select id="subject" class="form-control form-select">
                                                    <option value="Select">Select Subject</option>
                                                    <option value="Match">Match</option>
                                                    <option value="English">English</option>
                                                    <option value="Bangla">Bangla</option>
                                                    <option value="Economics">Economics</option>
                                                    <option value="Physics">Physics</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label for="status"
                                                    class="text-sm fw-semibold text-primary-light d-inline-block mb-8">Status</label>
                                                <select id="status" class="form-control form-select">
                                                    <option value="Select">Select Status</option>
                                                    <option value="Active">Active</option>
                                                    <option value="Inactive">Inactive</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <button type="reset"
                                                    class="btn btn-danger-200 text-danger-600 w-100">Reset</button>
                                            </div>
                                            <div class="col-6">
                                                <button type="submit" class="btn btn-primary-600 w-100">Apply</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-8 text-secondary-light">
                            <span class="">
                                Rows per page:
                            </span>
                            <div class="dt-length">
                                <select name="dataTable_length" aria-controls="dataTable"
                                    class="dt-input form-control form-select">
                                    <option value="5">5</option>
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="p-0">
                        <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length='10'>
                            <thead>
                                <tr>
                                    <th scope="col">
                                        #
                                    </th>
                                    <th scope="col">Staff ID</th>
                                    <th scope="col">Staff Name</th>
                                    <th scope="col">Gender</th>
                                    <th scope="col">Dob</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone Number</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Position</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($liststaff as $list)
                                <tr>
                                    <td>
                                        {{$loop->iteration}}
                                    </td>
                                    <td><span class="text-primary-600">{{$list->employee_id}}</span></td>
                                    <td>
                                         <div class="d-flex align-items-center">
                                            @if(!empty($list->picture))
                                                <img src="{{$list->picture }}"
                                                    alt="{{ $list->surname . ' ' . $list->firstname }}"
                                                    class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                            @elseif(strtolower($list->gender) == 'male')
                                                <img src="{{ asset('assets/images/thumbs/guardian-img1.png') }}"
                                                    alt="Male Staff"
                                                    class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                            @else
                                                <img src="{{ asset('assets/images/thumbs/guardian-img2.png') }}"
                                                    alt="Female Staff"
                                                    class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                            @endif
                                            <div class="">
                                                <h6 class="text-md mb-0 fw-medium flex-grow-1">{{ $list->surname . ' ' . $list->firstname }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{$list->gender}}</td>
                                    <td>{{$list->dob}}</td>
                                    <td>{{$list->email}}</td>
                                    <td>{{$list->mobile}}</td>
                                    <td>{{$list->residential_address}}</td>
                                    <td>{{$list->position}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="text-primary-light text-xl"
                                                data-bs-toggle="dropdown" data-bs-display="static"
                                                aria-expanded="false">
                                                <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
                                                <li>
                                                    <a href="student-list.html"
                                                        class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                        <i class="ri-user-3-line"></i>
                                                        View Student
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="edit-teacher.html"
                                                        class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                        <i class="ri-edit-2-line"></i>
                                                        Edit
                                                    </a>
                                                </li>
                                               
                                                <li>
                                                    <button class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 showmodal"  data-url="{{ route('staff-id',$list->id)  }}"  data-bs-toggle="modal" data-bs-target="#exampleModalDelete"><i class="ri-delete-bin-6-line"></i>Delete</button>
                                                </li>
                                                 <li>
                                                    <button class="edit-sidebar-btn dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 showmodalupload"  data-url="{{ route('staff-id',$list->id)  }}"  data-bs-toggle="modal" data-bs-toggle="modal"
                                                         ><i class="ri-upload-cloud-2-line"></i>Upload Documents</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
       
</div>

 
@include('staff.delete-staff-modal')
@include('staff.staff-document-modal')
<!-- Edit sidebar end -->
@endsection

@section('scripts')
    <script>
         $(document).ready(function(){
    

    $('body').on('click', '.showmodal', function(){
        var userUrl = $(this).data('url');
        console.log('Fetching URL:', userUrl); // Debug: Check URL

        $.get(userUrl, function(data){
            console.log('Data received:', data); // Debug: See exact data structure
            
            // Check if elements exist before setting values
            console.log('staffID element:', $('#staffID').length);
            console.log('staffsurname element:', $('#staffsurname').length);
            console.log('stafffirstname element:', $('#stafffirstname').length);
            
            
            // Set the values
            $('#staffID').val(data.id);
            $('#staffsurname').text(data.surname);
            $('#stafffirstname').text(data.firstname);
            
            // Verify values were set
            console.log('Set staffsurname value:', $('#staffsurname').val());
            console.log('Set stafffirstname value:', $('#stafffirstname').val());
            
            // Show the modal
            $('#exampleModalDelete').modal('show');
        }).fail(function(error) {
            console.log('Error:', error);
        });
    });

    
});


  $(document).ready(function(){
    

    $('body').on('click', '.showmodalupload', function(){
        var userUrl = $(this).data('url');
        console.log('Fetching URL:', userUrl); // Debug: Check URL

        $.get(userUrl, function(data){
            console.log('Data received:', data); // Debug: See exact data structure
            
            // Check if elements exist before setting values
            console.log('staffIDs element:', $('#staffIDs').length);
            
            // Set the values
            $('#staffIDs').val(data.id);
           
            // Verify values were set
            console.log('Set staffsurname value:', $('#staffsurname').val());
            console.log('Set stafffirstname value:', $('#stafffirstname').val());
            
            // Show the modal
           
        }).fail(function(error) {
            console.log('Error:', error);
        });
    });

    
});


 // Sidebar js start
    $('.my-sidebar-btn').on('click', function () {
        $('.my-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-my-sidebar, .overlay').on('click', function () {
        $('.my-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });


    $('.edit-sidebar-btn').on('click', function () {
        $('.edit-sidebar').addClass('active');
        $('.overlay').addClass('active');
    });
    $('.close-edit-sidebar, .overlay').on('click', function () {
        $('.edit-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });
    // Sidebar js end
</script>

 <script>
let counter = 0;

$('#addDocument').click(function(){

    let year = $('#year').val();
    let level = $('#level option:selected').text();
    let qualification = $('#qualification').val();
    let fileInput = $('#document')[0];
    let file = fileInput.files[0]; // may be undefined if no file chosen

    if(!level){
        alert('Select Level');
        return;
    }

     

    if(year == ''){
        alert('Enter Year');
        return;
    }

    if(qualification == ''){
        alert('Enter Qualification');
        return;
    }

     if(fileInput.files.length == 0){
        alert('Choose a document to upload');
        return;
    }

    // File is now optional — no validation blocking it
    let fileName = file ? file.name : 'No file';

    let row = `
        <tr id="row_${counter}">
            <td>${level}</td>
            <td>${year}</td>
            <td>${qualification}</td>
            <td>${fileName}</td>
            <td>
                <button type="button" class="btn btn-danger btn-sm removeDoc" data-id="${counter}">
                    Remove
                </button>
            </td>
        </tr>
    `;

    $('#documentTable tbody').append(row);

    $('#documentContainer').append(`
        <div id="doc_${counter}" style="display:none">
            <input type="hidden" name="documents[${counter}][level]" value="${level}">
            <input type="hidden" name="documents[${counter}][year]" value="${year}">
            <input type="hidden" name="documents[${counter}][qualification]" value="${qualification}">
            <input type="file"
                   name="documents[${counter}][document]"
                   class="realFileInput">
        </div>
    `);

    // Only attach the file if one was actually selected
    if (file) {
        let realInput = $('#doc_'+counter+' .realFileInput')[0];
        const dt = new DataTransfer();
        dt.items.add(file);
        realInput.files = dt.files;
    }

    $('#level').val('');
    $('#year').val('');
    $('#qualification').val('');
    $('#document').val('');

    counter++;
    toggleSaveButton();
});

$(document).on('click', '.removeDoc', function(){
    let id = $(this).data('id');
    $('#row_'+id).remove();
    $('#doc_'+id).remove();

    toggleSaveButton();
});

function toggleSaveButton(){
    let rowCount = $('#documentTable tbody tr').length;
    $('#saveBtn').prop('disabled', rowCount === 0);
}

toggleSaveButton();
</script>
@endsection