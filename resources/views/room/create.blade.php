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
                        <div class="page-title-subheading">Create a new room</div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('room.store') }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
                    @csrf
                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">House</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-house" style="width:100%" id="house" name="house_id">
                            <option value="">-</option>
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
                            <input name="name" id="name" type="text" class="form-control"  autofocus required>
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
                                <option value="0">Ground floor</option>
                                <option value="1">1st floor</option>
                                <option value="2">2nd floor</option>
                                <option value="3">3rd floor</option>
                                <option value="4">4th floor</option>
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
                                <input name="size" id="size" type="number" step="any" class="form-control">
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