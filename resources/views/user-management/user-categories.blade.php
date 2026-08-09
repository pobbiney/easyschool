<!-- page title -->
@php $pageName = "user-management"; $subpageName = "user-categories"; @endphp

@extends('layouts.app')

@section('content')

<div class="dashboard-main-body">

    <div class="breadcrumb d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <div class="">
            <h1 class="fw-semibold mb-4 h6 text-primary-light">USER MANAGEMENT</h1>
            <div class="">
                <a href="{{ route('dashboard') }}" class="text-secondary-light hover-text-primary hover-underline">Dashboard</a>
                <span class="text-secondary-light"> / User Categories</span>
            </div>
        </div>
        <button type="button" class="my-sidebar-btn btn btn-primary-600 d-flex align-items-center gap-6">
            <span class="d-flex text-md">
                <i class="ri-add-large-line"></i>
            </span>
            Add Category
        </button>
    </div>

    <div class="mt-24">
        <div class="card h-100">
            <div class="card-body p-0 dataTable-wrapper">

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-16 px-20 py-12 border-bottom border-neutral-200">
                    <div class="d-flex flex-wrap align-items-center gap-16">
                        <form class="navbar-search dt-search m-0">
                            <input type="text" class="dt-input bg-transparent radius-4" aria-controls="dataTable"
                                name="search" placeholder="Search...">
                            <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-8 text-secondary-light">
                        <span>Rows per page:</span>
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
                                <th scope="col">#</th>
                                <th scope="col">Category Name</th>
                                <th scope="col">Status</th>
                                <th scope="col">Allowed Screens</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="text-primary-600 fw-semibold">{{ $category->cat_name }}</span></td>
                                <td>
                                    @if($category->status == 'Active')
                                        <span class="bg-success-100 text-success-600 px-24 py-4 radius-4 fw-medium text-sm">Active</span>
                                    @else
                                        <span class="bg-danger-100 text-danger-600 px-24 py-4 radius-4 fw-medium text-sm">{{ $category->status ?: 'Inactive' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if(count($category->assigned_links) > 0)
                                        {{ implode(', ', $category->assigned_links) }}
                                    @else
                                        <span class="text-secondary-light">No screens assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="text-primary-light text-xl"
                                            data-bs-toggle="dropdown" data-bs-display="static"
                                            aria-expanded="false">
                                            <iconify-icon icon="tabler:dots-vertical"></iconify-icon>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
                                            <li>
                                                <button type="button"
                                                    data-url="{{ route('get-user-category-id', $category->cat_id) }}"
                                                    class="edit-sidebar-btn dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 show-category-edit">
                                                    <i class="ri-edit-2-line"></i>
                                                    Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button"
                                                    data-url="{{ route('get-user-category-id', $category->cat_id) }}"
                                                    class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6 show-category-delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                                    <i class="ri-delete-bin-6-line"></i>
                                                    Delete
                                                </button>
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

@include('user-management.add-category-modal')
@include('user-management.edit-category-modal')
@include('user-management.delete-category-modal')

@endsection

@section('scripts')
<script>
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

    $('body').on('click', '.show-category-edit', function () {
        var categoryUrl = $(this).data('url');

        $.get(categoryUrl, function (data) {
            $('#edit_cat_id').val(data.cat_id);
            $('#edit_cat_name').val(data.cat_name);
            $('#edit_status').val(data.status);

            $('.edit-link-checkbox').prop('checked', false);

            if (data.link_ids) {
                data.link_ids.forEach(function (linkId) {
                    $('#edit_link_' + linkId).prop('checked', true);
                });
            }
        });
    });

    $('body').on('click', '.show-category-delete', function () {
        var categoryUrl = $(this).data('url');

        $.get(categoryUrl, function (data) {
            $('#delete_cat_id').val(data.cat_id);
            $('#delete_cat_name').text(data.cat_name);
        });
    });
</script>
@endsection
