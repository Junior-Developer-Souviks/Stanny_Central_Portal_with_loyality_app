<div class="container">
    <section class="admin__title">
        <h5>Country</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Country</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>

    </section>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="row">
                <div class="col-12">
                    <div class="card my-4">
                        <div class="card-header pb-0">
                            <div class="row">
                                <div class="col-md-6">
                                     <div class="position-relative">
                                    <input type="text" class="form-control" placeholder="Search by country name..."
                                        wire:model="search" wire:keyup="FindCountry($event.target.value)">
                                       
                                          @if($search)
                                            <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 16px;"
                                                wire:click="resetSearch">
                                                ❌
                                            </span>
                                        @endif
                                     </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    @if(session()->has('message'))
                                    <div class="alert alert-success text-center" id="flashMessage">
                                        {{ session('message') }}
                                    </div>
                                    @elseif(session()->has('error'))
                                    <div class="alert alert-danger" id="flashMessage">
                                        {{ session('error') }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    SL</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    Country</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    Country Code</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    Mobile Length</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    Status</th>
                                                <th
                                                    class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($country as $index => $countries)
                                            <tr>
                                                <td>
                                                    <h6 class="mb-0 text-sm">{{ $index + 1 }}</h6>
                                                </td>

                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $countries->title }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $countries->country_code
                                                        }}</p>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{
                                                        $countries->mobile_length }}</p>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input ms-auto" type="checkbox"
                                                            wire:click="toggleStatus({{ $countries->id }})"
                                                            @if($countries->status)
                                                        checked
                                                        @endif>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button
                                                        class="btn btn-outline-primary select-md btn_action btn_outline"
                                                        wire:click="editCountry({{ $countries->id }})">Edit</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-4">
                                    {{ $country->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card my-4">
                <div class="card-header pb-0">
                    <h6>Edit Country</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Country Name</label>
                        <input type="text" class="form-control" wire:model="title">
                        @error('title')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Country Code</label>
                        <input type="text" class="form-control" wire:model="country_code">
                        @error('country_code')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label>Mobile Length</label>
                        <input type="number" class="form-control" wire:model="mobile_length">
                        @error('mobile_length')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    @if ($isEditMode)
                      <button class="btn btn-success btn-sm" wire:click="updateCountry">Update</button>
                    @else
                      <button class="btn btn-success btn-sm" disabled>Select a country to edit</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>