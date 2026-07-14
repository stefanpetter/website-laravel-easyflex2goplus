<x-app-layout>
<div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-search icon-gradient bg-tempting-azure"></i>
                    </div>
                    <div>
                        Search
                        <div class="page-title-subheading">Found {{ $request_count }} results for "{{ request('q') }}"</div>
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
                                    <a href="/rooms/create" class="nav-link">
                                        <i class="nav-link-icon lnr-inbox"></i>
                                        <span>
                                            Create room
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
        @if(count($groups) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Groups</h5>

                    <table style="width: 100%;" id="group_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groups as $group)
                                <tr>
                                    <td>{{ $group->id }}</td>
                                    <td>{{ $group->name }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        @if(count($houses) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Houses</h5>

                    <table style="width: 100%;" id="house_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($houses as $house)
                                <tr>
                                    <td>{{ $house->id }}</td>
                                    <td>{{ $house->name }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        @if(count($rooms) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Rooms</h5>

                    <table style="width: 100%;" id="room_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>House</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rooms as $room)
                                <tr>
                                    <td>{{ $room->id }}</td>
                                    <td>{{ $room->name }}</td>
                                    <td>{{ $room->house->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        @if(count($beds) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Beds</h5>

                    <table style="width: 100%;" id="bed_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Room</th>
                                <th>House</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($beds as $bed)
                                <tr>
                                    <td>{{ $bed->id }}</td>
                                    <td>{{ $bed->name }}</td>
                                    <td>{{ $bed->room->name ?? '-' }}</td>
                                    <td>{{ $bed->room->house->name ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        @if(count($flexworkers) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Flexworkers</h5>

                    <table style="width: 100%;" id="flexworker_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>RelationID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Nationality</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($flexworkers as $flexworker)
                                <tr>
                                    <td>{{ $flexworker->id }}</td>
                                    <td>{{ $flexworker->relation_id ?? '-' }}</td>
                                    <td>{{ $flexworker->name }}</td>
                                    <td>{{ ucfirst($flexworker->gender) }}</td>
                                    <td>{{ ucfirst($flexworker->nationality) }}</td>
                                    <td>{{ ucfirst($flexworker->status) }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
        @if(count($users) > 0)
            <div class="main-card mb-3 card">
                <div class="card-body"><h5 class="card-title">Users</h5>

                    <table style="width: 100%;" id="user_dataTable" class="table table-hover table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                            @endforeach
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

    </div>

    <script>
        $(document).ready( function () {
            let group_table = new DataTable('#group_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#group_dataTable tbody').on('click', 'tr', function() {
                id = group_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/groups/" + id;
            })

            let house_table = new DataTable('#house_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#house_dataTable tbody').on('click', 'tr', function() {
                id = house_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/houses/" + id;
            })

            let room_table = new DataTable('#room_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#room_dataTable tbody').on('click', 'tr', function() {
                id = room_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/groups/" + id;
            })

            let bed_table = new DataTable('#bed_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#bed_dataTable tbody').on('click', 'tr', function() {
                id = bed_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/beds/" + id;
            })

            let flexworker_table = new DataTable('#flexworker_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#flexworker_dataTable tbody').on('click', 'tr', function() {
                id = flexworker_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/flexworkers/" + id;
            })

            let user_table = new DataTable('#user_dataTable', {
                columnDefs: [
                    {
                        target: 0,
                        visible: false
                    }
                ],
                order: [
                    [2, 'asc']
                ]
            });

            $('#user_dataTable tbody').on('click', 'tr', function() {
                id = user_table.row(this).data()[0]
                console.log('clicked: ' + id)
                window.location.href = "/users/" + id;
            })
        });
    </script>
</x-app-layout>