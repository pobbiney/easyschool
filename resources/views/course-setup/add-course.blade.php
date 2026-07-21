<!-- page title -->
@php $pageName = "course"; $subpageName = "add-course"; @endphp

@extends('layouts.app')
 
@section('content')

<div class="dashboard-main-body">

        <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
            <div class="">
                <h1 class="fw-semibold mb-4 h6 text-primary-light">COURSE SETUP</h1>
                <div class="">
                <a href="#" class="text-secondary-light hover-text-primary hover-underline">Dashboard </a>
                <a href="#" class="text-secondary-light hover-text-primary hover-underline d-none"> /
                    Course Setup</a>
                <span class="text-secondary-light">/ Add New Course / Subject</span>
                </div>
            </div>

              <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
                <span class="d-flex text-md">
                    <i class="ri-add-large-line"></i>
                </span>
                Add Course
            </button>
             
        </div>
        <div class="row gy-3">
            <div class="col-xl-12">
                <div class="shadow-1 radius-12 bg-base h-100 overflow-hidden">
                    <div
                        class="card-header border-bottom bg-base py-16 px-24 d-flex align-items-center justify-content-between">
                        <h6 class="text-lg fw-semibold mb-0">List Courses / Subjects</h6>
                    </div>
                    
                    <div class="card-body p-20">
                         <div class="card-body p-0 dataTable-wrapper">

                    <div
                        class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                        <div class="d-flex flex-wrap align-items-center gap-16">
                            <div class="dropdown">
                                
                               
                            </div>
                            <form class="navbar-search dt-search m-0">
                                <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable"
                                    name="search" placeholder="Search...">
                                <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                            </form>
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
                                    <th scope="col"> Name</th>
                                    <th scope="col">Course Category</th>
                                    <th scope="col">Status</th>
                                     
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listcourse as $list)
                                    
                                
                                <tr>
                                    <td> {{ $loop->iteration}}</td>
                                    <td> {{ $list->name }} </td>
                                    <td> {{ $list->category }} </td>
                                    <td>  
                                        @if ($list->status == "Active")
                                            <span  class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $list->status }}</span>
                                            @elseif ($list->status != "Active")
                                            <span  class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $list->status }}</span>
                                        @endif
                                        </td>
                                     
                                    <td>
                                       <button type="button"  data-url="{{ route('get-course-id',$list->id)  }}"
                                                        class="edit-sidebar-btn dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 showfin">
                                                        <i class="ri-edit-2-line"></i>
                                                        Edit
                                                    </button>
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
             
        
</div>
@include('course-setup.add-course-modal')
@include('course-setup.edit-course-modal')
@endsection

@section('scripts')
 <script>
     

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
     $('body').on('click', '.showfin', function(){
        var userUrl = $(this).data('url');
        console.log('Fetching URL:', userUrl); // Debug: Check URL

        $.get(userUrl, function(data){
            console.log('Data received:', data); // Debug: See exact data structure
            
            // Check if elements exist before setting values
            console.log('CourseID element:', $('#CourseID').length);
            console.log('coursename element:', $('#coursename').length);
             console.log('coursecat element:', $('#coursecat').length);
            console.log('coursedesc element:', $('#coursedesc').length);
            console.log('coursestats element:', $('#coursestats').length);
            
            
            // Set the values
            $('#CourseID').val(data.id);
            $('#coursename').val(data.name);
            $('#coursecat').val(data.category);
            $('#coursedesc').val(data.descritpion);
            $('#coursestats').val(data.status);
            
            
            // Verify values were set
            console.log('Set coursename value:', $('#coursename').val());
            console.log('Set coursecat value:', $('#coursecat').val());
            console.log('Set coursedesc value:', $('#coursedesc').val());
            console.log('Set coursestats value:', $('#coursestats').val());
           
            
            // Show the modal
            $('#exLargeModalFin').modal('show');
        }).fail(function(error) {
            console.log('Error:', error);
        });
    });
</script>
@endsection