<div class="container">

    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- PAGE TITLE                                                              --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <section class="admin__title">
        <h5>Ledger Report</h5>
    </section>


    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- FILTERS                                                                 --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <section>
        <div class="search__filter">

            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">

                        {{-- Search --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable mb-1">Search</label>
                            <input type="text"
                                   wire:model="search"
                                   wire:keyup="applyFilters"
                                   class="form-control select-md bg-white search-input"
                                   placeholder="Search by customer name or phone"
                                   style="width: 300px;">
                        </div>

                        {{-- Start Date --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">Start Date</label>
                            <input type="date"
                                   wire:model="from"
                                   wire:change="applyFilters"
                                   class="form-control select-md bg-white">
                        </div>

                        {{-- End Date --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">End Date</label>
                            <input type="date"
                                   wire:model="to"
                                   wire:change="applyFilters"
                                   class="form-control select-md bg-white">
                        </div>

                        {{-- Type --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">Type</label>
                            <select wire:model="type"
                                    wire:change="applyFilters"
                                    class="form-control select-md bg-white">
                                <option value="">All Types</option>
                                <option value="earned">Earned</option>
                                <option value="redeemed">Redeemed</option>
                                <option value="expired">Expired</option>
                                <option value="adjusted">Adjusted</option>
                            </select>
                        </div>

                        {{-- Channel --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">Channel</label>
                            <select wire:model="channel"
                                    wire:change="applyFilters"
                                    class="form-control select-md bg-white">
                                <option value="">All Channels</option>
                                <option value="store">Store</option>
                                <option value="grocery">Grocery</option>
                                <option value="airport">Airport Lounge</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row align-items-center justify-content-between mt-2">

                {{-- Summary counts --}}
                <div class="col-auto">
                    <p class="text-sm font-weight-bold">
                        {{ $totalPoints }} Points Transactions &nbsp;|&nbsp;
                        {{ $totalLounge }} Lounge Transactions
                    </p>
                </div>

                {{-- Actions --}}
                <div class="col-auto">
                    <div class="row g-3 align-items-end">

                        {{-- Tab toggle --}}
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <button wire:click="$set('tab','all')"
                                        class="btn select-md {{ $tab === 'all'    ? 'btn-success'        : 'btn-outline-success' }}">
                                    All
                                </button>
                                <button wire:click="$set('tab','points')"
                                        class="btn select-md {{ $tab === 'points' ? 'btn-success'        : 'btn-outline-success' }}">
                                    Points
                                </button>
                                <button wire:click="$set('tab','lounge')"
                                        class="btn select-md {{ $tab === 'lounge' ? 'btn-secondary'      : 'btn-outline-secondary' }}">
                                    Lounge
                                </button>
                            </div>
                        </div>

                        {{-- Clear --}}
                        <div class="col-auto">
                            <button wire:click="clearFilters"
                                    class="btn btn-outline-danger select-md">
                                Clear
                            </button>
                        </div>

                        {{-- Export CSV --}}
                        <div class="col-auto">
                            <a href="javascript:void(0)"
                               wire:click="export"
                               class="btn btn-outline-success select-md">
                                <i class="fas fa-file-csv me-1"></i> Export CSV
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- SUMMARY METRIC CARDS                                                   --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <div class="row g-3 my-2">

        {{-- Points issued --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Points Issued</p>
                <h5 class="font-weight-bold text-success mb-0">{{ ($summary['points']['issued'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Points redeemed --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Points Redeemed</p>
                <h5 class="font-weight-bold mb-0" style="color:red;">{{ ($summary['points']['redeemed'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Points expired --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Points Expired</p>
                <h5 class="font-weight-bold text-secondary mb-0">{{ ($summary['points']['expired'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Points net --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Net Outstanding</p>
                <h5 class="font-weight-bold text-info mb-0">{{ ($summary['points']['net_outstanding'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">Points liability</p>
            </div>
        </div>

        {{-- Lounge issued --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Lounge Issued</p>
                <h5 class="font-weight-bold mb-0" style="color:#534AB7;">{{ ($summary['lounge']['issued'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Lounge redeemed --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Lounge Redeemed</p>
                <h5 class="font-weight-bold mb-0" style="color:#993556;">{{ ($summary['lounge']['redeemed'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Lounge expired --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Lounge Expired</p>
                <h5 class="font-weight-bold text-secondary mb-0">{{ ($summary['lounge']['expired'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">This period</p>
            </div>
        </div>

        {{-- Lounge net --}}
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <p class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 mb-1">Lounge Outstanding</p>
                <h5 class="font-weight-bold text-info mb-0">{{ ($summary['lounge']['net_outstanding'] ?? 0) }}</h5>
                <p class="text-xs text-muted mt-1">Visits liability</p>
            </div>
        </div>

    </div>


    {{-- Flash messages --}}
    @if(session()->has('message'))
        <div class="alert alert-success" id="flashMessage">{{ session('message') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- POINTS TABLE                                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array($tab, ['all', 'points']))
    <div class="card my-2">
        <div class="card-header pb-0">

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-success" style="font-size:13px; padding:6px 14px;">
                    <i class="fas fa-coins me-1"></i> Points Ledger
                </span>
                <span class="text-sm text-muted">{{ $totalPoints }} transactions</span>
            </div>

            <div class="table-responsive p-0">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                wire:click="sortBy('id')" style="cursor:pointer;">
                                Date & Time
                                @if($sortField === 'id') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                wire:click="sortBy('customer_name')" style="cursor:pointer;">
                                Customer
                                @if($sortField === 'customer_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Type
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                wire:click="sortBy('points')" style="cursor:pointer;">
                                Points
                                @if($sortField === 'points') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Bal. Before
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Bal. After
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Channel
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Source
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Redeemed By
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Expiry Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pointsTransactions as $txn)
                        <tr>

                            {{-- Date --}}
                            <td class="align-middle">
                                <span class="text-dark text-sm font-weight-bold mb-0">
                                    {{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y') }}
                                </span><br>
                                <p class="small text-muted mb-1 badge bg-warning">
                                    {{ \Carbon\Carbon::parse($txn->created_at)->format('H:i') }}
                                </p>
                            </td>

                            {{-- Customer --}}
                            <td>
                                <p class="small text-muted mb-1">
                                    <span>Name: <strong>{{ ucwords($txn->customer->name ?? '—') }}</strong></span><br>
                                    <span>Mobile: <strong>{{ $txn->customer->phone ?? '—' }}</strong></span>
                                </p>
                            </td>

                            {{-- Type badge --}}
                            <td class="align-middle">
                                @php
                                    $typeLabel = $txn->is_expired ? 'expired'
                                        : ($txn->type === 'credit'
                                            ? (in_array($txn->source, ['bonus','manual_adjustment','adjustment']) ? 'adjusted' : 'earned')
                                            : 'redeemed');
                                    $typeClass = match($typeLabel) {
                                        'earned'   => 'bg-success',
                                        'redeemed' => 'bg-warning text-dark',
                                        'expired'  => 'bg-secondary',
                                        'adjusted' => 'bg-info',
                                        default    => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $typeClass }}">{{ ucfirst($typeLabel) }}</span>
                            </td>

                            {{-- Points --}}
                            <td class="align-middle">
                                @if($txn->type === 'debit')
                                    <span class="text-danger font-weight-bold">−{{ ($txn->points) }}</span>
                                @else
                                    <span class="text-success font-weight-bold">+{{ ($txn->points) }}</span>
                                @endif
                            </td>

                            {{-- Balance before --}}
                            <td class="align-middle">
                                <p class="text-xs font-weight-bold mb-0">{{ ($txn->balance_before ?? 0) }}</p>
                            </td>

                            {{-- Balance after --}}
                            <td class="align-middle">
                                <p class="text-xs font-weight-bold mb-0">{{ ($txn->balance_after ?? 0) }}</p>
                            </td>

                            {{-- Channel --}}
                            <td class="align-middle">
                                <span class="badge bg-light text-dark border">{{ ucfirst($txn->channel ?? '—') }}</span>
                            </td>

                            {{-- Source --}}
                            <td class="align-middle">
                                <p class="text-xs text-muted mb-0">{{ ucfirst($txn->source ?? '—') }}</p>
                            </td>

                            {{-- Redeemed by --}}
                            <td class="align-middle">
                                <p class="text-xs text-muted mb-0">{{ $txn->redeemedBy->name ?? '—' }}</p>
                            </td>

                            {{-- Expiry --}}
                            <td class="align-middle">
                                @if($txn->expiry_date)
                                    @if(\Carbon\Carbon::parse($txn->expiry_date)->isPast())
                                        <span class="badge bg-danger">
                                            {{ \Carbon\Carbon::parse($txn->expiry_date)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">
                                            {{ \Carbon\Carbon::parse($txn->expiry_date)->format('d M Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">No points transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $pointsTransactions->links() }}
                </div>
            </div>

        </div>
    </div>
    @endif


    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- LOUNGE TABLE                                                           --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array($tab, ['all', 'lounge']))
    <div class="card my-2">
        <div class="card-header pb-0">

            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge" style="background:#534AB7; font-size:13px; padding:6px 14px;">
                    <i class="fas fa-plane me-1"></i> Lounge Visits Ledger
                </span>
                <span class="text-sm text-muted">{{ $totalLounge }} transactions</span>
            </div>

            <div class="table-responsive p-0">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                style="cursor:pointer;" wire:click="sortBy('id')">
                                Date & Time
                                @if($sortField === 'id') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                style="cursor:pointer;" wire:click="sortBy('customer_name')">
                                Customer
                                @if($sortField === 'customer_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Type
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                style="cursor:pointer;" wire:click="sortBy('lounge_visits')">
                                Visits
                                @if($sortField === 'lounge_visits') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅ @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Before
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                After
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Used
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Channel
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Source
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Redeemed By
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Expiry Date
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loungeTransactions as $txn)
                        <tr>

                            {{-- Date --}}
                            <td class="align-middle">
                                <span class="text-dark text-sm font-weight-bold mb-0">
                                    {{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y') }}
                                </span><br>
                                <p class="small text-muted mb-1 badge bg-warning">
                                    {{ \Carbon\Carbon::parse($txn->created_at)->format('H:i') }}
                                </p>
                            </td>

                            {{-- Customer --}}
                            <td>
                                <p class="small text-muted mb-1">
                                    <span>Name: <strong>{{ ucwords($txn->customer->name ?? '—') }}</strong></span><br>
                                    <span>Mobile: <strong>{{ $txn->customer->phone ?? '—' }}</strong></span>
                                </p>
                            </td>

                            {{-- Type badge --}}
                            <td class="align-middle">
                                @php
                                    $lTypeLabel = $txn->is_expired ? 'expired'
                                        : ($txn->type === 'credit' ? 'earned' : 'redeemed');
                                    $lTypeClass = match($lTypeLabel) {
                                        'earned'   => 'text-white',
                                        'redeemed' => 'text-white',
                                        'expired'  => 'bg-secondary',
                                        default    => 'bg-light text-dark',
                                    };
                                    $lTypeBg = match($lTypeLabel) {
                                        'earned'   => '#534AB7',
                                        'redeemed' => '#993556',
                                        default    => '',
                                    };
                                @endphp
                                <span class="badge {{ $lTypeClass }}"
                                      @if($lTypeBg) style="background:{{ $lTypeBg }}" @endif>
                                    {{ ucfirst($lTypeLabel) }}
                                </span>
                            </td>

                            {{-- Visits --}}
                            <td class="align-middle">
                                @if($txn->type === 'debit')
                                    <span class="font-weight-bold" style="color:#993556;">−{{ $txn->lounge_visits }}</span>
                                @else
                                    <span class="font-weight-bold" style="color:#534AB7;">+{{ $txn->lounge_visits }}</span>
                                @endif
                            </td>

                            {{-- Before --}}
                            <td class="align-middle">
                                <p class="text-xs font-weight-bold mb-0">{{ $txn->lounge_before ?? '—' }}</p>
                            </td>

                            {{-- After --}}
                            <td class="align-middle">
                                <p class="text-xs font-weight-bold mb-0">{{ $txn->lounge_after ?? '—' }}</p>
                            </td>

                            {{-- Used --}}
                            <td class="align-middle">
                                <p class="text-xs font-weight-bold mb-0">{{ $txn->lounge_used ?? 0 }}</p>
                            </td>

                            {{-- Channel --}}
                            <td class="align-middle">
                                <span class="badge bg-light text-dark border">{{ ucfirst($txn->channel ?? '—') }}</span>
                            </td>

                            {{-- Source --}}
                            <td class="align-middle">
                                <p class="text-xs text-muted mb-0">{{ ucfirst($txn->source ?? '—') }}</p>
                            </td>

                            {{-- Redeemed by --}}
                            <td class="align-middle">
                                <p class="text-xs text-muted mb-0">{{ $txn->redeemedBy->name ?? '—' }}</p>
                            </td>

                            {{-- Expiry --}}
                            <td class="align-middle">
                                @if($txn->expiry_date)
                                    @if(\Carbon\Carbon::parse($txn->expiry_date)->isPast())
                                        <span class="badge bg-danger">
                                            {{ \Carbon\Carbon::parse($txn->expiry_date)->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">
                                            {{ \Carbon\Carbon::parse($txn->expiry_date)->format('d M Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No lounge transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $loungeTransactions->links() }}
                </div>
            </div>

        </div>
    </div>
    @endif


    {{-- Loading spinner (same as order index) --}}
    @if(empty($search))
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
    @endif

</div>