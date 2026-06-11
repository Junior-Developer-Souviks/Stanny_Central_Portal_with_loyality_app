<div class="row mb-4">
    <div class="col-lg-8 col-md-6 mb-md-0 mb-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header pb-0">
                        <div class="row">
                            @if(session()->has('message'))
                                <div class="alert alert-success" id="flashMessage">
                                    {{ session('message') }}
                                </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-7">
                                <h6>Measurement</h6>
                            </div>
                            <div class="col-lg-6 col-5 my-auto text-end">
                                <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model.debounce.500ms="search" class="form-control border border-2 p-2 custom-input-sm" placeholder="Enter Title">
                                        <button type="button" wire:target="search" class="btn btn-dark text-light mb-0 custom-input-sm">
                                            <span class="material-icons">search</span>
                                        </button>
                                    </div>
                                        <!-- Optionally, add a search icon button -->
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle">
                                            SL</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle">
                                            Title</th>
                                        <!-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle"> -->
                                            <!-- measurement</th> -->
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle">
                                            Short Code</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle">
                                            Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 align-middle px-4">
                                            Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($measurements as $k => $measurement)
                                        <tr>
                                            <td class="align-middle text-center">{{ $k + 1 }}</td>
                                            <td class="align-middle text-center">{{ $measurement->title }}</td>
                                            <td class="align-middle text-center">{{ $measurement->shortcode }}</td>
                                            
                                           
                                            <td class="align-middle text-sm" style="text-align: center;">
                                                <div class="form-check form-switch">
                                                    <input 
                                                        class="form-check-input ms-auto" 
                                                        type="checkbox" 
                                                        id="flexSwitchCheckDefault{{ $measurement->id }}" 
                                                        wire:click="toggleStatus({{ $measurement->id }})"
                                                        @if($measurement->status) checked @endif
                                                    >
                                                </div>
                                            </td>
                                            <td class="align-middle text-end px-4">
                                                <a class="button" href="{{ route('measurements.index',$measurement->id) }}" class="btn btn-outline-info btn-sm custom-btn-sm">Measurment</a>
                                                <button wire:click="edit({{ $measurement->id }})" class="btn btn-outline-info btn-sm custom-btn-sm">Edit</button>
                                                <button wire:click="destroy({{ $measurement->id }})" class="btn btn-outline-danger btn-sm custom-btn-sm">Delete</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 col-md-6 mb-md-0 mb-4">
        <div class="row">
            <div class="col-12">             
                <div class="card my-4">       
                    <div class="card-body px-0 pb-2 mx-4">
                        @if ($measurements->isEmpty())
                            <div class="d-flex justify-content-between mb-3">
                                <h5>Create measurement</h5>  
                            </div>
                        @endif   
                         <form wire:submit.prevent="{{ $measurementId ? 'update' : 'store' }}">
                            <div class="form-group">
                                <label>
                                    Category
                                    <a href="{{ route('admin.measurements') }}" class="badge bg-secondary text-decoration-none">
                                        measurements
                                    </a>
                                </label>
                               
                            </div>
                            <div class="form-group">
                                <label>Measurement Title</label>
                                <input type="text" wire:model="title" class="form-control border border-2 p-2" placeholder="Enter Title">
                                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Short Code</label>
                                <input type="text" wire:model="short_code" class="form-control border border-2 p-2" placeholder="Enter Title">
                                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary mt-3">
                                {{ $measurementId ? 'Update measurement' : 'Create measurement' }}
                            </button>
                         </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('script')
<script>
    document.addEventListener('livewire:update', function () {
    let el = document.getElementById('sortable');
    new Sortable(el, {
        handle: '.handle',
        onEnd: function (evt) {
            let positions = [];
            document.querySelectorAll('#sortable tr').forEach((row) => {
                positions.push(row.getAttribute('data-id'));
            });
            @this.updatePosition(positions);
        },
    });
});
</script>
@endpush