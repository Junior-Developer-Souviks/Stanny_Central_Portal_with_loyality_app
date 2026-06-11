<div class="container">
    <section class="admin__title">
        <h5>Customer Opening Balance</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Report</li>
            <li><a href="{{route('admin.accounting.list_opening_balance')}}">Customer Opening Balance</a></li>
            <li class=" back-button btn btn-success btn-sm text-decoration-none text-light font-weight-bold mb-0">
                <a href="{{route('admin.accounting.add_opening_balance')}}" class="text-white d-flex align-items-center">
                    <i class="material-icons text-white" style="font-size: 15px;">add</i>
                    Add</a>
            </li>
        </ul>
    </section>
    <div class="card">
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover" x-data="{selectedRow:null}">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- @dump($openingBalances) --}}
                        @foreach($openingBalances as $index => $balance)
                            <tr @click="selectedRow === {{ $index }} ? selectedRow = null : selectedRow = {{ $index }}" class="cursor-pointer">
                                <td>{{ $index + 1 }}</td>
                                <td>{{ date('d/m/Y', strtotime($balance->entry_date)) }}</td>
                                <td>{{ $balance->transaction_id }}</td>
                                <td>{{ $balance->customer ? $balance->customer->name : "" }}</td>
                                <td>
                                    <strong class="{{ $balance->payment && $balance->payment->payment_for === 'credit' ? 'text-success' : ($balance->payment && $balance->payment->payment_for === 'debit' ? 'text-danger' : '') }}">
                                        {{ number_format($balance->transaction_amount, 2) }}
                                        ({{ ucfirst($balance->payment ? $balance->payment->bank_cash : "") }})
                                    </strong>
                                </td>
                                <td>
                                    <a class="btn btn-outline-danger select-md btn_edit mt-2" wire:click="confirmDelete({{ $balance->id }})" @click.stop>Delete</a>
                                </td>
                            </tr>
                            <tr x-show="selectedRow === {{ $index }}" x-cloak>
                                <td colspan="6">
                                    <div class="store_details">
                                        <table class="table">
                                            <tr>
                                                <td><strong>Person Name:</strong> {{ $balance->customer ? $balance->customer->name : "" }}</td>
                                                <td><strong>Company Name:</strong> {{ $balance->customer ? $balance->customer->company_name : "" }}</td>
                                                <td><strong>Phone:</strong> {{ $balance->customer ? $balance->customer->phone : "" }}</td>
                                                <td><strong>WhatsApp:</strong> {{ $balance->customer ? $balance->customer->whatsapp_no : "" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Purpose:</strong> {{ $balance->customer ? $balance->purpose : "" }}</td>
                                                <td><strong>Description:</strong> {{ $balance->customer ? $balance->purpose_description : "" }}</td>
                                                <td><strong>Amount:</strong> {{ $balance->payment ? $balance->payment->amount : "" }}</td>
                                                <td><strong>Credit / Debit:</strong> {{ $balance->payment ? $balance->payment->payment_for : "" }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Payment Mode:</strong> {{ $balance->payment ? $balance->payment->payment_mode : "" }}</td>
                                                <td><strong>Bank:</strong> {{ $balance->payment ? $balance->payment->bank_name : "" }}</td>
                                                <td><strong>Narration:</strong> {{ $balance->payment ? $balance->payment->narration : "" }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    {{-- {{ $customers->links() }}  --}}
                </div>
            </div>
        </div>
    </div>
    @push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('alpine:init', () => {
        Alpine.data('accordion', () => ({
            selectedRow: null
        }));
    });
    </script>
    <script>
        window.addEventListener('showDeleteConfirm', function (event) {
            // console.log(event);
            let itemId = event.detail[0].itemId; // Assign itemId correctly
            Swal.fire({
                title: "Are you sure?",
                text: "This action cannot be undone!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('DeleteItem', itemId); // Call Livewire method
                    Swal.fire("Deleted!", "The opening balance has been deleted.", "success");
                }
            });
        });
    </script>
    @endpush
    
</div>

