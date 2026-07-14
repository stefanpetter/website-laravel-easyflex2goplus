<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-attention text-danger">
                        </i>
                    </div>
                    <div>Booking Conflicts
                        <div class="page-title-subheading">Overview of overlapping bookings that need attention
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">
                    Booking Conflicts 
                    @if($conflicts->count() > 0)
                        <span class="badge bg-warning text-dark">{{ $conflicts->count() }} conflict(s)</span>
                    @else
                        <span class="badge bg-success">No conflicts</span>
                    @endif
                </h5>
                
                @if($conflicts->count() > 0)
                    @foreach($conflicts as $bedId => $conflictingBookings)
                        @php
                            $bed = $conflictingBookings->first()->bed;
                        @endphp
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="pe-7s-home"></i> {{ $bed->room->house->name ?? 'House' }} | 
                                    <i class="pe-7s-albums"></i> {{ $bed->room->name ?? 'Room' }} | 
                                    <i class="pe-7s-network"></i> {{ $bed->name }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Flexworker</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($conflictingBookings as $booking)
                                            <tr>
                                                <td>
                                                    <a href="/bookings/{{ $booking->id }}" class="badge bg-info">
                                                        #{{ $booking->id }}
                                                    </a>
                                                </td>
                                                <td>{{ $booking->flexworker->name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $booking->statuses()[$booking->status]['color'] }}">
                                                        {{ $booking->statuses()[$booking->status]['name'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $booking->date_start ? $booking->date_start->format('d-m-Y') : '-' }}</td>
                                                <td>{{ $booking->date_end ? $booking->date_end->format('d-m-Y') : 'Ongoing' }}</td>
                                                <td>
                                                    <a href="/bookings/{{ $booking->id }}" class="btn btn-sm btn-primary">
                                                        <i class="pe-7s-pen"></i> Edit
                                                    </a>
                                                    <a href="/bookings/{{ $booking->id }}/delete" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Are you sure you want to delete this booking?')">
                                                        <i class="pe-7s-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="alert alert-light border mt-2">
                                    <small class="text-muted">{{ $conflictingBookings->count() }} bookings with overlapping dates on this bed.</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="alert alert-light border" role="alert">
                        <i class="pe-7s-check"></i> No booking conflicts detected.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
