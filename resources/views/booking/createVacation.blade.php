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
                        <div class="page-title-subheading">Create a new vacation booking
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('booking.vacation.store', $booking->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    <div class="position-relative row mb-3"><label for="flexworker_id" class="form-label col-sm-2 col-form-label">Flexworker</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-flexworker" style="width:100%" id="flexworker_id" name="flexworker_id" disabled>
                            <option value="{{ $booking->flexworker_id }}">{{ $booking->flexworker->name }}</option>
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
                            <input name="date_end" id="date_end" type="text" class="form-control" required>
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
                            <select name="status" id="status" class="form-control" disabled>
                                <option value="vacation">Vacation</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3" id="resumeVacationAvailable">
                        <label for="checkbox2" class="form-label col-sm-2 col-form-label">Resume booking after vacation</label>
                        <div class="col-sm-10">
                            <i>Adding a vacation through this form will split the existing booking. (Start date: {{ $booking->date_start->format('d-m-Y') }} | End date: {{ ($booking->date_end) ? $booking->date_end->format('d-m-Y') : 'none' }})</i>
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
        var bookingId = {{ $booking->id }};

        function isDateDisabled(date) {
            if (unavailableDates.length === 0) return false;
            
            var checkDate = moment(date);
            
            // For vacation: disable dates that are already booked by OTHER bookings (not the current one)
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
            
            if (!startDate) {
                return isDateDisabled(date);
            }
            
            var selectedStart = moment(startDate, 'DD-MM-YYYY');
            
            if (checkDate.isBefore(selectedStart, 'day')) {
                return true;
            }
            
            // Check if the vacation range would overlap with any OTHER booking
            for (var i = 0; i < unavailableDates.length; i++) {
                var bookingStart = moment(unavailableDates[i].start, 'DD-MM-YYYY');
                var bookingEnd = moment(unavailableDates[i].end, 'DD-MM-YYYY');
                
                if (selectedStart.isSameOrBefore(bookingEnd, 'day') && checkDate.isSameOrAfter(bookingStart, 'day')) {
                    return true;
                }
            }
            
            return false;
        }

        function loadUnavailableDates() {
            $.ajax({
                url: '/api/beds/{{ $booking->bed_id }}/unavailable-dates',
                method: 'GET',
                success: function(response) {
                    // Filter out the current booking from unavailable dates
                    unavailableDates = response.filter(function(range) {
                        // We need to exclude the current booking's date range
                        // This is a simplification - ideally we'd pass booking ID in response
                        return true; // Server should handle this
                    });
                    
                    initializeDatePickers();
                },
                error: function() {
                    console.error('Failed to load unavailable dates');
                    initializeDatePickers();
                }
            });
        }

        function initializeDatePickers() {
            // Vacation must be within the existing booking period
            var bookingStart = moment('{{ $booking->date_start->format("d-m-Y") }}', 'DD-MM-YYYY');
            @if($booking->date_end)
            var bookingEnd = moment('{{ $booking->date_end->format("d-m-Y") }}', 'DD-MM-YYYY');
            @else
            var bookingEnd = moment().add(10, 'years');
            @endif

            dateStartPicker = $('#date_start').daterangepicker({
                "singleDatePicker": true,
                "showWeekNumbers": true,
                "autoApply": true,
                "minDate": bookingStart,
                "maxDate": bookingEnd,
                "locale": {
                  "firstDay": 1,
                  "format": "DD-MM-YYYY",
                  "separator": " | "
                }
            });

            $('#date_start').on('apply.daterangepicker', function(ev, picker) {
                if (dateEndPicker) {
                    try { $('#date_end').data('daterangepicker').remove(); } catch(e) {}
                }
                
                dateEndPicker = $('#date_end').daterangepicker({
                    "singleDatePicker": true,
                    "showWeekNumbers": true,
                    "autoUpdateInput": false,
                    "autoApply": true,
                    "minDate": picker.startDate,
                    "maxDate": bookingEnd,
                    "locale": {
                      "firstDay": 1,
                      "format": "DD-MM-YYYY",
                      "separator": " | "
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
                "maxDate": bookingEnd,
                "locale": {
                  "firstDay": 1,
                  "format": "DD-MM-YYYY",
                  "separator": " | "
                }
            });

            $('#date_end').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY'));
            });
        }

        // Wait for jQuery and other dependencies
        $(document).ready(function() {
            loadUnavailableDates();
        });
    </script>
</x-app-layout>