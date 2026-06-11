<div class="container" style="min-height: 600px;">
    <section class="admin__title">
        <h5>User Ledger</h5>
    </section>

    <section>
        <ul class="breadcrumb_menu">
            <li>Report</li>
            <li><a href="{{route('admin.report.user_ledger')}}">User Ledger</a></li>
            <li class="back-button"></li>
        </ul>
    </section>
    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        {{-- Search by Date Range --}}
                        <div class="col-auto mt-0">
                            <label class="form-label"><strong>From Date</strong></label>
                            <input type="date" wire:change="updateFromDate($event.target.value)" wire:model="from_date" wire:key="from_date" @if(!empty($is_opening_bal)) min="{{$opening_bal_date}}" @endif  max="{{ $to_date }}"class="form-control select-md bg-white" placeholder="From Date">
                        </div>
                        <div class="col-auto mt-0">
                        <label class="form-label"><strong>To Date</strong></label>
                            <input type="date"wire:change="updateToDate($event.target.value)" wire:model="to_date" wire:key="to_date" class="form-control select-md bg-white" placeholder="To Date">
                        </div>

                        {{-- User Type Dropdown --}}
                        <div class="col-auto mt-0">
                            <label class="form-label"><strong>User Type</strong></label>
                            <select wire:change="getUser($event.target.value)" wire:model="user_type" wire:key="user_type" class="form-control select-md bg-white">
                                <option value="" hidden selected>Select User Type</option>
                                <option value="staff">Staff</option>
                                <option value="customer">Customer</option>
                                <option value="supplier">Supplier</option>
                            </select>

                        </div>

                        {{-- User Name Dropdown (Changes Based on User Type Selection) --}}
                        <div class="col-auto mt-0">
                            @if($user_type === 'staff')  {{-- Staff --}}
                                <label class="form-label"><strong>Search Staff</strong></label>
                                <input type="text" wire:model.defer="staffSearchTerm"
                                    wire:keyup="searchStaff" class="form-control form-control-sm bg-white"
                                    placeholder="Search by staff name" id="searchStaff">
                                    @if(isset($errorMessage['staff']))
                                        <div class="text-danger text-sm">{{ $errorMessage['staff'] }}</div>
                                     @endif
                                @if(!empty($staffSearchResults))
                                <div class="dropdown-menu show" style="max-height: 200px; overflow-y: auto;">
                                    @foreach ($staffSearchResults as $staff)
                                    <button class="dropdown-item" type="button" 
                                        wire:click="selectStaff({{ $staff->id }})">
                                        {{ ucwords($staff->name) }}
                                    </button>
                                    @endforeach
                                </div>
                                @endif

                            @elseif($user_type === 'customer') {{-- Customer --}}
                                <label class="form-label"><strong>Customer</strong></label>
                                <input type="text" wire:model.defer="customerSearchTerm"
                                    wire:keyup="searchCustomer" class="form-control form-control-sm bg-white"
                                    placeholder="Search by customer name" id="searchCustomer">
                                    @if(isset($errorMessage['customer']))
                                        <div class="text-danger text-sm">{{ $errorMessage['customer'] }}</div>
                                     @endif
                                @if(!empty($customerSearchResults))
                                    <div class="dropdown-menu show" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($customerSearchResults as $customer)
                                        <button class="dropdown-item" type="button" 
                                            wire:click="selectCustomers({{ $customer->id }})">
                                            {{ ucwords($customer->name) }}
                                        </button>
                                        @endforeach
                                    </div>
                                @endif
                            @elseif($user_type === 'supplier') {{-- Supplier --}}
                                <label class="form-label"><strong>Search Supplier</strong></label>
                                <input type="text" wire:model.defer="supplierSearchTerm"
                                    wire:keyup="searchSupplier" class="form-control form-control-sm bg-white"
                                    placeholder="Search by supplier name" id="searchSupplier">
                                    @if(isset($errorMessage['supplier']))
                                        <div class="text-danger text-sm">{{ $errorMessage['supplier'] }}</div>
                                     @endif
                                @if(!empty($supplierSearchResults))
                                    <div class="dropdown-menu show" style="max-height: 200px; overflow-y: auto;">
                                        @foreach ($supplierSearchResults as $supplier)
                                            <button class="dropdown-item" type="button" 
                                                wire:click="selectSupplier({{ $supplier->id }})">
                                                {{ ucwords($supplier->name) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Payment Type Dropdown --}}
                        <div class="col-auto mt-0">
                            <label class="form-label"><strong>Bank/Cash</strong></label>
                            <select wire:change="PaymentMode($event.target.value)" wire:key="bank_cash"  wire:model="bank_cash" class="form-control select-md bg-white">
                                <option value="" hidden selected> Mode</option>
                                <option value=""> All</option>
                                <option value="bank">Bank</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>

                        {{-- Reset Button --}}
                        <div class="col-auto mt-5">
                            <!-- <label class="form-label"><strong></strong></label> -->
                            <button type="button"  wire:click="resetForm" class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </section>
    <section>
        
    </section>
    @if(count($ledgerData) > 0)
        <div class="filter">
            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="col-auto">
                    <button wire:click="exportLedger" class="btn btn-outline-success select-md">
                        <i class="fas fa-file-export"></i>Download CSV
                    </button>
                    <button wire:click="generatePDF" class="btn btn-outline-success select-md">
                        <i class="fas fa-file-pdf"> </i> Download Pdf
                    </button>
                    </div>

                    <!-- <div class="col-auto">
                        <button wire:click="generatePDF" class="btn btn-outline-primary select-md btn_outline">Download Invoice</button>
                    </div> -->


                
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover ledger">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Updated Date</th>
                                    <th>Date</th>
                                    <th>Transaction Id / Voucher No</th>
                                    <th>Purpose</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Closing</th>
                                    <th>Entered at</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $net_value = $cred_value = $deb_value = 0;
                                    $cred_ob_amount = $deb_ob_amount = $zero_ob_amount = "";
                                    // dd($day_opening_amount);
                                    $getCrDrOB = App\Helpers\Helper::getCrDr($day_opening_amount);
                                    if($getCrDrOB == 'Cr'){
                                        $cred_ob_amount = $day_opening_amount;
                                        $cred_value += $cred_ob_amount;
                                    } else if($getCrDrOB == 'Dr'){
                                        $deb_ob_amount = $day_opening_amount;
                                        $deb_ob_amount_positive = App\Helpers\Helper::replaceMinusSign($deb_ob_amount);
                                        $deb_value += $deb_ob_amount_positive;
                                    } else if($getCrDrOB == ''){
                                        $zero_ob_amount = "";
                                    }                        
                                    if(!empty($is_opening_bal_showable)){
                                        $net_value += $day_opening_amount;
                                    }     
                                @endphp
                                @if (!empty($ledgerData) && !empty($user_type) &&  $is_opening_bal_showable==1)
                                @php
                                    $deb_value += (float)$deb_ob_amount;
                                @endphp
                                <tr class="cursor-pointer">
                                    <td></td>
                                    <td></td>
                                    <td>{{ date('d-m-Y', strtotime($from_date)) }}</td>
                                    <td></td>
                                    <td>Opening Balance</td>
                                    <td>
                                        <span class="text-danger">{{ App\Helpers\Helper::replaceMinusSign($deb_ob_amount) }}</span>
                                    </td>
                                    <td>
                                        <span class="text-success">{{ $cred_ob_amount }}</span>
                                    </td>
                                    <td>                            
                                        {{ App\Helpers\Helper::replaceMinusSign($day_opening_amount) }} 
                                        {{ App\Helpers\Helper::getCrDr($day_opening_amount) }}
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>                    
                                @endif 
                                @forelse($ledgerData as $index => $item)
                                    @php
                                        $debit_amount = $credit_amount = '';
                                        if(!empty($item->is_credit)){
                                            $credit_amount = $item->transaction_amount;
                                            $net_value += $item->transaction_amount;
                                            $cred_value += $item->transaction_amount;
                                        }
                                        if(!empty($item->is_debit)){
                                            $debit_amount = $item->transaction_amount;
                                            $net_value -= $item->transaction_amount;
                                            $deb_value += $item->transaction_amount;
                                        }
                                    @endphp
                                    <tr  class="store_details_row cursor-pointer">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->updated_at?date('d-m-Y', strtotime($item->updated_at)):"" }}</td>
                                        <td>{{ date('d-m-Y', strtotime($item->created_at)) }}</td>
                                        <td>{{ $item->transaction_id }}</td>
                                        <td>
                                            <strong>{{ ucwords(str_replace('_', ' ', $item->purpose)) }}({{ ucwords($item->bank_cash) }})</strong>
                                        </td>
                                        <td class="text-danger">
                                            @if($item->is_debit == 1)
                                            {{ number_format((float) $debit_amount) }}
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            @if($item->is_credit == 1)
                                            {{ number_format((float) $credit_amount) }}
                                            @endif
                                        </td>

                                        <td>
                                            {{ App\Helpers\Helper::replaceMinusSign($net_value) }} 
                                            <span class="{{ App\Helpers\Helper::getCrDr($net_value)==="Dr"?"text-danger":"text-success"}}">
                                                {{ App\Helpers\Helper::getCrDr($net_value) }}
                                            </span>
                                            
                                        </td>
                                        <td>{{ date('d-m-Y', strtotime($item->entry_date)) }}</td>
                                        <td>
                                            @if($item->is_credit == 1 && $item->customer_id && $item->purpose=='payment_receipt')
                                            <a href="{{route('admin.accounting.add_payment_receipt',$item->transaction_id)}}" class="btn btn-outline-success select-md btn_outline">Edit Payment</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                <tr>
                                    <td colspan="10">
                                        <div class="alert alert-danger p-2">
                                            Data not found!
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                @if(count($ledgerData)>0)
                                    <tr class="table-info">
                                        <td colspan="5"><strong>Closing Amount</strong>  </td>
                                        <td>
                                            <strong> {{ number_format((float) $deb_value) }}</strong>
                                        </td>
                                        <td>
                                            <strong>{{ number_format((float) $cred_value) }}</strong>
                                        </td>
                                        <td>                            
                                            <strong class="{{ App\Helpers\Helper::getCrDr($net_value)==="Dr"?"text-danger":"text-success"}}">                                                               
                                                {{ number_format(App\Helpers\Helper::replaceMinusSign($net_value)) }} {{ App\Helpers\Helper::getCrDr($net_value)}}
                                            </strong>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>
