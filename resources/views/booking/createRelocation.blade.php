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
                        <div class="page-title-subheading">Relocate a flexworker
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('booking.relocation.store', $booking->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
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
                    <div class="position-relative row mb-3"><label for="date_start" class="form-label col-sm-2 col-form-label">Date of relocation</label>
                        <div class="col-sm-10">
                            <input name="date_start" id="date_start" type="text" class="form-control" required>
                            @error('date_start')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">New bed</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-bed" style="width:100%" id="bed_id" name="bed_id" disabled>
                                <option value="0">-</option>
                            </select>
                            @error('bed_id')
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
        var unavailableDates = [];
        var dateStartPicker = null;
        var selectedBedId = null;

        function isDateDisabled(date) {
            if (unavailableDates.length === 0) return false;
            
            var checkDate = moment(date);
            
            // For relocation start date, check if the date is within any booking on the NEW bed
            for (var i = 0; i < unavailableDates.length; i++) {
                var start = moment(unavailableDates[i].start, 'DD-MM-YYYY');
                var end = moment(unavailableDates[i].end, 'DD-MM-YYYY');
                
                if (checkDate.isBetween(start, end, null, '[]')) {
                    return true;
                }
            }
            return false;
        }

        function loadUnavailableDatesForBed(bedId) {
            if (!bedId || bedId == '0') {
                unavailableDates = [];
                if (dateStartPicker) {
                    try { $('#date_start').data('daterangepicker').remove(); } catch(e) {}
                }
                initializeDatePicker();
                return;
            }

            selectedBedId = bedId;

            $.ajax({
                url: '/api/beds/' + bedId + '/unavailable-dates',
                method: 'GET',
                success: function(response) {
                    unavailableDates = response;
                    
                    if (dateStartPicker) {
                        try { $('#date_start').data('daterangepicker').remove(); } catch(e) {}
                    }
                    
                    initializeDatePicker();
                },
                error: function() {
                    console.error('Failed to load unavailable dates');
                }
            });
        }

        function initializeDatePicker() {
            // Relocation date must be after current booking start and respect new bed availability
            var originalBookingStart = moment('{{ $booking->date_start->format("d-m-Y") }}', 'DD-MM-YYYY');
            var originalBookingEnd = {!! $booking->date_end ? "moment('".$booking->date_end->format('d-m-Y')."', 'DD-MM-YYYY')" : "moment().add(10, 'years')" !!};

            dateStartPicker = $('#date_start').daterangepicker({
                "singleDatePicker": true,
                "showWeekNumbers": true,
                "autoUpdateInput": false,
                "autoApply": true,
                "minDate": moment().add(1, 'day'), // At least tomorrow
                "maxDate": originalBookingEnd,
                "locale": {
                  "firstDay": 1,
                  "format": "DD-MM-YYYY",
                  "separator": " | "
                },
                isInvalidDate: function(date) {
                    // Check if new bed is available on this date AND for the remaining period
                    return isDateDisabled(date);
                }
            });

            $('#date_start').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY'));
                $('#bed_id').removeAttr("disabled");
            });
        }

        $(document).ready(function() {

            $('.js-data-bed').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/beds",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                    return {q: params.term, date_start: $('#date_start').val(), date_end: '{{ $booking->date_end ? $booking->date_end->format("d-m-Y") : "" }}'};
                    },
                    processResults: function (data) {
                    return {results: data};
                    },
                }
            });

            // Load unavailable dates when a new bed is selected
            $('.js-data-bed').on('select2:select', function(e) {
                var bedId = e.params.data.id;
                loadUnavailableDatesForBed(bedId);
            });

            // Initialize with basic date picker (will be enhanced when bed is selected)
            initializeDatePicker();
        });
    </script>
</x-app-layout>