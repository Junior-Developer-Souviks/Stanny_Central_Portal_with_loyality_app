<div class="container">
    <section class="admin__title">
        <h5>{{ $payment_voucher_no ? 'Edit Payment Receipt' : 'Add Payment Receipt' }}</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li><a href="{{ route('admin.accounting.payment_collection') }}">Payment Collection</a></li>
            <li>{{ $payment_voucher_no ? 'Edit Payment Receipt' : 'Add Payment Receipt' }}</li>
            <li class="back-button">
                <a class="btn btn-sm btn-danger select-md text-light font-weight-bold mb-0"
                    href="{{ route('admin.accounting.payment_collection') }}" role="button">
                    < Back </a>
            </li>
        </ul>
    </section>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <form wire:submit.prevent="editReceipt">
                        @if (session()->has('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif



                            <div class="row" id="noncash_sec">

                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label for="">Cheque No / UTR No </label>
                                        <input type="text" value="" wire:model="chq_utr_no"
                                            class="form-control form-control-sm" maxlength="100" {{ $readonly }}>
                                        @if (isset($errorMessage['chq_utr_no']))
                                            <div class="text-danger">{{ $errorMessage['chq_utr_no'] }}</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group mb-3">
                                        <label for="">Bank Name </label>
                                        <div id="bank_search">
                                            <input type="text" id="" placeholder="Search Bank"
                                                wire:model="bank_name" value=""
                                                class="form-control form-control-sm bank_name" maxlength="200"
                                                {{ $readonly }}>
                                            @if (isset($errorMessage['bank_name']))
                                                <div class="text-danger">{{ $errorMessage['bank_name'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>



                        @if ($activePayementMode == 'cheque')
                            <div class="row">

                                <div class="col-sm-4">
                                    <div class="form-group mb-3">
                                        <label for="">Amount Credit Date </label>
                                        <input type="date" wire:model="credit_date" id="credit_date"
                                            class="form-control form-control-sm">
                                        @error('credit_date')
                                            <div class="text-danger">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                @if (!empty($payment_data->cheque_photo))
                                     <div class="form-group mb-3">
                                    <img src="{{ asset($payment_data->cheque_photo) }}" class="img-fluid rounded" alt="Uploaded Image" >
                                </div>
                                @endif


                            </div>
                        @endif
                        <div class="row">
                            <div class="form-group text-end">
                                <button type="submit" id="submit_btn"
                                    class="btn btn-sm btn-success select-md">{{ $payment_voucher_no
                                        ? 'Update Receipt'
                                        : "Add
                                                                                                                                                Receipt" }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>
@push('js')
    <script>
        function validateNumber(input) {
            // Remove any characters that are not digits or a single decimal point
            input.value = input.value.replace(/[^0-9.]/g, '');

            // Ensure only one decimal point is allowed
            const parts = input.value.split('.');
            if (parts.length > 2) {
                input.value = parts[0] + '.' + parts[1];
            }
        }
    </script>
@endpush
