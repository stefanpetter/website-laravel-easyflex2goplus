<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-home text-success">
                        </i>
                    </div>
                    <div>Houses
                        <div class="page-title-subheading">Show details for house <i>{{ $house->name }}</i>
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
                                        <a href="/houses/{{ $house->id }}/delete" class="nav-link" onclick="return confirm_delete()">
                                            <i class="nav-link-icon lnr-inbox"></i>
                                            <span>
                                                Delete house
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
                <form method="POST" action="{{ route('house.update', $house->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    @method('PUT')
                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">Group</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-group" style="width:100%" id="location" name="group_id">
                            <option value="{{ $house->group->id ?? '' }}">{{ $house->group->name ?? '-' }}</option>
                            </select>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input name="name" id="name" type="text" class="form-control" value="{{ $house->name }}">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="status" class="form-label col-sm-2 col-form-label">Status</label>
                        <div class="col-sm-10">
                            <select name="status" id="status" class="form-control" required>
                                <option value="available" {{ ($house->status == 'available') ? 'selected' : ''}}>Available</option>
                                <option value="unavailable" {{ ($house->status == 'unavailable') ? 'selected' : ''}}>Unavailable</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="snf_beds" class="form-label col-sm-2 col-form-label">SNF beds</label>
                        <div class="col-sm-10">
                            <input name="snf_beds" id="snf_beds" type="number" class="form-control" value="{{ $house->snf_beds }}">
                            @error('snf_beds')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="snf_status" class="form-label col-sm-2 col-form-label">SNF status</label>
                        <div class="col-sm-10">
                            <select name="snf_status" id="snf_status" class="form-control" required>
                                <option value="available" {{ ($house->snf_status == 'available') ? 'selected' : ''}}>Available</option>
                                <option value="maintenance" {{ ($house->snf_status == 'maintenance') ? 'selected' : ''}}>Maintenance</option>
                            </select>
                            @error('snf_status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Price per week</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="" id="inputGroupPrepend">€</span>
                                </div>
                                <input name="price" id="price" type="number" min="1" step="any" class="form-control" value="{{ $house->price }}">
                            </div>
                            @error('price')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="grootboek_nr" class="form-label col-sm-2 col-form-label">Kostenplaats</label>
                        <div class="col-sm-10">
                            <input name="grootboek_nr" id="grootboek_nr" type="text" class="form-control" value="{{ $house->grootboek_nr }}">
                            @error('grootboek_nr')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="gbo" class="form-label col-sm-2 col-form-label">GBO</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input name="gbo" id="gbo" type="number" step="any" class="form-control" value="{{ $house->gbo }}">
                                <div class="input-group-text">
                                    <span class="" id="inputGroupPrepend">m<sup>2</sup></span>
                                </div>
                            </div>
                            @error('gbo')
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
                <h5 class="card-title">Rooms</h5>
                <table style="width: 100%;" id="dataTable" class="table table-hover table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Beds & Occupancy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($house->rooms as $room)
                            <tr>
                                <td>{{ $room->id }}</td>
                                <td>{{ $room->name }}</td>
                                <td>
                                    @foreach($room->beds as $bed)
                                        <div class="mb-1">
                                            <span class="badge bg-secondary">{{ $bed->name }}</span>
                                            @php
                                                $currentBooking = $bed->bookings->first();
                                            @endphp
                                            @if($currentBooking && $currentBooking->flexworker)
                                                <i class="fa fa-arrow-right mx-1"></i>
                                                <span class="badge bg-success">{{ $currentBooking->flexworker->name }}</span>
                                            @else
                                                <span class="text-muted ms-2">(Available)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tfoot>
                </table>
            </div>

            <script src="/assets/js/select2.min.js"></script>

            <script>
                $(document).ready(function() {
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
                });
    </script>

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
                        window.location.href = "/rooms/" + id;
                    })
                });

                function confirm_delete() {
                    return confirm('Are you sure?');
                }
            </script>
        </div>
    </div>
</x-app-layout>