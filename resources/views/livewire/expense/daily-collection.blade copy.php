<div class="container-fluid px-2 px-md-4">
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="card card-body">
        <h4 class="m-0">Add Daily Collection</h4>
        <div class="card card-plain h-100">
            <div class="card-header pb-0 p-3">
                <div class="row">
                    <div class="col-md-8 d-flex align-items-center">
                        <h6 class="badge bg-success">Collection Information</h6>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="#" class="btn btn-cta">
                            <i class="material-icons text-white" style="font-size: 15px;">chevron_left</i> Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <form wire:submit.prevent="submitForm" enctype="multipart/form-data">
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-success">Collection Details</h6>
                    </div>
                    <div class="row mb-3">
                        <div class="mb-3 col-md-4">
                            <label for="collection_at" class="form-label">Collected From</label>
                            <!-- <select wire:model="collection_at" wire:change="onCollectionAtChange" id="collection_at" class="form-control form-control-sm">
                                <option value="" disabled selected>Select One</option>
                                <option value="1">Staff</option>
                                <option value="2">Supplier</option>
                            </select> -->
                            <select wire:model="collection_at" wire:change="onCollectionAtChange" id="expense_at" class="form-control form-control-sm">
                                <option value="" disabled selected>Select One</option>
                                <option value="1">Stuff</option>
                                <option value="2">Supplier</option>
                            </select>
                            @error('collection_at') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        @if($collection_at == '1')
                            <div class="mb-3 col-md-4">
                                <label for="staff_id" class="form-label">Staff Name</label>
                                <select wire:model="staff_id" id="staff_id" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select One</option>
                                    @foreach($stuffOptions as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('staff_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($collection_at == '2')
                            <div class="mb-3 col-md-4">
                                <label for="supplier_id" class="form-label">Supplier Name</label>
                                <select wire:model="supplier_id" id="supplier_id" class="form-control form-control-sm">
                                    <option value="" disabled selected>Select One</option>
                                    @foreach($supplierOptions as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="mb-3 col-md-4">
                            <label for="collection_title" class="form-label">Collection Title</label>
                            <select wire:model="collection_title" id="collection_title" class="form-control form-control-sm">
                                <option value="" disabled selected>Select Collection Title</option>
                                @foreach($collectionTitles as $title)
                                    <option value="{{ $title['id'] }}">{{ $title['title'] }}</option>
                                @endforeach
                            </select>
                            @error('collection_title') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3 col-md-4">
                            <label for="amount" class="form-label">Amount</label>
                            <input wire:model="amount" type="number" id="amount" class="form-control form-control-sm" placeholder="Enter Amount">
                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-8 mb-2 d-flex align-items-center">
                        <h6 class="badge bg-success">Payment Details</h6>
                    </div>
                    <div class="row mb-3">
                        <div class="mb-3 col-md-4">
                            <label for="voucher_no" class="form-label">Voucher No</label>
                            <input wire:model="voucher_no" type="text" id="voucher_no" class="form-control form-control-sm" placeholder="Voucher Number" readonly>
                            @error('voucher_no') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="image" class="form-label">Upload File</label>
                            <input wire:model="image" type="file" id="image" class="form-control form-control-sm">
                            @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3 col-md-4">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea wire:model="remarks" id="remarks" class="form-control form-control-sm" rows="4" placeholder="Enter any remarks here..."></textarea>
                            @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-cta mt-3">Save Collection</button>
                </form>
            </div>
        </div>
    </div>
</div>
