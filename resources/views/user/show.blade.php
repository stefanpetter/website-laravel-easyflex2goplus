<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-users text-success">
                        </i>
                    </div>
                    <div>Users
                        <div class="page-title-subheading">Show details for user <i>{{ $user->name }}</i>
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
                                        <a href="/users/{{ $user->id }}/delete" class="nav-link" onclick="return confirm_delete()">
                                            <i class="nav-link-icon lnr-inbox"></i>
                                            <span>
                                                Delete user
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
                <form method="POST" action="{{ route('user.update', $user->id) }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    @method('PUT')

                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input name="name" id="name" type="text" class="form-control" value="{{ $user->name }}">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="email" class="form-label col-sm-2 col-form-label">Email</label>
                        <div class="col-sm-10">
                            <input name="email" id="email" type="text" class="form-control" value="{{ $user->email }}">
                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3"><label for="password" class="form-label col-sm-2 col-form-label">New password</label>
                        <div class="col-sm-10">
                            <input name="password" id="password" type="password" class="form-control">
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="position-relative row mb-3">
                        <label for="checkbox2" class="form-label col-sm-2 col-form-label">Is Admin</label>
                        <div class="col-sm-10">
                            <div class="position-relative form-check">
                                <label class="form-check-label">
                                    <input id="is_admin" name="is_admin" type="checkbox" class="form-check-input" {{ $user->is_admin ? 'checked' : '' }}>
                                </label>
                            </div>
                        </div>
                    </div>


                    <div class="position-relative row mb-3"><label for="exampleEmail" class="form-label col-sm-2 col-form-label"></label>
                        <div class="col-sm-10"><button class="btn btn-primary">Save</button></div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>