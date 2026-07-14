<x-app-layout>
<div class="app-main__inner">
                        <div class="app-page-title">
                            <div class="page-title-wrapper">
                                <div class="page-title-heading">
                                    <div class="page-title-icon">
                                        <i class="pe-7s-network icon-gradient bg-tempting-azure"></i>
                                    </div>
                                    <div>
                                        Groups
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
                                                    <a href="/groups/create" class="nav-link">
                                                        <i class="nav-link-icon lnr-inbox"></i>
                                                        <span>
                                                            Create group
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
                        <div class="main-card mb-3 card">
                            <div class="card-body">

                                <table style="width: 100%;" id="dataTable" class="table table-hover table-striped table-bordered">
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
                                console.log('clicked: ' + id)
                                window.location.href = "/groups/" + id;
                            })
                        });
                    </script>
                    </x-app-layout>