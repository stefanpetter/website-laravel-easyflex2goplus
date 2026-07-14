<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-network text-success">
                        </i>
                    </div>
                    <div>Groups
                        <div class="page-title-subheading">Create a new group
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('group.store') }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Name</label>
                        <div class="col-sm-10">
                            <input name="name" id="name" type="text" class="form-control"  autofocus required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                    <div class="position-relative row mb-3"><label for="name" class="form-label col-sm-2 col-form-label">Tourist tax</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <span class="" id="inputGroupPrepend">€</span>
                                </div>
                                <input name="tourist_tax" id="tourist_tax" type="number" step="any" class="form-control">
                            </div>
                            @error('tourist_tax')
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
</x-app-layout>