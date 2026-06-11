<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">User Ledger</h3>
        {{-- <button class="btn btn-cta btn-sm">
            <i class="material-icons text-white" style="font-size: 15px;">refresh</i>
            Refresh
        </button> --}}
    </div>

    <div class="row g-3 mb-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label text-xs">From Date</label>
            <input type="date" class="form-control border border-2" wire:change="updateDate($event.target.value)" wire:model="from_date" value="{{$from_date}}" placeholder="From" autocomplete="off">
        </div>
        <div class="col-md-3">
            <label class="form-label text-xs">To Date</label>
            <input type="date" class="form-control border border-2" wire:change="updateDate($event.target.value)" wire:model="to_date" value="{{$to_date}}" placeholder="To" autocomplete="off">
        </div>
            <div class="col-md-3">
                <label class="form-label text-xs">User Type</label>
                <select class="form-control border border-2" wire:change="ChangeUsertype($event.target.value)">
                    <option selectd hidden>Choose User Type</option>
                    <option value="customer">Customer</option>
                    {{-- <option value="staff">Staff</option>
                    <option value="partner">Partner</option>
                    <option value="supplier">Supplier</option> --}}
                </select>
            </div>
            @if ($userType)
                <div class="col-md-3 mt-3">
                    <label class="form-label text-xs">Search {{ ucwords($userType) }}</label>
                    <input type="text" class="form-control border border-2"
                        wire:model="search"
                        wire:keyup="searchUsers"
                        placeholder="Search {{ ucwords($userType) }}...">
                    
                    @if (!empty($results))
                        <ul class="list-group position-absolute w-100 mt-1" style="z-index: 1050;">
                            @foreach ($results as $name)
                                <li class="list-group-item" style="cursor: pointer;" wire:click="selectUser('{{$name}}')">{{ $name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
       
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-items-center">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Date</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Order Id</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Purpose</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Debit</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Credit</th>
                            {{-- <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Closing</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledgerData as $entry)
                            <tr>
                                <td>{{ date('d/m/Y',strtotime($entry->transaction_date)) }}</td>
                                <td>{{ $entry->order?  $entry->order->order_number : "" }}</td>
                                <td>{{ $entry->purpose }}</td>
                                <td>{{ $entry->transaction_type == 'Debit' ? $entry->paid_amount : '0' }}</td>
                                <td>{{ $entry->transaction_type == 'Credit' ? $entry->paid_amount : '0' }}</td>
                                {{-- <td>{{ $entry->remaining_amount }}</td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
