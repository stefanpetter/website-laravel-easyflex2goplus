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
                        <div class="page-title-subheading">Create a new house
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('house.store') }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">Group</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-group" style="width:100%" id="location" name="group_id">
                            <option value="">-</option>
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
                            <input name="name" id="name" type="text" class="form-control">
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
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
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
                            <input name="snf_beds" id="name" type="number" class="form-control">
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
                                <option value="available">Available</option>
                                <option value="maintenance">Maintenance</option>
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
                                <input name="price" id="price" type="number" min="1" step="any" class="form-control">
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
                            <input name="grootboek_nr" id="grootboek_nr" type="text" class="form-control">
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
                                <input name="gbo" id="gbo" type="number" step="any" class="form-control">
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
</x-app-layout>