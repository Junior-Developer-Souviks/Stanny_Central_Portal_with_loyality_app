<div class="container">
    <section class="admin__title">
        <h5>Designation Wise Permissions</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{route('staff.designation')}}">Designations</a></li>
            <li>{{$designation_name}}</li>
            <li class="back-button">
                <a class="btn btn-outline-danger select-md" href="{{route('staff.designation')}}" role="button">< Back
                </a>
            </li>
        </ul>
    </section>
    
    <div class="card my-2">
        <div class="card-header pb-0">
            <div class="row">
                @if(session()->has('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif
                <div class="row">
                    @foreach($allPermissions as $parentName => $permissions)
                        <div class="col-12">
                            <h6 class="text-uppercase font-weight-bold">{{ ucfirst(str_replace('_', ' ', $parentName)) }}</h6> <!-- Parent Name -->
                        </div>
                
                            @foreach($permissions as $permission)
                                <div class="col-md-3"> <!-- Each checkbox in a col-3 -->
                                    <label class="cursor-pointer">
                                        <input type="checkbox" wire:model.defer="permissions" value="{{ $permission['id'] }}" wire:change="updatePermissions">
                                        {{ ucfirst(str_replace('_', ' ', $permission['name'])) }}
                                    </label>
                                </div>
                            @endforeach
                    
                
                        <div class="col-12">
                            <hr> <!-- Separator after each parent group -->
                        </div>
                    @endforeach
                </div>
                
                

                {{-- <button wire:click="updatePermissions" class="btn btn-primary mt-2">Update Permissions</button> --}}
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>
