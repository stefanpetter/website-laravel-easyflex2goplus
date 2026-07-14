<x-app-layout>
    <div class="app-main__inner">
        <div class="app-page-title">
            <div class="page-title-wrapper">
                <div class="page-title-heading">
                    <div class="page-title-icon">
                        <i class="pe-7s-upload text-success">
                        </i>
                    </div>
                    <div>Beds
                        <div class="page-title-subheading">Create a new bed
                        </div>
                    </div>
                </div>
            </div>
        </div>            
        <div class="main-card mb-3 card">
            <div class="card-body"><h5 class="card-title">Details</h5>
                <form method="POST" action="{{ route('bed.store') }}" class='{!! ($errors->any()) ? "was-validated" : "needs-validation" !!}'>
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

                    <div class="position-relative row mb-3"><label for="group_id" class="form-label col-sm-2 col-form-label">Room</label>
                        <div class="col-sm-10">
                            <select class="form-control js-data-room" style="width:100%" id="room" name="room_id">
                            <option value="">-</option>
                            </select>
                            @error('house_id')
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
            $('.js-data-room').select2({
                theme: "bootstrap",
                ajax: {
                    url: "/api/rooms",
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