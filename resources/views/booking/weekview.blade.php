<x-app-layout>
    <div class="app-main__inner">
            <div class="app-page-title">
                <div class="page-title-wrapper">
                    <div class="page-title-heading">
                        <div class="page-title-icon">
                            <i class="pe-7s-date icon-gradient bg-tempting-azure"></i>
                        </div>
                        <div>
                            Bookings Weekview
                        </div>
                    </div>
                    <div class="page-title-actions">
                        <div class="d-inline-block dropdown">
                            <button type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn-shadow dropdown-toggle btn btn-info">
                                <span class="btn-icon-wrapper pe-2 opacity-7">
                                    <i class="fa fa-business-time fa-w-20"></i>
                                </span>
                                Actions
                            </button>
                            <div tabindex="-1" role="menu" aria-hidden="true" class="dropdown-menu dropdown-menu-right">
                                <ul class="nav flex-column">
                                    <li class="nav-item">
                                        <a href="/bookings/create" class="nav-link">
                                            <i class="nav-link-icon lnr-inbox"></i>
                                            <span>
                                                Create booking
                                            </span>
                                            <div class="ms-auto badge rounded-pill bg-success"><i class="fa fa-plus fa-w-20"></i></div>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a disabled href="javascript:void(0);" class="nav-link disabled">
                                            <i class="nav-link-icon lnr-file-empty"></i>
                                            <span>
                                                Export to CSV
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>   
                </div>
            </div>            
            <div class="main-card mb-3 card">

            <div class="card-header">

                <div class="btn-actions-pane-left">
                    <div role="group" class="btn-group-sm nav btn-group">
                        <a href="/bookings/weekview?week={{ \Carbon\Carbon::now()->format('W') }}&year={{ \Carbon\Carbon::now()->format('o') }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow btn btn-primary">Current week</a>
                    </div>
                </div>

                <div class="btn-actions-pane-right">
                    <div role="group" class="btn-group-sm nav btn-group">
                        <a href="/bookings/weekview?week={{ $weekMinus1 }}&year={{ $yearMinus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow btn btn-primary"><span aria-hidden="true">«</span></a>
                        <a href="/bookings/weekview?week={{ $weekMinus2 }}&year={{ $yearMinus2 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekMinus2 }}</a>
                        <a href="/bookings/weekview?week={{ $weekMinus1 }}&year={{ $yearMinus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekMinus1 }}</a>
                        <a href="/bookings/weekview?week={{ $currentWeek }}&year={{ $currentYear }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow active btn btn-primary">{{ $currentWeek }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus1 }}&year={{ $yearPlus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekPlus1 }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus2 }}&year={{ $yearPlus2 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekPlus2 }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus1 }}&year={{ $yearPlus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary"><span aria-hidden="true">»</span></a>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <form method="GET" id="houseForm" class="row g-6">
                    <div class="col-md-2">
                        <select class="form-control js-data-house" style="width:100%" id="house" name="house_id">
                            @if(request('house_id') > 0)
                                <option value="{{ $filterHouse->id }}">{{ $filterHouse->name }}</option>
                            @else
                                <option value="">Filter on house...</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control js-data-group" style="width:100%" id="group" name="group_id">
                            @if(request('group_id') > 0)
                                <option value="{{ $filterGroup->id }}">{{ $filterGroup->name }}</option>
                            @else
                                <option value="">Filter on group...</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control js-data-flexworker" style="width:100%" id="group" name="flexworker_id">
                            @if(request('flexworker_id') > 0)
                                <option value="{{ $filterFlexworker->id }}">{{ $filterFlexworker->name }}</option>
                            @else
                                <option value="">Filter on flexworker...</option>
                            @endif
                        </select>
                    </div>
                </form>

                <br />

                <table class="mb-0 table table-hover table-striped">
                    <thead>
                        <tr>
                            <th style="width: 13%">Info (Week {{ $currentWeek }})</th>
                            <th style="width: 3%">&nbsp;</th>
                            @for($i=0; $i<7; $i++)
                                <th style="width: 12%">{{ $weekArray[$i]['title'] }}<br />{{ $weekArray[$i]['subtitle'] }}</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($houses as $house)
                            @if(!is_array($filterHouses) || is_array($filterHouses) && in_array($house->id, $filterHouses))
                                <tr>
                                    <th scope="row">{{ $house->name }} @if($house->status != 'available')({{ ucfirst($house->status) }})@endif</th>
                                    <td>&nbsp;</td>
                                    <td colspan=7>&nbsp;</td>
                                </tr>

                                @foreach($house->rooms as $room)
                                    @if(!is_array($filterRooms) || is_array($filterRooms) && in_array($room->id, $filterRooms))
                                        <tr>
                                            <td align="right"><b>{{ $room->name }}</b></td>
                                            <td>&nbsp;</td>
                                            <td align="center" colspan=7>&nbsp;</td>
                                        </tr>

                                        @foreach($room->beds as $bed)
                                            @if(!is_array($filterBeds) || is_array($filterBeds) && in_array($bed->id, $filterBeds))
                                                <tr>
                                                    <td align="right">{{ $bed->name }}</td>
                                                    <td>&nbsp;</td>

                                                    @for($j=0; $j<7; $j++)
                                                        @php
                                                            $todaybooking = false;
                                                            $currentDate = $weekArray[$j]['date']; // Y-m-d format string
                                                            $formattedDate = \Carbon\Carbon::parse($currentDate)->format('d-m-Y');
                                                        @endphp

                                                        @if($bookings->has($bed->id))

                                                            @foreach($bookings[$bed->id] as $booking)
                                                                @if($booking->date_start->format('Y-m-d') <= $currentDate && (!$booking->date_end || $booking->date_end->format('Y-m-d') >= $currentDate))
                                                                    @php
                                                                        $todaybooking = $booking;
                                                                        $bookingStatuses = $booking->statuses();
                                                                        $statusColor = $bookingStatuses[$booking->status]['color'];
                                                                        $statusName = $bookingStatuses[$booking->status]['name'];
                                                                        $daysRemaining = ($booking->status == 'reserved' && $booking->date_end) ? \Carbon\Carbon::parse($currentDate)->diffInDays($booking->date_end) : 0;
                                                                    @endphp
                                                                @endif
                                                            @endforeach

                                                            @if($todaybooking)
                                                                <td>
                                                                    <a href="/bookings/{{ $todaybooking->id }}">
                                                                        <button class="mb-2 me-2 btn btn-{{ $statusColor }} btn-sm ">
                                                                            {{ $todaybooking->flexworker->name ?? '-' }}<br />
                                                                            <i>{{ $statusName }} {{ $daysRemaining > 0 ? "ending in {$daysRemaining} days" : '' }}</i>
                                                                        </button>
                                                                    </a>
                                                                </td>
                                                            @else
                                                                <td>
                                                                    @if($house->status == 'available')
                                                                        <a href="/bookings/create?date_start={{ $formattedDate }}&bed_id={{ $bed->id }}">
                                                                            <button class="mb-2 me-2 btn btn-success btn-sm ">
                                                                                <span class="btn-icon-wrapper">
                                                                                    <i class="pe-7s-date"></i>  
                                                                                </span>
                                                                            </button>
                                                                        </a>
                                                                    @else
                                                                        <button class="mb-2 me-2 btn btn-success btn-sm " disabled>
                                                                            <span class="btn-icon-wrapper">
                                                                                <i class="pe-7s-date"></i>
                                                                            </span>
                                                                        </button>
                                                                    @endif
                                                                </td>
                                                            @endif
                                                            
                                                        @else
                                                            <td>
                                                                @if($house->status == 'available')
                                                                    <a href="/bookings/create?date_start={{ $formattedDate }}&bed_id={{ $bed->id }}">
                                                                        <button class="mb-2 me-2 btn btn-success btn-sm ">
                                                                            <span class="btn-icon-wrapper">
                                                                                <i class="pe-7s-date"></i>  
                                                                            </span>
                                                                        </button>
                                                                    </a>
                                                                @else
                                                                    <button class="mb-2 me-2 btn btn-danger btn-sm " disabled>
                                                                        <span class="btn-icon-wrapper">
                                                                            <i class="pe-7s-close-circle"></i>
                                                                        </span>
                                                                    </button>
                                                                @endif
                                                            </td>
                                                        @endif
                                                    @endfor
                                                </tr>
                                            @endif
                                        @endforeach 
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </tbody>
                </table>
                <br />             
            </div>
            <div class="card-footer">
    
                <div class="btn-actions-pane-left">
                    <div role="group" class="btn-group-sm nav btn-group">
                        <a href="/bookings/weekview?week={{ \Carbon\Carbon::now()->format('W') }}&year={{ \Carbon\Carbon::now()->format('o') }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow btn btn-primary">Current week</a>
                    </div>
                </div>

                <div class="btn-actions-pane-right">
                    <div role="group" class="btn-group-sm nav btn-group">
                        <a href="/bookings/weekview?week={{ $weekMinus1 }}&year={{ $yearMinus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow btn btn-primary"><span aria-hidden="true">«</span></a>
                        <a href="/bookings/weekview?week={{ $weekMinus2 }}&year={{ $yearMinus2 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekMinus2 }}</a>
                        <a href="/bookings/weekview?week={{ $weekMinus1 }}&year={{ $yearMinus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekMinus1 }}</a>
                        <a href="/bookings/weekview?week={{ $currentWeek }}&year={{ $currentYear }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow active btn btn-primary">{{ $currentWeek }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus1 }}&year={{ $yearPlus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekPlus1 }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus2 }}&year={{ $yearPlus2 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary">{{ $weekPlus2 }}</a>
                        <a href="/bookings/weekview?week={{ $weekPlus1 }}&year={{ $yearPlus1 }}&house_id={{ request('house_id') }}&group_id={{ request('group_id') }}&flexworker_id={{ request('flexworker_id') }}" class="btn-shadow  btn btn-primary"><span aria-hidden="true">»</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/assets/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.js-data-house').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/houses",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                    return {q: params.term};
                    },
                    processResults: function (data) {
                    return {results: data};
                    },
                }
            });

            $('.js-data-group').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/groups",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                    return {q: params.term};
                    },
                    processResults: function (data) {
                    return {results: data};
                    },
                }
            });

            $('.js-data-flexworker').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/flexworkers",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                    return {q: params.term};
                    },
                    processResults: function (data) {
                    return {results: data};
                    },
                }
            });

            $('.js-data-house').on('select2:select', function(e) {
                var house_id = e.params.data.id;
                window.location.href = "/bookings/weekview?week={{ $currentWeek }}&year={{ $currentYear }}&group_id=0&house_id=" + house_id + '&flexworker_id=0';
            });

            $('.js-data-group').on('select2:select', function(e) {
                var group_id = e.params.data.id;
                window.location.href = "/bookings/weekview?week={{ $currentWeek }}&year={{ $currentYear }}&group_id=" + group_id + "&house_id=0&flexworker_id=0";
            });

            $('.js-data-flexworker').on('select2:select', function(e) {
                var flexworker_id = e.params.data.id;
                window.location.href = "/bookings/weekview?week={{ $currentWeek }}&year={{ $currentYear }}&group_id=0&house_id=0&flexworker_id=" + flexworker_id;
            });
        });
    </script>
</x-app-layout>