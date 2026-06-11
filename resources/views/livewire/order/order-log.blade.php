<div class="container">
    <section class="admin__title">
        <h5>Order Logs</h5>
    </section>
    <section>
        <div class="search__filter">
          
            <div class="row align-items-center justify-content-between">
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">{{ env('ORDER_PREFIX'). $order->order_number }}</p>
                </div>
                <div class="col-auto mt-3">
                    <a href="{{route('admin.order.index')}}" class="btn btn-outline-danger select-md">Back</a>
                </div>
                
            </div>
        </div>
    </section>
    <div class="card my-2">
        <div class="card-header pb-0">
            <div class="row">
                @if(session()->has('message'))
                    <div class="alert alert-success" id="flashMessage">
                        {{ session('message') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
            </div>

            <div class="table-responsive p-0">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">SL #</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Purpose</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Previous</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Current</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Update BY</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">Updated At</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="align-center">
                                   {{ $log->sl_no }}
                                </td>
                                <td>{{ $log->purpose }}</td>
                                <td>{!! $log->before !!}</td>
                                <td>{!! $log->after !!}</td>

                                <td>{{ $log->user->name }}</td>
                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-4">
                    {{-- {{ $logs->links() }} --}}
                </div>
            </div>
        </div>
    </div>
    @if(empty($search))
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
    @endif

</div>
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('confirmCancel', function(event) {
            console.log("Received confirmCancel Event:", event.detail);

            if (event.detail && event.detail.orderId) {
                console.log("Order ID from Event:", event.detail.orderId);
            } else {
                console.error("Order ID is missing in the event.");
                return;
            }

            if (confirm('Are you sure you want to cancel the order?')) {
                console.log("Dispatching cancelOrder event with Order ID:", event.detail.orderId);
                Livewire.dispatch('cancelOrder', { orderId: event.detail.orderId });
            }
        });
    });




    </script>
@endpush
