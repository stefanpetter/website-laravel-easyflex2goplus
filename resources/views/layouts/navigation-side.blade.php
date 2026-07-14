<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ms-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                <li class="app-sidebar__heading">Dashboards</li>
                <li>
                    <a href="/" {!! request()->segment(1) == '' ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-graph1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="app-sidebar__heading">Scheduling</li>
                <li>
                    <a href="/bookings/weekview?week={{ date('W', strtotime('now')) }}&group_id=0&house_id=0&flexworker_id=0" {!! str_contains(request()->segment(2), 'weekview') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-keypad"></i>
                        Weekview
                    </a>
                </li>
                <li >
                    <a href="/bookings" {!! (str_contains(request()->segment(1), 'bookings') && !str_contains(request()->segment(2), 'weekview') && !str_contains(request()->segment(2), 'conflicts')) ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-date"></i>
                        Bookings
                    </a>
                </li>

                <li class="app-sidebar__heading">Housing</li>
                
                <li  >
                    <a href="/groups" {!! str_contains(request()->segment(1), 'groups') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-network"></i>
                        Groups
                    </a>
                </li>
                <li  >
                    <a href="/houses" {!! str_contains(request()->segment(1), 'houses') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-home"></i>
                        Houses
                    </a>
                </li>
                <li  >
                    <a href="/rooms" {!! str_contains(request()->segment(1), 'rooms') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-exapnd2"></i>
                        Rooms
                    </a>
                </li>
                <li  >
                    <a href="/beds" {!! str_contains(request()->segment(1), 'beds') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-upload"></i>
                        Beds
                    </a>
                </li>
                <li class="app-sidebar__heading">People</li>
                <li>
                    <a href="/flexworkers" {!! str_contains(request()->segment(1), 'flexworkers') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-users"></i>
                        Flexworkers
                    </a>
                </li>
                @can('admin')
                <li>
                    <a href="/users" {!! str_contains(request()->segment(1), 'users') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-users"></i>
                        Users
                    </a>
                </li>
                @endcan
                @can('admin')
                <li class="app-sidebar__heading">Admin</li>
                <li>
                    <a href="/bookings/conflicts" {!! str_contains(request()->segment(2), 'conflicts') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-attention"></i>
                        Booking Conflicts
                    </a>
                </li>
                <li>
                    <a href="/exports" {!! str_contains(request()->segment(1), 'exports') ? 'class="mm-active"' : '' !!}>
                        <i class="metismenu-icon pe-7s-download"></i>
                        Exports
                    </a>
                </li>
                @endcan
            </ul>
        </div>
    </div>
</div> 