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
                        <div class="page-title-subheading">Create a new booking
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('booking.store') }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    <div class="position-relative row mb-3"><label for="flexworker_id" class="form-label col-sm-2 col-form-label">Flexworker</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-flexworker" style="width:100%" id="flexworker_id" name="flexworker_id" autofocus required>
                            <option value="0">-</option>
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
                            <input name="date_start" id="date_start" type="text" class="form-control" required>
                            @error('date_start')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="date_end" class="form-label col-sm-2 col-form-label">Date end</label>
                        <div class="col-sm-10">
                            <input name="date_end" id="date_end" type="text" class="form-control" onclick="statusOptions()">
                            @error('date_end')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div id="date_end_required_hint" class="text-warning small mt-1" style="display:none">
                                An end date is required because there are existing bookings planned after the selected start date. Without an end date, this booking would overlap with them.
                            </div>
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">Bed</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-bed" style="width:100%" id="bed_id" name="bed_id" required>
                                <option value="{{ ($bed) ? $bed->id : '0' }}">{{ ($bed) ? (($bed->room->house->name ?? 'House').' | '.($bed->room->name ?? 'Room').' | '.$bed->name) : '-' }}</option>
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
                            <select name="status" id="status" class="form-control" onclick="statusOptions()" required>
                                <option value="reserved">Reserved</option>
                                <option value="reservedonrequest">Reserved on request</option>
                                <option value="pendingarrival">Pending arrival</option>
                                <option value="vacation">Vacation</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="renovation">Renovation</option>
                                <option value="blocked">Blocked</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3" style="display:none" id="resumeVacationAvailable">
                        <label for="checkbox2" class="form-label col-sm-2 col-form-label">Resume booking after vacation</label>
                        <div class="col-sm-10">
                            <div class="position-relative form-check">
                                <label class="form-check-label">
                                    <input id="resume_booking" name="resume_booking" type="checkbox" class="form-check-input">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="position-relative row mb-3" style="display:none" id="resumeVacationUnvailable">
                        <label for="checkbox2" class="form-label col-sm-2 col-form-label">Resume booking after vacation</label>
                        <div class="col-sm-10">
                            <i>Booking cannot be resumed, because there is already a booking for this bed in the future.</i>
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
        var unavailableDates = [];
        var dateStartPicker = null;
        var dateEndPicker = null;

        function isDateDisabled(date) {
            if (unavailableDates.length === 0) return false;
            
            var checkDate = moment(date);
            
            // Disable any date that falls within an existing booking
            for (var i = 0; i < unavailableDates.length; i++) {
                var start = moment(unavailableDates[i].start, 'DD-MM-YYYY');
                var end = moment(unavailableDates[i].end, 'DD-MM-YYYY');
                
                if (checkDate.isBetween(start, end, null, '[]')) {
                    return true;
                }
            }
            return false;
        }

        function isEndDateInvalid(date) {
            if (unavailableDates.length === 0) return false;
            
            var checkDate = moment(date);
            var startDate = $('#date_start').val();
            
            // If no start date selected, use same logic as start date
            if (!startDate) {
                return isDateDisabled(date);
            }
            
            var selectedStart = moment(startDate, 'DD-MM-YYYY');
            
            // End date cannot be before start date
            if (checkDate.isBefore(selectedStart, 'day')) {
                return true;
            }
            
            // Check if the range from start to this end date would overlap with any booking
            for (var i = 0; i < unavailableDates.length; i++) {
                var bookingStart = moment(unavailableDates[i].start, 'DD-MM-YYYY');
                var bookingEnd = moment(unavailableDates[i].end, 'DD-MM-YYYY');
                
                // Check if any part of the selected range overlaps with this booking
                // Overlap exists if: selectedStart <= bookingEnd AND checkDate >= bookingStart
                if (selectedStart.isSameOrBefore(bookingEnd, 'day') && checkDate.isSameOrAfter(bookingStart, 'day')) {
                    // There's an overlap - disable this end date
                    return true;
                }
            }
            
            return false;
        }

        function checkDateEndRequired() {
            var startDate = $('#date_start').val();

            if (!startDate || unavailableDates.length === 0) {
                $('#date_end').prop('required', false);
                $('#date_end_required_hint').hide();
                return;
            }

            var selectedStart = moment(startDate, 'DD-MM-YYYY');
            var hasBookingAfter = unavailableDates.some(function(booking) {
                var bookingStart = moment(booking.start, 'DD-MM-YYYY');
                return bookingStart.isAfter(selectedStart, 'day');
            });

            if (hasBookingAfter) {
                $('#date_end').prop('required', true);
                $('#date_end_required_hint').show();
            } else {
                $('#date_end').prop('required', false);
                $('#date_end_required_hint').hide();
            }
        }

        function loadUnavailableDates(bedId) {
            if (!bedId || bedId == '0') {
                unavailableDates = [];
                if (dateStartPicker) {
                    try { $('#date_start').data('daterangepicker').remove(); } catch(e) {}
                }
                if (dateEndPicker) {
                    try { $('#date_end').data('daterangepicker').remove(); } catch(e) {}
                }
                initializeDatePickers();
                return;
            }

            $.ajax({
                url: '/api/beds/' + bedId + '/unavailable-dates',
                method: 'GET',
                success: function(response) {
                    unavailableDates = response;
                    
                    // Reinitialize date pickers with new unavailable dates
                    if (dateStartPicker) {
                        try { $('#date_start').data('daterangepicker').remove(); } catch(e) {}
                    }
                    if (dateEndPicker) {
                        try { $('#date_end').data('daterangepicker').remove(); } catch(e) {}
                    }
                    
                    initializeDatePickers();
                    checkDateEndRequired();
                },
                error: function() {
                    console.error('Failed to load unavailable dates');
                }
            });
        }

        function initializeDatePickers() {
            dateStartPicker = $('#date_start').daterangepicker({
                {!! (request('date_start') ? ('"startDate": "'.request('date_start').'",') : '') !!}
                "singleDatePicker": true,
                "showWeekNumbers": true,
                "autoApply": true,
                "locale": {
                  "firstDay": 1,
                  "format": "DD-MM-YYYY",
                  "separator": " | "
                },
                isInvalidDate: function(date) {
                    return isDateDisabled(date);
                }
            });

            // When start date changes, reinitialize end date picker and recheck requirement
            $('#date_start').on('apply.daterangepicker', function(ev, picker) {
                checkDateEndRequired();
                // Refresh end date picker to account for new start date
                if (dateEndPicker) {
                    try { $('#date_end').data('daterangepicker').remove(); } catch(e) {}
                }
                
                dateEndPicker = $('#date_end').daterangepicker({
                    "singleDatePicker": true,
                    "showWeekNumbers": true,
                    "autoUpdateInput": false,
                    "autoApply": true,
                    "minDate": picker.startDate,
                    "locale": {
                      "firstDay": 1,
                      "format": "DD-MM-YYYY",
                      "separator": " | "
                    },
                    isInvalidDate: function(date) {
                        return isEndDateInvalid(date);
                    }
                });

                $('#date_end').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD-MM-YYYY'));
                });
            });

            dateEndPicker = $('#date_end').daterangepicker({
                "singleDatePicker": true,
                "showWeekNumbers": true,
                "autoUpdateInput": false,
                "autoApply": true,
                "locale": {
                  "firstDay": 1,
                  "format": "DD-MM-YYYY",
                  "separator": " | "
                },
                isInvalidDate: function(date) {
                    return isEndDateInvalid(date);
                }
            });

            $('#date_end').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY'));
            });
        }

        function statusOptions() {
            var status = $('#status').val();
            var date_start = $('#date_start').val();
            var date_end = $('#date_end').val();
            var bedId = $('#bed_id').val();

            $('#resumeVacationAvailable').hide();
            $('#resumeVacationUnvailable').hide();

            $.ajax({
                url: '{{ route('bookings.checkFuture') }}',
                method: 'GET',
                data: {
                    bed_id: bedId,
                    date_end: date_end
                },
                success: function(response) {
                    if (status == 'vacation' && response.has_future_bookings) {
                        $('#resumeVacationUnvailable').show();
                    } 
                    else if (status == 'vacation' && !response.has_future_bookings) {
                        $('#resumeVacationAvailable').show();
                    }
                }
            });
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

            $('.js-data-bed').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/beds",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                    return {q: params.term, date_start: $('#date_start').val(), date_end: $('#date_end').val()};
                    },
                    processResults: function (data) {
                    return {results: data};
                    },
                }
            });

            // Load unavailable dates when bed is selected
            $('.js-data-bed').on('select2:select', function(e) {
                var bedId = e.params.data.id;
                loadUnavailableDates(bedId);
            });

            // Initialize date pickers
            initializeDatePickers();

            // If bed is preselected, load its unavailable dates
            @if($bed)
                loadUnavailableDates({{ $bed->id }});
            @endif
        });
    </script>
</x-app-layout>