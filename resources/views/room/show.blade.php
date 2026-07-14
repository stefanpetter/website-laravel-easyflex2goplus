<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-exapnd2 text-success">
                        </i>
                    </div>
                    <div>Rooms
                        <div class="page-title-subheading">Show details for room <i>{{ $room->name }}</i>
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
                                        <a href="/rooms/{{ $room->id }}/delete" class="nav-link" onclick="return confirm_delete()">
                                            <i class="nav-link-icon lnr-inbox"></i>
                                            <span>
                                                Delete room
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
                <form method="POST" action="{{ route('room.update', $room->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    @method('PUT')
                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">House</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-house" style="width:100%" id="house" name="house_id">
                            <option value="{{ $room->house->id ?? '' }}">{{ $room->house->name ?? '-' }}</option>
                            </select>
                            @error('house_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input name="name" id="name" type="text" class="form-control" value="{{ $room->name }}">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="floor" class="form-label col-sm-2 col-form-label">Floor</label>
                        <div class="col-sm-10">
                            <select name="floor" id="floor" class="form-control" required>
                                <option value="0" {{ ($room->floor == '0') ? 'selected' : ''}}>Ground floor</option>
                                <option value="1" {{ ($room->floor == '1') ? 'selected' : ''}}>1st floor</option>
                                <option value="2" {{ ($room->floor == '2') ? 'selected' : ''}}>2nd floor</option>
                                <option value="3" {{ ($room->floor == '3') ? 'selected' : ''}}>3rd floor</option>
                                <option value="4" {{ ($room->floor == '4') ? 'selected' : ''}}>4th floor</option>
                            </select>
                            @error('floor')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="size" class="form-label col-sm-2 col-form-label">Size</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input name="size" id="size" type="number" step="any" class="form-control" value="{{ $room->size }}">
                                <div class="input-group-text">
                                    <span class="" id="inputGroupPrepend">m<sup>2</sup></span>
                                </div>
                            </div>
                            @error('size')
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
        <div class="main-card mb-3 card">
            <div class="card-body">
                <h5 class="card-title">Beds</h5>
                <table style="width: 100%;" id="dataTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Occupancy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($room->beds as $bed)
                            <tr>
                                <td>{{ $bed->id }}</td>
                                <td>{{ $bed->name }}</td>
                                <td>
                                    @php
                                        $currentBooking = $bed->bookings->first();
                                    @endphp
                                    @if($currentBooking && $currentBooking->flexworker)
                                        <span class="badge bg-success">{{ $currentBooking->flexworker->name }}</span>
                                    @else
                                        <span class="text-muted">(Available)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tfoot>
                </table>
            </div>

            <script>
                $(document).ready( function () {
                    let table = new DataTable('#dataTable', {
                        columnDefs: [
                            {
                                target: 0,
                                visible: false
                            }
                        ]
                    });

                    $('#dataTable tbody').on('click', 'tr', function() {
                        id = table.row(this).data()[0]
                        window.location.href = "/beds/" + id;
                    })
                });

                function confirm_delete() {
                    return confirm('Are you sure?');
                }
            </script>
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
        });
    </script>
</x-app-layout>