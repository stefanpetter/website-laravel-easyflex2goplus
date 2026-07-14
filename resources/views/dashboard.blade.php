<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-graph1 icon-gradient bg-mean-fruit">
                        </i>
                    </div>
                    <div>Dashboard
                        <div class="page-title-subheading"></div>
                    </div>
                </div>
                </div>
        </div>            
        <div class="row">
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content bg-midnight-bloom">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Total Beds</div>
                            <div class="widget-subheading">SNF / Actual</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white"><span>{{ $totalSnfBeds }} / {{ $totalBeds }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content bg-arielle-smile">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Booked beds</div>
                            <div class="widget-subheading">Actual beds with a booking for today</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white"><span>{{ $bookedBedsToday }} ({{ round((100 / $totalBeds * $bookedBedsToday), 1) }}%)</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4">
                <div class="card mb-3 widget-content bg-grow-early">
                    <div class="widget-content-wrapper text-white">
                        <div class="widget-content-left">
                            <div class="widget-heading">Flexworkers</div>
                            <div class="widget-subheading">Flexworkers with status Active</div>
                        </div>
                        <div class="widget-content-right">
                            <div class="widget-numbers text-white"><span>{{ $totalFlexworkers }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">Flexworkers arriving within 2 weeks</div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Flexworker</th>
                                <th>Status</th>
                                <th>End date</th>
                                <th>House</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(count($arrivingBookings) > 0)
                                    @foreach($arrivingBookings as $booking)
                                        <tr id="{{ $booking->id }}" onclick="openRow(this)">
                                            <td>
                                                <div class="widget-content p-0">
                                                    <div class="widget-content-wrapper">

                                                        <div class="widget-content-left flex2">
                                                            {{ $booking->flexworker->name ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge bg-{{ $booking->statuses()[$booking->status]['color'] ?? '-' }}">{{ $booking->statuses()[$booking->status]['name'] ?? '-' }}</div>
                                            </td>
                                            <td>
                                                {{ $booking->date_end->format('d-m-Y') ?? '-' }} 
                                            </td>
                                            <td>
                                                {{ $booking->bed->room->house->name ?? '-' }} | {{ $booking->bed->room->name ?? '-' }} | {{ $booking->bed->name ?? '-' }} 
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="main-card mb-3 card">
                    <div class="card-header">Bookings ending within 2 weeks</div>
                    <div class="table-responsive">
                        <table class="align-middle mb-0 table table-borderless table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Flexworker</th>
                                <th>Status</th>
                                <th>End date</th>
                                <th>House</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(count($endingBookings) > 0)
                                    @foreach($endingBookings as $booking)
                                        <tr id="{{ $booking->id }}" onclick="openRow(this)">
                                            <td>
                                                <div class="widget-content p-0">
                                                    <div class="widget-content-wrapper">

                                                        <div class="widget-content-left flex2">
                                                            {{ $booking->flexworker->name ?? '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="badge bg-{{ $booking->statuses()[$booking->status]['color'] ?? '-' }}">{{ $booking->statuses()[$booking->status]['name'] ?? '-' }}</div>
                                            </td>
                                            <td>
                                                {{ $booking->date_end->format('d-m-Y') ?? '-' }} 
                                            </td>
                                            <td>
                                                {{ $booking->bed->room->house->name ?? '-' }} | {{ $booking->bed->room->name ?? '-' }} | {{ $booking->bed->name ?? '-' }} 
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="mb-3 card">
                    <div class="card-header-tab card-header-tab-animation card-header">
                        <div class="card-header-title">
                            <i class="header-icon lnr-apartment icon-gradient bg-love-kiss"> </i>
                            Status per bed
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="bookings-doughnut-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        function openRow(row){
            window.location.href = "/bookings/" + row.id;
        }

         // Doughnut Chart
        if (document.getElementById("bookings-doughnut-chart")) {

            const  ctx = document.getElementById("bookings-doughnut-chart").getContext("2d");

            myDoughnut = new Chart(ctx, {
                type: "doughnut",
                data: {!! $bookingsForDoughnutChart !!},
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                    legend: {
                        position: "top"
                    },
                    title: {
                        display: false,
                        text: "Chart.js Doughnut Chart"
                    }
                    }
                }
            });
        };
        
    </script>
</x-app-layout>
