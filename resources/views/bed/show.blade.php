<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-upload text-success">
                        </i>
                    </div>
                    <div>Beds
                        <div class="page-title-subheading">Show details for bed <i>{{ $bed->name }}</i>
                        </div>
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
                                        <a href="/beds/{{ $bed->id }}/delete" class="nav-link" onclick="return confirm_delete()">
                                            <i class="nav-link-icon lnr-inbox"></i>
                                            <span>
                                                Delete bed
                                            </span>
                                            <div class="ms-auto badge rounded-pill bg-danger"><i class="fa fa-trash fa-w-20"></i></div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div> 
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('bed.update', $bed->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    @method('PUT')
                    <div class="position-relative row mb-3"><label for="room_id" class="form-label col-sm-2 col-form-label">Room</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-room" style="width:100%" id="room" name="room_id">
                                <option value="{{ $bed->room->id ?? '' }}">{{ $bed->room->name ?? '-' }}</option>
                            </select>
                            @error('room_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input name="name" id="name" type="text" class="form-control" value="{{ $bed->name }}">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="exampleEmail" class="form-label col-sm-2 col-form-label"></label>
                        <div class="col-sm-10"><button class="btn btn-primary">Save</button></div>
                    </div>

                </form>
            </div>
        </div>

        @if($activeBooking)
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Active Booking</h5>
                <div class="table-responsive">
                    <table class="mb-0 table table-hover">
                        <tbody>
                            <tr>
                                <th scope="row">Status</th>
                                <td>
                                    <span class="badge bg-{{ \App\Models\Booking::statuses()[$activeBooking->status]['color'] ?? 'secondary' }}">
                                        {{ \App\Models\Booking::statuses()[$activeBooking->status]['name'] ?? $activeBooking->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Flexworker</th>
                                <td>
                                    @if($activeBooking->flexworker)
                                        <a href="{{ route('flexworker.show', $activeBooking->flexworker->id) }}">
                                            {{ $activeBooking->flexworker->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Start Date</th>
                                <td>{{ $activeBooking->date_start?->format('d-m-Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">End Date</th>
                                <td>{{ $activeBooking->date_end?->format('d-m-Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">Duration</th>
                                <td>{{ $activeBooking->date_start && $activeBooking->date_end ? $activeBooking->date_start->diffInDays($activeBooking->date_end) + 1 . ' days' : 'Ongoing' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">All Bookings</h5>
                @if($bookings->count() > 0)
                    <div class="table-responsive">
                        <table class="mb-0 table table-hover">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Flexworker</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookings as $booking)
                                <tr class="{{ $booking->id === $activeBooking?->id ? 'table-active' : '' }}">
                                    <td>
                                        <span class="badge bg-{{ \App\Models\Booking::statuses()[$booking->status]['color'] ?? 'secondary' }}">
                                            {{ \App\Models\Booking::statuses()[$booking->status]['name'] ?? $booking->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($booking->flexworker)
                                            <a href="{{ route('flexworker.show', $booking->flexworker->id) }}">
                                                {{ $booking->flexworker->name }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $booking->date_start?->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ $booking->date_end?->format('d-m-Y') ?? '-' }}</td>
                                    <td>{{ $booking->date_start && $booking->date_end ? $booking->date_start->diffInDays($booking->date_end) + 1 . ' days' : '-' }}</td>
                                    <td>
                                        @php
                                            $isActive = $booking->date_start && $booking->date_start->isPast() && 
                                                       (!$booking->date_end || $booking->date_end->isFuture() || $booking->date_end->isToday());
                                            $isPast = $booking->date_end && $booking->date_end->isPast();
                                        @endphp
                                        @if($isActive)
                                            <span class="badge bg-success">Current</span>
                                        @elseif($isPast)
                                            <span class="badge bg-secondary">Past</span>
                                        @else
                                            <span class="badge bg-info">Future</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">No bookings found for this bed.</p>
                @endif
            </div>
        </div>

        <script>
            function confirm_delete() {
                return confirm('Are you sure?');
            }
        </script>
    </div>

    <script src="/assets/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.js-data-room').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/rooms",
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
        });
    </script>
</x-app-layout>