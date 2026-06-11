<div class="container">
    <section class="admin__title">
        <h5>Update Supplier</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ route('suppliers.index') }}">Supplier List</a></li>
            <li>Edit Customer</li>
            <li class="back-button">
              <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0" href="{{ route('suppliers.index') }}" role="button">
                <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i>
                <span class="ms-1">Back</span>
              </a>
            </li>
          </ul>
    </section>
    <div class="card card-body">
        <div class="card card-plain h-100">
            <div class="card-header pb-0 p-3">
                <div class="row mt-2 justify-content-between">
                     {{-- Supplier Information --}}
                     <div class="col-md-8">
                        <h6 class="badge bg-danger custom_danger_badge">Basic Information</h6>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-3">
                <form wire:submit.prevent="updateSupplier">
                    <div class="row mb-2">
                        <!-- Supplier Details -->
                        <div class="mb-3 col-md-6">
                            <label for="name" class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                {{-- <select wire:model="prefix" class="form-control form-control-sm border border-1" style="max-width: 90px">
                                    <option value="" selected hidden>Prefix</option>
                                    @foreach (App\Helpers\Helper::getNamePrefixes() as $prefix)
                                        <option value="{{$prefix}}">{{ $prefix }}</option>
                                    @endforeach
                                </select> --}}
                               <input type="text" wire:model="name" id="name" class="form-control form-control-sm border border-2 p-2" placeholder="Enter supplier name">
                            </div>
                            {{-- @error('prefix')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror --}}
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" wire:model="email" id="email" class="form-control form-control-sm border border-2 p-2" placeholder="Enter email address">
                            @error('email')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                       <!-- Phone Number -->
                        <div class="mb-2 col-md-3">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <div class="input-group input-group-sm" id="parent_mobile" wire:ignore>
                                <input id="mobile" type="tel" class="form-control tel-code-input"
                                    style="width:286px;" maxlength="8">
                                <!-- hidden Livewire bindings -->
                                <input type="hidden" wire:model="phone_code" id="phone_code">
                                <input type="hidden" wire:model="phone" id="phone">

                            </div>

                            @error('phone')
                            <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                            <div class="form-check-label-group">
                                <input type="checkbox" id="is_whatsapp1" wire:model="isWhatsappPhone">
                                <label for="is_whatsapp1" class="form-check-label ms-1">Is Whatsapp</label>
                            </div>
                        </div>

                        <!-- Alternative Phone Number 1 -->
                        <div class="mb-2 col-md-3">
                            <label for="alt_phone_1" class="form-label">Alternative Phone 1</label>
                            <div class="input-group input-group-sm" id="parent_alt_phone_code_1" wire:ignore>
                                <input id="alt_phone_1" type="tel" class="form-control tel-code-input"
                                    style="width:269px;" maxlength="8">
                                <input type="hidden" wire:model="alt_phone_code_1" id="alt_phone_code_1">
                                <input type="hidden" wire:model="alternative_phone_number_1"
                                    id="alt_phone_hidden_1">
                            </div>
                            @error('alternative_phone_number_1')
                            <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                            <div class="form-check-label-group">
                                <input type="checkbox" id="is_whatsapp2" wire:model="isWhatsappAlt1">
                                <label for="is_whatsapp2" class="form-check-label ms-1">Is Whatsapp</label>
                            </div>
                        </div>

                        <!-- Alternative Phone Number 2 -->
                        <div class="mb-2 col-md-3">
                            <label for="alt_phone_2" class="form-label">Alternative Phone 2</label>
                            <div class="input-group input-group-sm" id="parent_alt_phone_code_2" wire:ignore>
                                <input id="alt_phone_2" type="tel" class="form-control tel-code-input"
                                    style="width:269px;" maxlength="8">
                                <input type="hidden" wire:model="alt_phone_code_2" id="alt_phone_code_2">
                                <input type="hidden" wire:model="alternative_phone_number_2"
                                    id="alt_phone_hidden_2">
                            </div>
                            @error('alternative_phone_number_2')
                                <div class="text-danger error-message">{{ $message }}</div>
                            @enderror
                            <div class="form-check-label-group">
                                <input type="checkbox" id="is_whatsapp3" wire:model="isWhatsappAlt2">
                                <label for="is_whatsapp3" class="form-check-label ms-1">Is Whatsapp</label>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-danger custom_danger_badge">Address Information</h6>
                    </div>
                    <div class="row mb-3">
                        <div class="mb-3 col-md-4">
                            <label for="billing_address" class="form-label">Address <span class="text-danger">*</span></label>
                            <input type="text" wire:model="billing_address" id="billing_address" class="form-control form-control-sm border border-2 p-2" placeholder="Enter billing address">
                            @error('billing_address')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="billing_landmark" class="form-label">Landmark</label>
                            <input type="text" wire:model="billing_landmark" id="billing_landmark" class="form-control form-control-sm border border-2 p-2" placeholder="Enter landmark">
                            @error('billing_landmark')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="billing_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" wire:model="billing_city" id="billing_city" class="form-control form-control-sm border border-2 p-2" placeholder="Enter city">
                            @error('billing_city')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="billing_state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" wire:model="billing_state" id="billing_state" class="form-control form-control-sm border border-2 p-2" placeholder="Enter state">
                            @error('billing_state')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="billing_country" class="form-label">Country <span class="text-danger">*</span></label>
                            <input type="text" wire:model="billing_country" id="billing_country" class="form-control form-control-sm border border-2 p-2" placeholder="Enter country">
                            @error('billing_country')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="billing_pin" class="form-label">Zip Code</label>
                            <input type="text" wire:model="billing_pin" id="billing_pin" class="form-control form-control-sm border border-2 p-2" placeholder="Enter PIN">
                            @error('billing_pin')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- Account Information --}}
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-danger custom_danger_badge">Account Information</h6>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="gst_number" class="form-label">GST Number</label>
                            <input type="text" wire:model="gst_number" id="gst_number" class="form-control form-control-sm border border-2 p-2" placeholder="Enter GST number">
                            @error('gst_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="gst_file" class="form-label">GST File</label>
                            <input type="file" wire:model="gst_file" id="gst_file" class="form-control form-control-sm border border-2 p-2">
                            @if ($this->existingGstFile)
                            <div class="mt-2">
                                <img src="{{ asset($this->existingGstFile) }}" alt="gst Image" class="img-thumbnail" style="max-width: 100px;">
                            </div>
                            @endif
                            @error('gst_file')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="credit_limit" class="form-label">Credit Limit</label>
                            <input type="number" wire:model="credit_limit" id="credit_limit" class="form-control form-control-sm border border-2 p-2" placeholder="Enter credit limit">
                            @error('credit_limit')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="credit_days" class="form-label">Credit Days</label>
                            <input type="number" wire:model="credit_days" id="credit_days" class="form-control form-control-sm border border-2 p-2" placeholder="Enter credit days">
                            @error('credit_days')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success select-md"><i class="material-icons me-1">update</i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.1.6/js/intlTelInput.min.js"></script>
<script>
    let dialCodesCache = null;

    // Load JSON once when page loads
    $.getJSON("{{ asset('assets/js/dial-codes.json') }}", function (data) {
        dialCodesCache = data;

        // Once JSON is loaded, init inputs
        initIntlTelInput("#mobile", "phone", "phone_code");
        initIntlTelInput("#alt_phone_1", "alternative_phone_number_1", "alt_phone_code_1");
        initIntlTelInput("#alt_phone_2", "alternative_phone_number_2", "alt_phone_code_2");
    });

    function loadDialCodes(dialNumber) {
        if (!dialCodesCache) return "cf"; // fallback while JSON not loaded
        return dialCodesCache[dialNumber] || "cf"; // default to cf
    }

    function initIntlTelInput(selector, phoneModel, codeModel) {
        var input = $(selector);
        var codeInput = $("#" + codeModel);
        var phoneInput = $("#" + phoneModel);
        var selected_dial_code = codeInput.val(); // only digits
        var selected_phone_number = phoneInput.val(); // only digits
        var defaultCountry = loadDialCodes(selected_dial_code);
        input.intlTelInput({
            initialCountry: defaultCountry,  // Central African Republic by default
            preferredCountries: ["us", "gb", "in", "cf"],
            separateDialCode: true
        });
        input.val(selected_phone_number);
        // On input change (number typing)
        input.on("input", function () {
            let number = input.val().replace(/\D/g, ''); // only digits
            @this.set(phoneModel, number);
        });

        // On country change
        input.on("countrychange", function () {
            let code = "+" + input.intlTelInput("getSelectedCountryData").dialCode;
            @this.set(codeModel, code);
            @this.call('CountryCodeSet', selector, code);
        });

        @this.set(codeModel, selected_dial_code);
        @this.call('CountryCodeSet', selector, selected_dial_code);
    }

    // Already existing
    window.addEventListener('update_input_max_length', function (event) {
        let itemId = event.detail[0].id;
        let mobile_length = event.detail[0].mobile_length;
        if (itemId && mobile_length) {
            document.querySelector(itemId).setAttribute("maxlength", mobile_length);
        }
    });
</script>
@endpush
