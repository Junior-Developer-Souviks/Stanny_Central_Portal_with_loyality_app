<div class="container">
    <section class="admin__title">
        <h5>Branch</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Branch</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <!-- <p class="text-sm font-weight-bold">Items</p> -->
            </div>
        </div>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model="search" class="form-control select-md bg-white search-input"
                                id="customer" placeholder="Search here" value="" style="width: 350px;"
                                wire:keyup="FindBranch($event.target.value)">
                        </div>
                        <div class="col-auto mt-3">
                            <button type="button" wire:click="resetFields"
                                class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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
                                                Name</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Country</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Email</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Mobile</th>
                                            <th
                                                class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($branchNames && $branchNames->count())
                                        @foreach($branchNames as $k => $branchName)
                                        <tr>
                                            <td>
                                                <h6 class="mb-0 text-sm">{{ $k + 1 }}</h6>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $branchName->name }}</p>
                                            </td>
                                            <td>
                                                @if (!empty($branchName->country))
                                                    <p class="text-xs font-weight-bold mb-0">{{ $branchName->country ? $branchName->country->title : ""}}</p>
                                                @else
                                                    <p class="text-xs font-weight-bold mb-0">N/A</p>
                                                @endif
                                              
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $branchName->email }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $branchName->mobile }}</p>
                                            </td>
                                            <td class="align-middle">
                                                <button wire:click="edit({{ $branchName->id }})"
                                                    class="btn btn-outline-primary select-md btn_action btn_outline"
                                                    title="Edit">Edit
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="2">No branches found</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <nav aria-label="Page navigation">
                                        {{ $branchNames->links() }}
                                    </nav>
                                </div>
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
                            <div class="d-flex justify-content-between mb-3">
                                <h5>{{$branchId ? "Update Branch" : "Create Branch"}}</h5>
                            </div>
                            <form wire:submit.prevent="{{ $branchId ? 'updateBranch' : 'storeBranch' }}">
                                <div class="row">
                                    <label class="form-label"> Branch Name</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model="name"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter Branch Name">
                                    </div>
                                    @error('name')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                   <label class="form-label mt-3">Country</label>

                                        <div wire:ignore>
                                            <select id="countrySelect" class="form-select form-select-sm border border-2 p-2" wire:model="country_id">
                                                <option value="">Select Country</option>
                                                @foreach($countries as $country)
                                                    <option value="{{ $country->id }}"
                                                     >
                                                        {{ $country->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        @error('country_id')
                                        <p class='text-danger inputerror'>{{ $message }}</p>
                                        @enderror

                                    <!-- Email -->
                                    <label class="form-label mt-3">Email</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="email" wire:model="email" autocomplete="new-email"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter Email">
                                    </div>
                                    @error('email')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <!-- Mobile -->
                                    <label class="form-label mt-3">Mobile</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="number" wire:model="mobile"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter Mobile Number">
                                    </div>
                                    @error('mobile')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <!-- WhatsApp -->
                                    <label class="form-label mt-3">WhatsApp</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="number" wire:model="whatsapp"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter WhatsApp Number">
                                    </div>
                                    @error('whatsapp')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <!-- City -->
                                    <label class="form-label mt-3">City</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <input type="text" wire:model="city" autocomplete="new-city"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter City">
                                    </div>
                                    @error('city')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror

                                    <!-- Address -->
                                    <label class="form-label mt-3">Address</label>
                                    <div class="ms-md-auto pe-md-3 d-flex align-items-center mb-2">
                                        <textarea type="text" wire:model="address" autocomplete="new-address"
                                            class="form-control form-control-sm border border-2 p-2"
                                            placeholder="Enter Address"></textarea>
                                    </div>
                                    @error('address')
                                    <p class='text-danger inputerror'>{{ $message }}</p>
                                    @enderror
                                    <div class="mb-2 text-end mt-4">
                                        @if($branchId)
                                        <a href="javascript:void(0);" class="btn btn-sm btn-danger select-md"
                                            wire:click.prevent="resetFields">
                                            Clear
                                        </a>
                                        @endif
                                        <button type="submit" class="btn btn-sm btn-success select-md"
                                            wire:loading.attr="disabled">
                                            <span>{{ $branchId ? 'Update' : 'Create' }}</span>
                                        </button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<!-- Chosen CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css"/>

<!-- jQuery + Chosen JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
  {{-- <script>
    function initChosen() {
        $('.chosen-select').chosen({
            width: '100%',
            no_results_text: "No result found"
        }).off('change').on('change', function (e) {
            let model = $(this).attr('wire:model');
            if (model) {
                @this.set(model, $(this).val());
            }
        });
    }

    document.addEventListener("livewire:navigated", () => {
        initChosen();
    });

    Livewire.hook('morph.updated', ({ el, component }) => {
        initChosen();
    });

    $(document).ready(function () {
        initChosen();
    });

    
</script> --}}

<script>
    var jq = $.noConflict();

    function initChosen() {

        let select = jq("#countrySelect");

        // Destroy if already initialized
        if (select.data('chosen')) {
            select.chosen("destroy");
        }

        select.chosen({
            width: "100%",
            no_results_text: "No result found"
        });

        // JS → Livewire
        select.off('change').on('change', function () {
            @this.set('country_id', jq(this).val());
        });

        // 🔥 Set initial value (for Create page)
        let initialValue = @this.get('country_id');
        if (initialValue) {
            select.val(initialValue).trigger("chosen:updated");
        }
    }

    // First page load
    document.addEventListener("DOMContentLoaded", function () {
        initChosen();
    });

    // After every Livewire update
    document.addEventListener("livewire:navigated", function () {
        initChosen();
    });

   
    Livewire.on('refresh-chosen', (event) => {
        let select = jq("#countrySelect");
        select.val(event.country).trigger("chosen:updated");
    });

</script>



@endpush