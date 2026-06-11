<div class="container">
    <section class="admin__title">
        <h5>{{$parent_id == 1 ?'Recurring Expense' : 'Non Recurring Expense'}}</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>{{$parent_id == 1 ?'Recurring Expense' : 'Non Recurring Expense'}}</li>
            <li></li>
            <!-- <li>Create Customer</li> -->
        </ul>
        <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <!-- <p class="text-sm font-weight-bold">Items</p> -->
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
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Debit Purpose</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Credit Purpose</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">For Customer</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">For Staff</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">For Supplier</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $k => $expense)
                                        <tr>
                                            <td><h6 class="mb-0 text-sm">{{ucwords($expense->title)}}</h6></td>
                                            <td class="align-middle">
                                                <span class="badge {{$expense->for_debit == 1 ? 'bg-success' :   'bg-danger'}}">  
                                                   {{ $expense->for_debit == 1 ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="align-middle"> 
                                                <span class="badge {{$expense->for_credit == 1? "bg-success" : "bg-danger" }}">
                                                    {{$expense->for_credit == 1? "Yes" : "No" }}
                                                </span>
                                            </td>
                                            <td class="align-middle"> 
                                                <span class="badge {{$expense->for_customer  == 1? "bg-success" : "bg-danger" }}">
                                                    {{$expense->for_customer  == 1? "Yes" : "No" }}
                                                </span>
                                            </td>
                                            <td class="align-middle"> 
                                                <span class="badge {{$expense->for_staff == 1? "bg-success" : "bg-danger" }}">
                                                    {{$expense->for_staff == 1? "Yes" : "No" }}
                                                </span>
                                            </td>
                                            <td class="align-middle"> 
                                                <span class="badge {{$expense->for_supplier  == 1? "bg-success" : "bg-danger" }}">
                                                    {{$expense->for_supplier  == 1? "Yes" : "No" }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-sm" style="text-align: center;">
                                                <div class="form-check form-switch">
                                                    <input 
                                                        class="form-check-input ms-auto" 
                                                        type="checkbox" 
                                                        id="flexSwitchCheckDefault{{$expense->id}}" 
                                                        wire:click="toggleStatus({{$expense->id}})"
                                                        @if($expense->status) checked @endif
                                                    >
                                                </div>
                                            </td>
                                            <td class="align-middle text-end px-4">
                                               
                                                <button wire:click="edit({{$expense->id}})" class="btn btn-outline-primary select-md btn_action btn_outline" title="Edit">Edit</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- {{ $expenses->links() }} --}}
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
                    <div class="card-body px-0 pb-2 mx-4 asibe-bar">
                        <form wire:submit.prevent="saveExpense">
                            @csrf
                                <h4 class="page__subtitle">{{ $expenseId ? 'Edit Expense' : 'Add New Expense' }}</h4>
                                <input type="hidden" name="parent_id" value="{{$parent_id}}">                         
                                <div class="form-group mb-3">
                                    <label class="label-control">Title <span class="text-danger">*</span> </label>
                                    <input type="text" wire:model="title" placeholder="Enter Expense Title" class="form-control border border-2 p-2" value="{{old('title')}}" >
                                    @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>
                                <div class="form-group mb-3">
                                    <label class="label-control">Description </label>
                                    <textarea wire:model="description" placeholder="Enter Expense Description" class="form-control border border-2 p-2" rows="5">{{old('description')}}</textarea>
                                    @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div> 
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input wire:model="for_debit" class="form-check-input" type="checkbox" value="1"  id="for_debit">
                                                <i></i>
                                                <label class="form-check-label" for="for_debit">
                                                  Debit Purpose
                                                </label>
                                            </div>
                                            @error('for_debit') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>  
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input wire:model="for_credit" class="form-check-input" type="checkbox" value="1" id="for_credit">
                                                <i></i>
                                                <label class="form-check-label" for="for_credit">
                                                  Credit Purpose
                                                </label>
                                            </div>
                                            @error('for_credit') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div> 
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input wire:model="for_staff" class="form-check-input" type="checkbox" value="1"  id="for_staff">
                                                <i></i>
                                                <label class="form-check-label" for="for_staff">
                                                  For Staff
                                                </label>
                                            </div>
                                            @error('for_staff') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>  
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input wire:model="for_customer" class="form-check-input" type="checkbox" value="1"  id="for_customer">
                                                <i></i>
                                                <label class="form-check-label" for="for_customer">
                                                  For Customer
                                                </label>
                                            </div>
                                            @error('for_customer') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>  
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <div class="form-check ps-0 custom-checkbox">
                                                <input wire:model="for_supplier" class="form-check-input" type="checkbox" value="1"  id="for_supplier">
                                                <i></i>
                                                <label class="form-check-label" for="for_supplier">For Supplier</label>
                                            </div>
                                            @error('for_supplier') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>  
                                    </div>
                                </div>                       
                                                        
                                <div class="mb-2 text-end">
                                    @if($expenseId)
                                        <a href="javascript:void(0);" 
                                        class="btn btn-sm btn-danger select-md" 
                                        wire:click.prevent="resetForm">
                                        Clear
                                    </a>
                                    @endif
                                    <button type="submit" class="btn btn-sm btn-success select-md" 
                                            wire:loading.attr="disabled">
                                        <span> 
                                         {{$expenseId ? 'Update Expense' : 'Create Expense'}}
                                        </span>
                                    </button>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>