

    <!-- Navbar -->
    <!-- End Navbar -->
    <!-- <div class="container-fluid py-4"> -->
    
    <div class="container">
        <style>
            /* Hide details by default */
            .store_details_column {
                display: none;
            }
       </style>
        <section class="admin__title">
            <h5>Expenses List</h5>
        </section>
        <section>
            <div class="search__filter">
                <div class="row align-items-center justify-content-end">
                    <div class="col-auto">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-auto mt-3">
                                <a href="{{ route('admin.accounting.add_depot_expense') }}" class="btn btn-outline-success select-md"><i class="material-icons">add</i>Add Expense</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">
                        <!-- <p class="text-sm font-weight-bold">Items</p> -->
                    </div>
                    <div class="col-auto">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto mt-0">
                                <!-- <label for="" class="date_lable">Payment Date</label> -->
                                <input type="date" wire:model="paymentDate" wire:change="AddPaymentDate($event.target.value)"
                                    class="form-control select-md bg-white">
                            </div>
                            <div class="col-auto mt-0">
                                <input type="text"
                                      wire:keyup="searchExpense($event.target.value)"
                                       class="form-control select-md bg-white"
                                       placeholder="Search Expense by Transaction ID or Amount"
                                       style="width: 250px;" >
                            </div>
                    
                            <div class="col-auto mt-3">
                                <a href="{{route('admin.accounting.list.depot_expense')}}" class="btn btn-outline-danger select-md">Clear</a>
                            </div>
                            <div class="col-auto">
                                <button wire:click="export" class="btn btn-outline-success select-md"><i class="fas fa-file-csv me-1"></i>Export</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <div class="card my-2">
            <div class="row">
                
                <div class="col-md-12">           
                    <div class="table-responsive"> 
                        <table class="table table-sm table-hover ledger">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expense Date</th>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Created From</th>
                                    <th>Approval</th>
                                    <th>Approved By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                               
                                @forelse ($expenses as $key=>$item)
                                @php
                                    $ExpenseAt = "";
                                    $ExpenseType = "";

                                    $expenseData =($item->staff_id ? DB::table('users')->where('id', $item->staff_id)->first() :
                                                ($item->customer_id ? DB::table('users')->where('id', $item->customer_id)->first() :
                                                ($item->supplier_id ? DB::table('suppliers')->where('id', $item->supplier_id)->first() : null)));

                                
                                    
                                    $expenseType = $item->expense_id ? DB::table('expences')->where('id', $item->expense_id)->first() : null;
                                    $ExpenseType = $expenseType ? $expenseType->title : "";
                                @endphp
                                <tr class="store_details_row">  
                                    <td>{{$key +1 }}</td>
                                    <td>@if($item->payment_date){{date('d/m/Y', strtotime($item->payment_date))}}@endif</td>    
                                    <td>{{ $item->voucher_no }}</td>
                                    <td>Rs. {{number_format((float)$item->amount, 2, '.', '')}} ( {{ucwords($item->bank_cash)}} )</td>    
                                    <td>
                                            <span class="badge bg-success">{{ucwords($item->created_from)}}</span>
                                    </td>  
                                    <td>
                                        @if (!empty($item->is_ledger_added))
                                            <span class="badge bg-success">Approved</span>                                
                                        @else
                                            <span class="badge bg-danger">Not Approved</span>  
                                            
                                        @endif
                                    </td>
                                    
                                    <td>
                                        {{$item->ExpenseApprovedBy ? $item->ExpenseApprovedBy->name : ""}}
                                    </td>
                                    
                                    <td>
                                         @if($item->created_from == 'app' && $item->is_ledger_added == 0 && $canApprove)
                           
                                                <a href="{{route('admin.accounting.expense.details',$item->id)}}" class="btn btn-outline-warning select-md">
                                                    Approve
                                                </a>
                                           
                                        @endif
                                        <a href="{{ route('admin.accounting.edit_depot_expense', $item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                                    </td>                                   
                                </tr>
                                <tr>                        

                                    <td colspan="5" class="store_details_column">

                                        <div class="store_details">

                                            <table class="table">                                   

                                                <tr>   

                                                    <td><span>Amount: <strong>Rs. {{number_format((float)$item->amount, 2, '.', '')}}</strong></span></td>                        

                                                    @php
                                                        $expenseAt = '';

                                                        if ($item->stuff_id && $item->staff) {
                                                            $expenseAt = 'Staff Name: ' . $item->staff->name;
                                                        } elseif ($item->customer_id && $item->customer) {
                                                            $expenseAt = 'Customer Name: ' . $item->customer->name;
                                                        } elseif ($item->supplier_id && $item->supplier) {
                                                            $expenseAt = 'Supplier Name: ' . $item->supplier->name;
                                                        }
                                                    @endphp

                                                    <td>{{ $expenseAt }}</strong></span></td>
  

                                                    @if (!empty($item->payment_mode))

                                                        <td><span>Payment Mode: <strong>{{ ucwords($item->payment_mode)}}</strong></span></td>    

                                                    @endif

                                                    @if (!empty($item->bank_name))

                                                        <td><span>Bank: <strong>{{ ucwords($item->bank_name)}}</strong></span></td>    

                                                    @endif

                                                    @if (!empty($item->chq_utr_no))

                                                        <td><span>Cheque / UTR No: <strong>{{ ucwords($item->chq_utr_no)}}</strong></span></td>    

                                                    @endif

                                                    @if (!empty($item->narration))

                                                        <td><span>Narration: <strong>{{ ucwords($item->narration)}}</strong></span></td>    

                                                    @endif

                                                </tr>

                                                <tr>

                                                    @if (!empty($item->created_by))

                                                        <td><span>Created By: <strong>{{ ucwords($item->creator?$item->creator->name:" ")}}</strong></span></td>  

                                                        <td><span>Created At: <strong>{{ date('d/m/Y h:i A', strtotime($item->created_at)) }}</strong></span></td>   
                                                    @endif
                                                    @if($ExpenseAt)
                                                    <td><span>Expense At: <strong>{{ $ExpenseAt }}</strong></span></td> 
                                                    @endif
                                                    @if($ExpenseType)
                                                    <td><span>Expense: <strong>{{ $ExpenseType }}</strong></span></td> 
                                                    @endif
                                                    
                                                     <td>
                                                        <span>Receipt Copy: </span>
                                                    
                                                            @if (!empty($item->expense_proof))
                                                                <a href="{{ asset($item->expense_proof) }}" 
                                                                   target="_blank" 
                                                                   class="btn btn-sm btn-outline-success select-md mt-2">
                                                                    View Receipt
                                                                </a>
                                                            @else
                                                                <span class="text-danger">No File</span>
                                                            @endif
                                                    </td>
                                                </tr>

                                                <tr>

                                                    @if (!empty($item->updater))

                                                        <td><span>Updated By: <strong>{{ ucwords($item->updater->name)}}</strong></span></td>  

                                                        <td><span>Updated At: <strong>{{ date('d/m/Y h:i A', strtotime($item->updated_at)) }}</strong></span></td>    

                                                    @endif

                                                </tr>

                                            </table>

                                        </div>

                                    </td>

                                </tr>
                                
                                @empty
                                <tr>

                                    <td colspan="100%">
            
                                        <span></span>
            
                                    </td>
            
                                </tr>   
                                @endforelse
                            </tbody>
                        </table>   
                    </div>
                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            {{ $expenses->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        @push('js')
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
                window.addEventListener('close-import-modal', event => {
                    var importModal = document.getElementById('importModal');
                    var modal = bootstrap.Modal.getInstance(importModal);
                    modal.hide();
                });
                $(document).ready(function () {
                    $(".store_details_row").click(function () {
                        // Toggle visibility of the next .store_details_column row
                        $(this).next("tr").find(".store_details_column").toggle();
                    });
                });
            </script>
    
        @endpush
    </div>
    
    