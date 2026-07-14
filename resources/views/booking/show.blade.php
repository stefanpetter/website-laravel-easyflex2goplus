<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-date text-success">
                        </i>
                    </div>
                    <div>Bookings
                        <div class="page-title-subheading">Show details for booking
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
                                    <a href="/bookings/{{ $booking->id }}/vacation/create" class="nav-link">
                                        <i class="nav-link-icon lnr-inbox"></i>
                                        <span>
                                            Add vacation
                                        </span>
                                        <div class="ms-auto badge rounded-pill bg-success"><i class="fa fa-plus fa-w-20"></i></div>
                                    </a>
                                </li>
                                @if(!$booking->date_end)
                                <li class="nav-item">
                                    <a href="/bookings/{{ $booking->id }}/relocation/create" class="nav-link">
                                        <i class="nav-link-icon lnr-inbox"></i>
                                        <span>
                                            Relocate flexworker
                                        </span>
                                        <div class="ms-auto badge rounded-pill bg-primary"><i class="fa fa-house fa-w-20"></i></div>
                                    </a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a href="/bookings/{{ $booking->id }}/delete" class="nav-link" onclick="return confirm_delete()">
                                        <i class="nav-link-icon lnr-inbox"></i>
                                        <span>
                                            Delete booking
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
                <form method="POST" action="{{ route('booking.update', $booking->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    @method('PUT')
                    <div class="position-relative row mb-3"><label for="flexworker_id" class="form-label col-sm-2 col-form-label">Flexworker</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-flexworker" style="width:100%" id="flexworker_id" name="flexworker_id" autofocus required>
                            <option value="{{ $booking->flexworker_id ?? '0' }}">{{ $booking->flexworker->name ?? '-' }}</option>
                            </select>
                            @error('flexworker_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="date_start" class="form-label col-sm-2 col-form-label">Date start</label>
                        <div class="col-sm-10">
                            <input name="date_start" id="date_start" type="text" class="form-control disabled" value="{{ ($booking->date_start) ? $booking->date_start->format('d-m-Y') : '' }}" {{ ($canEditStartDate) ? '' : 'disabled' }}>
                            @error('date_start')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    @if(!$canEditStartDate)
                        <input type="hidden" name="date_start" id="date_start_hidden" value="{{ ($booking->date_start) ? $booking->date_start->format('d-m-Y') : '' }}">
                    @endif
                    <div class="position-relative row mb-3"><label for="date_end" class="form-label col-sm-2 col-form-label">Date end</label>
                        <div class="col-sm-10">
                            <input name="date_end" id="date_end" type="text" class="form-control" value="{{ ($booking->date_end) ? $booking->date_end->format('d-m-Y') : '' }}" {{ ($canEditEndDate) ? '' : 'disabled' }}>
                            @if($canEditEndDate && $futureBooking && $futureBooking->date_end)
                                <i>Due to a follow up booking, the latest enddate can be {{ $futureBooking->date_end->subDay()->format('d-m-Y') }}</i>
                            @endif
                            @error('date_end')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">Bed</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-bed" style="width:100%" id="bed_id" name="bed_id" disabled>
                                <option value="{{ $booking->bed_id }}">{{ (($booking->bed->room->house->name ?? 'House').' | '.($booking->bed->room->name ?? 'Room').' | '.$booking->bed->name) }}</option>
                            </select>
                            @error('bed_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="status" class="form-label col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-10">
                            <select name="status" id="status" class="form-control" required>
                                <option value="reserved" {{ ($booking->status == 'reserved') ? 'selected' : ''}}>Reserved</option>
                                <option value="reservedonrequest" {{ ($booking->status == 'reservedonrequest') ? 'selected' : ''}}>Reserved on request</option>
                                <option value="pendingarrival" {{ ($booking->status == 'pendingarrival') ? 'selected' : ''}}>Pending arrival</option>
                                <option value="vacation" {{ ($booking->status == 'vacation') ? 'selected' : ''}}>Vacation</option>
                                <option value="maintenance" {{ ($booking->status == 'maintenance') ? 'selected' : ''}}>Maintenance</option>
                                <option value="renovation" {{ ($booking->status == 'renovation') ? 'selected' : ''}}>Renovation</option>
                                <option value="blocked" {{ ($booking->status == 'blocked') ? 'selected' : ''}}>Blocked</option>
                            </select>
                            @error('status')
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
    </div>

    <script src="/assets/js/select2.min.js"></script>

    <script>

        $('#date_end').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY'));
        });

        function confirm_delete() {
            return confirm('Are you sure?');
        }

        $(document).ready(function() {
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

            @if($canEditStartDate)
                $('#date_start').daterangepicker({
                    "singleDatePicker": true,
                    "showWeekNumbers": true,
                    "autoApply": true,
                    "minDate": "{{ $booking->date_start->format('d-m-Y') }}",
                    @if($booking->date_end)
                       "maxDate": "{{ $booking->date_end->subDay()->format('d-m-Y') }}",
                    @endif
                    "locale": {
                      "firstDay": 1,
                      "format": "DD-MM-YYYY"
                    }
                });
            @endif
    
            @if($canEditEndDate)
                $('#date_end').daterangepicker({
                    
                    "singleDatePicker": true,
                    "showWeekNumbers": true,
                    "autoUpdateInput": false,
                    "autoApply": true,
                    "minDate": "{{ $booking->date_start->addDay()->format('d-m-Y') }}",
                    @if($futureBooking && $futureBooking->date_end)
                    "maxDate": "{{ $futureBooking->date_end->subDay()->format('d-m-Y') }}",
                    @endif
                    "locale": {
                        "firstDay": 1,
                        "format": "DD-MM-YYYY"
                    }
                });
            @endif
        });
    </script>
</x-app-layout>
