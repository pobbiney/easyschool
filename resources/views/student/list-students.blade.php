@php $pageName = "student"; $subpageName = "list-students"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div>
            <h1 class="fw-semibold mb-4 h6 text-primary-light">STUDENT MANAGEMENT</h1>
            <div>
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / Student List</span>
            </div>
        </div>
        <a href="{{ route('add-student') }}" class="btn btn-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md"><i class="ri-add-large-line"></i></span>
            Register New Student
        </a>
    </div>

    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <form class="navbar-search dt-search m-0">
                        <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable" name="search" placeholder="Search students...">
                        <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                    </form>
                    <div class="d-flex align-items-center gap-8 text-secondary-light">
                        <span>Rows per page:</span>
                        <div class="dt-length">
                            <select name="dataTable_length" aria-controls="dataTable" class="dt-input form-control form-select">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-0">
                    <table class="table bordered-table mb-0 data-table" id="dataTable" data-page-length="10">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Date of Birth</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $student->student_id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(!empty($student->picture))
                                            <img src="{{ asset($student->picture) }}" alt="{{ $student->full_name }}" class="flex-shrink-0 me-12 radius-8" width="44" height="44" style="object-fit:cover;">
                                        @elseif(strtolower($student->gender) == 'male')
                                            <img src="{{ asset('assets/images/thumbs/guardian-img1.png') }}" alt="Student" class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                        @else
                                            <img src="{{ asset('assets/images/thumbs/guardian-img2.png') }}" alt="Student" class="flex-shrink-0 me-12 radius-8" width="44" height="44">
                                        @endif
                                        <div>
                                            <h6 class="text-md mb-0 fw-medium">{{ $student->full_name }}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->class_name }}</td>
                                <td>{{ $student->dob }}</td>
                                <td>{{ $student->gender }}</td>
                                <td>{{ $student->phone }}</td>
                                <td>{{ $student->category ?: '-' }}</td>
                                <td>
                                    @if($student->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @elseif($student->status == 'Draft')
                                        <span class="bg-warning-100 text-warning-600 px-24 py-4 radius-4 fw-medium text-sm">Draft</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $student->status }}</span>
                                    @endif
                                </td>
                                <td class="table-action-cell">
                                    <div class="dropdown">
                                        <button type="button" class="text-primary-light text-xl" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                                            <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
                                            <li>
                                                <a href="{{ route('view-student-details', Crypt::encrypt($student->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-user-3-line"></i> View Student
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('edit-student', Crypt::encrypt($student->id)) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
                                                    <i class="ri-edit-2-line"></i> Edit
                                                </a>
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

@endsection
