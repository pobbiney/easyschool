@php

    $staff_query = DB::select('SELECT * FROM staff WHERE id = :id', ['id' => auth()->user()->id]);

    $userCat = auth()->user()->user_cat;
    $links = DB::select('SELECT user_links.link_id, user_links.page_id,user_links.page_id_sub, user_links.link_url, user_links.link_name, user_links.link_image, user_links.link_parent FROM user_cat_links INNER JOIN user_links ON user_cat_links.link_id = user_links.link_id WHERE user_cat_links.cat_id = :id ORDER BY user_links.link_name ASC',['id' => $userCat]);
    $parents = array();
    $child = array();
    foreach ($links as $row_links) {
        if ($row_links->link_parent == 0) {
            $parents[] = $row_links;
        } else {
            $child[] = $row_links;
        }
    }
@endphp

<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description"
    content="Modern Education Admin Dashboard for schools, colleges, universities, and eLearning platforms. Includes student and course management, attendance, exams, payments, analytics, and a fully responsive clean UI—ideal for LMS, coaching centers, and academic admin systems.">
  <meta name="keywords"
    content="Education Admin Dashboard, School Admin Panel, College Dashboard, University Dashboard, LMS Dashboard, eLearning Admin Template, Student Management System, Course Management, Education Template, Study Dashboard, Online Learning Dashboard, Academic Admin Panel">
  <meta name="robots" content="INDEX,FOLLOW">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Title -->
  <title>EasySchool - School, College & LMS  </title>
 

   <link rel="icon" type="image/png" href="{{asset('assets/images/favicon.png')}}" sizes="16x16">
  <!-- remix icon font css  -->
  <link rel="stylesheet" href="{{asset('assets/css/remixicon.css')}}">
  <!-- BootStrap css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/bootstrap.min.css')}}">
  <!-- Apex Chart css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/apexcharts.css')}}">
  <!-- Data Table css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/dataTables.min.css')}}">
  <!-- Date picker css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/flatpickr.min.css')}}">
  <!-- Calendar css -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/full-calendar.css')}}">
  <!-- calendar -->
  <link rel="stylesheet" href="{{asset('assets/css/lib/calendar.css')}}">
  <!-- main css -->
  <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
      <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .sidebar-menu {
  font-family: 'Inter', 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
}

.sidebar-menu .menu-name,
.sidebar-menu > li > a > span {
  font-size: 16px;
  font-weight: 600;
}

.sidebar-submenu li a {
  font-size: 16px;
  font-weight: 600;
}
    </style>
  @yield('css')
</head>

<body>

  <!-- Theme Customization Structure Start -->
<div class="body-overlay"></div>

<button type="button"
    class="theme-customization__button w-48-px h-48-px bg-primary-600 text-white rounded-circle d-flex justify-content-center align-items-center position-fixed end-0 bottom-0 mb-40 me-40 text-2xxl bg-hover-primary-700" aria-label="Theme Customization Button">
    <i class="ri-settings-3-line animate-spin"></i>
</button>
<div class="theme-customization-sidebar w-100 bg-base h-100vh overflow-y-auto position-fixed end-0 top-0">
    <div class="d-flex align-items-center gap-3 py-16 px-24 justify-content-between border-bottom">
        <div>
            <h6 class="text-sm dark:text-white">Theme Settings</h6>
            <p class="text-xs mb-0 text-neutral-500 dark:text-neutral-200">Customize and preview instantly</p>
        </div>
        <button data-slot="button"
            class="theme-customization-sidebar__close text-neutral-900 bg-transparent text-hover-primary-600 d-flex text-xl">
            <i class="ri-close-fill"></i>
        </button>
    </div>

    <div class="d-flex flex-column gap-48 p-24 overflow-y-auto flex-grow-1">

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Theme Mode</h6>
            <div class="d-grid grid-cols-3 gap-3 dark-light-mode">
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl active"
                    data-theme="light" aria-label="light">
                    <i class="ri-sun-line"></i>
                </button>
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl"
                    data-theme="dark" aria-label="dark">
                    <i class="ri-moon-line"></i>
                </button>
                <button type="button"
                    class="theme-btn theme-setting-item__btn d-flex align-items-center justify-content-center h-64-px rounded-3 text-xl"
                    data-theme="system" aria-label="system">
                    <i class="ri-computer-line"></i>
                </button>
            </div>
        </div>

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Page Direction</h6>
            <div class="d-grid grid-cols-2 gap-3">
                <button type="button"
                    class="theme-setting-item__btn ltr-mode-btn d-flex align-items-center justify-content-center gap-2 h-56-px rounded-3 text-xl" aria-label="LTR">
                    <span><i class="ri-align-item-left-line"></i></span>
                    <span class="h6 text-sm font-medium mb-0">LTR</span>
                </button>

                <button type="button"
                    class="theme-setting-item__btn rtl-mode-btn d-flex align-items-center justify-content-center gap-2 h-56-px rounded-3 text-xl" aria-label="RTL">
                    <span class="h6 text-sm font-medium mb-0">RTL</span>
                    <span><i class="ri-align-item-right-line"></i></span>
                </button>
            </div>
        </div>

        <div class="theme-setting-item">
            <h6 class="fw-medium text-primary-light text-md mb-3">Color Schema</h6>
            <div class="d-grid grid-cols-3 gap-3">
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="base" aria-label="Base">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #25A194;"></span>
                    <span class="fw-medium mt-1" style="color: #25A194;">Base</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="red" aria-label="Red">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #dc2626;"></span>
                    <span class="fw-medium mt-1" style="color: #dc2626;">Red</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="blue" aria-label="Blue">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #2563eb;"></span>
                    <span class="fw-medium mt-1" style="color: #2563eb;">Blue</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="yellow" aria-label="Yellow">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #ff9f29;"></span>
                    <span class="fw-medium mt-1" style="color: #ff9f29;">Yellow</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="cyan" aria-label="Cyan">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #00b8f2;"></span>
                    <span class="fw-medium mt-1" style="color: #00b8f2;">Cyan</span>
                </button>
                <button type="button"
                    class="color-picker-btn d-flex flex-column justify-content-center align-items-center"
                    data-color="violet" aria-label="Violet">
                    <span class="color-picker-btn__box h-40-px w-100 rounded-3"
                        style="background-color: #7c3aed;"></span>
                    <span class="fw-medium mt-1" style="color: #7c3aed;">Violet</span>
                </button>
            </div>
        </div>

    </div>
</div>
<!-- Theme Customization Structure End -->

  <div class="overlay bg-black bg-opacity-50 w-100 h-100 position-fixed z-9 visibility-hidden opacity-0 duration-300">
  </div>
<aside class="sidebar">
  <button type="button" class="sidebar-close-btn">
    <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
  </button>
  <div class="">
    <div class="sidebar-logo d-flex align-items-center justify-content-between">
      <a href="index.html" class="">
        <img src="assets/images/logo.png" alt="site logo" class="light-logo">
        <img src="assets/images/logo-light.png" alt="site logo" class="dark-logo">
        <img src="assets/images/logo-icon.png" alt="site logo" class="logo-icon">
      </a>
      <button type="button" class="text-xxl d-xl-flex d-none line-height-1 sidebar-toggle text-neutral-500"
        aria-label="Collapse Sidebar">
        <i class="ri-contract-left-line"></i>
      </button>
    </div>
  </div>
  <!-- User Info start -->
  <div class="mx-16 py-12">
    <div class="dropdown profile-dropdown">
      <button type="button"
        class="profile-dropdown__button d-flex align-items-center justify-content-between p-10 w-100 overflow-hidden bg-neutral-50 radius-12 "
        data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
        <span class="d-flex align-items-start gap-10">
          <img src="assets/images/thumbs/leave-request-img2.png" alt="Thumbnail"
            class="w-40-px h-40-px rounded-circle object-fit-cover flex-shrink-0">
          <span class="profile-dropdown__contents">
             <span class="h6 mb-0 text-md d-block text-primary-light">{{auth()->user()->name}}</span>
            <span class="text-secondary-light text-sm mb-0 d-block">{{auth()->user()->getUserCategory()}}</span>
          </span>
        </span>
        <span class="profile-dropdown__icon pe-8 text-xl d-flex line-height-1">
          <i class="ri-arrow-right-s-line"></i>
        </span>
      </button>
      <ul class="dropdown-menu dropdown-menu-lg-end border p-12">
        <li>
          <a href="student-details.html" 
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-user-3-line"></i>
            My Profile
          </a>
        </li>
        <li>
          <a href="general.html"
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-settings-3-line"></i>
            Setting
          </a>
        </li>
        <li>
            <form action="{{ route('logout-authentication-process') }}" method="POST">
									@csrf
        <button type="submit" 
            class="dropdown-item rounded text-secondary-light bg-hover-neutral-200 text-hover-neutral-900 d-flex align-items-center gap-2 py-6">
            <i class="ri-shut-down-line"></i>
            Log Out
        </button>
          </form>
        </li>
      </ul>
    </div>
  </div>
  <!-- User Info end -->
  <div class="sidebar-menu-area">
    <ul class="sidebar-menu" id="sidebar-menu">
      <li  >
        <a href="{{route('dashboard')}}">
          <i class="ri-home-4-line"></i>
          <span class="col menu-name @if ($pageName == "dashboard") active  @endif">Dashboard </span>
        </a>
        
      </li>
       @foreach ($parents as $parent)
      <li class="dropdown @if ($pageName == $parent->page_id) active  @endif">
        <a href="javascript:void(0)">
          <i class="{{$parent->link_image}}"></i>
          <span   >{{$parent->link_name}}</span>
        </a>
        <ul class="sidebar-submenu">
           @foreach ($child as $sub)
			 @if ($parent->link_id == $sub->link_parent)
          <li>
            <a href="{{route($sub->link_url)}}" class="@if ($subpageName == $sub->page_id_sub) active @endif">
              <i class="ri-circle-fill circle-icon w-auto"></i>
             {{ $sub->link_name}}
            </a>
          </li>
           @endif
			 @endforeach
        </ul>
      </li>
       @endforeach
  
    </ul>
  </div>
</aside>

<main class="dashboard-main">
  <div class="navbar-header shadow-1">
  <div class="row align-items-center justify-content-between">
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-4">
        <button type="button" class="sidebar-mobile-toggle" aria-label="Sidebar Mobile Toggler Button">
          <iconify-icon icon="heroicons:bars-3-solid" class="icon"></iconify-icon>
        </button>
        <form class="navbar-search">
          <input type="text" class="bg-transparent" name="search" placeholder="Search">
          <iconify-icon icon="ion:search-outline" class="icon"></iconify-icon>
        </form>
      </div>
    </div>
    <div class="col-auto">
      <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" data-theme-toggle
          class="w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center" aria-label="Dark & Light Mode Button"></button>
        <div class="dropdown d-inline-block">
          <button
            class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center"
            type="button" data-bs-toggle="dropdown" aria-label="Language Change Button">
            <img src="assets/images/flags/ghana.png" alt="image" class="w-24 h-24 object-fit-cover rounded-circle">
          </button>
          
        </div><!-- Language dropdown end -->

        <div class="dropdown">
          <button
            class="has-indicator w-40-px h-40-px bg-neutral-200 rounded-circle d-flex justify-content-center align-items-center position-relative"
            type="button" data-bs-toggle="dropdown" aria-label="Notification Button">
            <iconify-icon icon="iconoir:bell" class="text-primary-light text-xl"></iconify-icon>
            <span class="w-8-px h-8-px bg-danger-600 position-absolute end-0 top-0 rounded-circle mt-2 me-2"></span>
          </button>
          <div class="dropdown-menu to-top dropdown-menu-lg p-0">
            <div
              class="m-16 py-12 px-16 radius-8 bg-primary-50 mb-16 d-flex align-items-center justify-content-between gap-2">
              <div>
                <h6 class="text-lg text-primary-light fw-semibold mb-0">Notifications</h6>
              </div>
              <span
                class="text-primary-600 fw-semibold text-lg w-40-px h-40-px rounded-circle bg-base d-flex justify-content-center align-items-center">05</span>
            </div>

            <div class="max-h-400-px overflow-y-auto scroll-sm pe-4">
              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <iconify-icon icon="bitcoin-icons:verify-outline" class="icon text-xxl"></iconify-icon>
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Congratulations</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Your profile has been Verified. Your
                      profile has been Verified</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between bg-neutral-50">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <img src="assets/images/notification/profile-1.png" alt="Image">
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Ronald Richards</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">You can stitch between artboards</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-info-subtle text-info-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    AM
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Arlene McCoy</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between bg-neutral-50">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-success-subtle text-success-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    <img src="assets/images/notification/profile-2.png" alt="Image">
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Robiul Hasan</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>

              <a href="javascript:void(0)"
                class="px-24 py-12 d-flex align-items-start gap-3 mb-2 justify-content-between">
                <div class="text-black hover-bg-transparent hover-text-primary d-flex align-items-center gap-3">
                  <span
                    class="w-44-px h-44-px bg-info-subtle text-info-main rounded-circle d-flex justify-content-center align-items-center flex-shrink-0">
                    DR
                  </span>
                  <div>
                    <h6 class="text-md fw-semibold mb-4">Darlene Robertson</h6>
                    <p class="mb-0 text-sm text-secondary-light text-w-200-px">Invite you to prototyping</p>
                  </div>
                </div>
                <span class="text-sm text-secondary-light flex-shrink-0">23 Mins ago</span>
              </a>
            </div>

            <div class="text-center py-12 px-16">
              <a href="javascript:void(0)" class="text-primary-600 fw-semibold text-md hover-underline">See All Notification</a>
            </div>

          </div>
        </div><!-- Notification dropdown end -->

      </div>
    </div>
  </div>
</div>



    @yield('content')
  

  <footer class="d-footer">
  <div class="">
    <p class="mb-0 text-center"> &copy; <span class="current-year"></span> Developed By Speedlines Technology.</p>
  </div>
</footer>
</main>

 
  <!-- jQuery library js -->
  <script src="{{asset('assets/js/lib/jquery-3.7.1.min.js')}}"></script>
  <!-- Bootstrap js -->
  <script src="{{asset('assets/js/lib/bootstrap.bundle.min.js')}}"></script>
  <!-- Apex Chart js -->
  <script src="{{asset('assets/js/lib/apexcharts.min.js')}}"></script>
  <!-- Iconify Font js -->
  <script src="{{asset('assets/js/lib/iconify-icon.min.js')}}"></script>
  <!-- Data Table js -->
  <script src="{{asset('assets/js/lib/dataTables.min.js')}}"></script>
  
  <!-- jQuery UI js -->
  <script src="{{asset('assets/js/lib/jquery-ui.min.js')}}"></script>
  
  <!-- main js -->
  <script src="{{asset('assets/js/app.js')}}"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <script>
        $(document).ready(function () {

    $('.datepicker').daterangepicker({
        singleDatePicker: true,
        autoApply: true,
        linkedCalendars: false,
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            format: 'YYYY-MM-DD'
        }
    });

    $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

});
</script>

<script>
  // ============================ Revenue Statistics Chart start ===============================
  var options = {
    series: [{
      name: 'Total Fee',
      data: [25, 35, 50, 60, 26, 20, 40, 20, 50, 16, 10, 40]
    }, {
      name: 'Collected Fee',
      data: [15, 16, 24, 30, 20, 15, 20, 10, 25, 10, 6, 20]
    }],
    chart: {
      type: 'bar',
      height: 250,
      stacked: true,
      toolbar: {
        show: false
      },
      zoom: {
        enabled: true
      }
    },
    colors: ["#25A194", "#FF7A2C"],
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: "50%",
        shape: "pyramid",
      },
    },
    xaxis: {
      categories: ['Jan', 'Feb', 'Mar', 'Apr',
        'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ],
    },
    yaxis: {
      labels: {
        formatter: function (value) {
          return "$" + value + "k";
        },
        style: {
          fontSize: "14px"
        }
      },
    },
    legend: {
      show: false,
    },
    fill: {
      opacity: 1
    }
  };

  var chart = new ApexCharts(document.querySelector("#revenueStatistic"), options);
  chart.render()
  // ============================ Revenue Statistics Chart End ===============================

  // ===================== Income Vs Expense Start =============================== 
  function createChartThree(chartId, color1, color2) {
    var options = {
      series: [{
        name: 'Income',
        data: [48, 35, 55, 32, 48, 30, 15, 50, 57]
      }, {
        name: 'Expense',
        data: [12, 20, 15, 26, 22, 60, 40, 32, 25]
      }],
      legend: {
        show: false
      },
      chart: {
        type: 'area',
        width: '100%',
        height: 260,
        toolbar: {
          show: false
        },
        padding: {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        }
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'stepline',
        width: 2,
        colors: [color1, color2],
        lineCap: 'round'
      },
      grid: {
        show: true,
        borderColor: '#D1D5DB',
        strokeDashArray: 1,
        position: 'back',
        xaxis: {
          lines: {
            show: false
          }
        },
        yaxis: {
          lines: {
            show: true
          }
        },
        row: {
          colors: undefined,
          opacity: 0.2
        },
        column: {
          colors: undefined,
          opacity: 0.2
        },
        padding: {
          top: -20,
          right: 0,
          bottom: -10,
          left: 0
        },
      },
      colors: [color1, color2],
      markers: {
        colors: [color1, color2],
        strokeWidth: 1,
        size: 0,
        hover: {
          size: 10
        }
      },
      xaxis: {
        labels: {
          show: false
        },
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        tooltip: {
          enabled: false
        },
        labels: {
          formatter: function (value) {
            return value;
          },
          style: {
            fontSize: "14px"
          }
        }
      },
      yaxis: {
        labels: {
          formatter: function (value) {
            return "$" + value + "k";
          },
          style: {
            fontSize: "14px"
          }
        },
      },
      tooltip: {
        x: {
          format: 'dd/MM/yy HH:mm'
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shade: "light",
          type: "vertical",
          opacityFrom: 0.4,
          opacityTo: 0.05,
          stops: [0, 100]
        }
      }
    };

    var chart = new ApexCharts(document.querySelector(`#${chartId}`), options);
    chart.render();
  }

  createChartThree('incomeExpense', '#16a34a', '#FF9F29');
  // ===================== Income Vs Expense End =============================== 

  // ================================ New Admissions Chart Start ================================ 
  var options = {
    series: [40, 87, 87, 30],
    colors: ['#0A51CE', '#25A194', '#FF7A2C', '#009F5E'],
    labels: ['Health', 'Business', 'Lifestyle', 'Entertainment'],
    legend: {
      show: false
    },
    chart: {
      type: 'donut',
      height: 270,
      sparkline: {
        enabled: true // Remove whitespace
      },
      margin: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      },
      padding: {
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
      }
    },
    stroke: {
      width: 2,
    },
    dataLabels: {
      enabled: false
    },
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          position: 'bottom'
        }
      }
    }],
  };

  var chart = new ApexCharts(document.querySelector("#newAdmissions"), options);
  chart.render();
  // ================================ New Admissions Chart End ================================ 

  // ================================ Animated Radial Progress Bar Start ================================ 
  $('svg.radial-progress').each(function (index, value) {
    $(this).find($('circle.complete')).removeAttr('style');
  });

  // Activate progress animation on scroll
  $(window).scroll(function () {
    $('svg.radial-progress').each(function (index, value) {
      // Trigger when the element is fully in the viewport
      if (
        $(window).scrollTop() >= $(this).offset().top - $(window).height() &&
        $(window).scrollTop() <= $(this).offset().top + $(this).height()
      ) {
        // Get percentage of progress
        const percent = $(value).data('percentage');
        // Get radius of the svg's circle.complete
        const radius = $(this).find($('circle.complete')).attr('r');
        // Get circumference (2πr)
        const circumference = 2 * Math.PI * radius;
        // Get stroke-dashoffset value based on the percentage of the circumference
        const strokeDashOffset = circumference - ((percent * circumference) / 100);
        // Transition progress for 1.25 seconds
        $(this).find($('circle.complete')).animate({ 'stroke-dashoffset': strokeDashOffset }, 1250);
      }
    });
  }).trigger('scroll');
  // ================================ Animated Radial Progress Bar End ================================

  // ============================= Calendar Js Start =================================
  let display = document.querySelector(".display");
  let days = document.querySelector(".days");
  let previous = document.querySelector(".left");
  let next = document.querySelector(".right");

  let date = new Date();

  let year = date.getFullYear();
  let month = date.getMonth();

  function displayCalendar() {
    const firstDay = new Date(year, month, 1);

    const lastDay = new Date(year, month + 1, 0);

    const firstDayIndex = firstDay.getDay(); //4

    const numberOfDays = lastDay.getDate(); //31

    let formattedDate = date.toLocaleString("en-US", {
      month: "long",
      year: "numeric"
    });

    display.innerHTML = `${formattedDate}`;

    for (let x = 1; x <= firstDayIndex; x++) {
      const div = document.createElement("div");
      div.innerHTML += "";

      days.appendChild(div);
    }

    for (let i = 1; i <= numberOfDays; i++) {
      let div = document.createElement("div");
      let currentDate = new Date(year, month, i);

      div.dataset.date = currentDate.toDateString();

      div.innerHTML += i;
      days.appendChild(div);
      if (
        currentDate.getFullYear() === new Date().getFullYear() &&
        currentDate.getMonth() === new Date().getMonth() &&
        currentDate.getDate() === new Date().getDate()
      ) {
        div.classList.add("current-date");
      }
    }
  }

  // Call the function to display the calendar
  displayCalendar();

  previous.addEventListener("click", () => {
    days.innerHTML = "";

    if (month < 0) {
      month = 11;
      year = year - 1;
    }
    month = month - 1;
    date.setMonth(month);
    displayCalendar();
  });

  next.addEventListener("click", () => {
    days.innerHTML = "";

    if (month > 11) {
      month = 0;
      year = year + 1;
    }

    month = month + 1;
    date.setMonth(month);

    displayCalendar();
  });
  // ============================= Calendar Js End =================================

</script>

 <script>
    @if(session('message_success'))
  Swal.fire({
    icon: 'success',
    title: 'Saved!',
    text: "{{ session('message_success') }}",
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false
});
    @endif
    @if(session('message_error'))
Swal.fire({
    icon: 'error',
    title: 'Error !',
    text: "{{ session('message_error') }}",
    timer: 2500,
    timerProgressBar: true,
    showConfirmButton: false
});
    @endif
</script>

@yield('scripts')
</body>

</html>