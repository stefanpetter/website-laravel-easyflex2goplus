<x-app-layout>
<div class="app-main__inner">
                        <div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-date icon-gradient bg-tempting-azure"></i>
                                    </div>
                                    <div>
                                        Bookings
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
                            <div class="card-header">Bookings created previous week</div>
                            <div class="card-body">

                                <table style="width: 100%;" id="previousWeekBookings" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Flexworker</th>
                                            <th>Status</th>
                                            <th>Date start</th>
                                            <th>Date end</th>
                                            <th>Bed</th>
                                            <th>Room</th>
                                            <th>House</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($previousWeekBookings as $booking)
                                            <tr>
                                                <td>{{ $booking->id }}</td>
                                                <td>{{ $booking->flexworker->name ?? '-' }}</td>
                                                <td>{{ ucfirst($booking->status) }}</td>
                                                <td>{{ ($booking->date_start) ? $booking->date_start->format('d-m-Y') : '-' }}</td>
                                                <td>{{ ($booking->date_end) ? $booking->date_end->format('d-m-Y') : '-' }}</td>
                                                <td>{{ $booking->bed->name ?? '-' }}</td>
                                                <td>{{ $booking->bed->room->name ?? '-' }}</td>
                                                <td>{{ $booking->bed->room->house->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tfoot>
                                </table>
                            </div>
                        </div>         
                        <div class="main-card mb-3 card">
                            <div class="card-header">All Bookings</div>
                            <div class="card-body">

                                <table style="width: 100%;" id="allBookings" class="table table-hover table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Flexworker</th>
                                            <th>Status</th>
                                            <th>Date start</th>
                                            <th>Date end</th>
                                            <th>Bed</th>
                                            <th>Room</th>
                                            <th>House</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($bookings as $booking)
                                            <tr>
                                                <td>{{ $booking->id }}</td>
                                                <td>{{ $booking->flexworker->name ?? '-' }}</td>
                                                <td>{{ ucfirst($booking->status) }}</td>
                                                <td>{{ ($booking->date_start) ? $booking->date_start->format('d-m-Y') : '-' }}</td>
                                                <td>{{ ($booking->date_end) ? $booking->date_end->format('d-m-Y') : '-' }}</td>
                                                <td>{{ $booking->bed->name ?? '-' }}</td>
                                                <td>{{ $booking->bed->room->name ?? '-' }}</td>
                                                <td>{{ $booking->bed->room->house->name ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <script>
                        $(document).ready( function () {
                            let previousWeekBookingsTable = new DataTable('#previousWeekBookings', {
                                columnDefs: [
                                    {
                                        target: 0,
                                        visible: false
                                    }
                                ],
                                order: [
                                    [3, 'asc'],
                                    [2, 'asc']
                                ]
                            });

                            let allBookingsTable = new DataTable('#allBookings', {
                                columnDefs: [
                                    {
                                        target: 0,
                                        visible: false
                                    }
                                ],
                                order: [
                                    [3, 'asc'],
                                    [2, 'asc']
                                ]
                            });

                            $('#previousWeekBookings tbody').on('click', 'tr', function() {
                                id = previousWeekBookingsTable.row(this).data()[0]
                                console.log('clicked: ' + id)
                                window.location.href = "/bookings/" + id;
                            })

                            $('#allBookings tbody').on('click', 'tr', function() {
                                id = allBookingsTable.row(this).data()[0]
                                console.log('clicked: ' + id)
                                window.location.href = "/bookings/" + id;
                            })
                        });
                    </script>
                    </x-app-layout>