<div class="container">
    <style>
       /* Common */
        .points-card{
            height:90px;
            border-radius:12px;
            overflow:hidden;
            transition:
                transform .35s cubic-bezier(.25,.8,.25,1),
                box-shadow .35s ease;
        }


        .points-card:hover{
            transform:translateY(-5px) scale(1.01);
            box-shadow:0 18px 35px rgba(0,0,0,.18);
        }


        .points-card .card-header{
            height:100%;
            position:relative;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:15px 20px;
            color:#fff;
        }



        .dash-big-icon{
            width:75px;
            height:75px;
            opacity:.35;
        }


        .dash-big-icon svg{
            width:65px;
            height:65px;
        }



        .card-content{
            position:relative;
            z-index:2;
            text-align:right;
        }


        .card-content h2{
            color:white;
            font-size:14px;
            font-weight:600;
            margin:0;
        }


        .card-content h3{
            color:white;
            font-size:28px;
            font-weight:700;
            margin:0;
        }



        /* ===================
        POINTS COLORS
        =================== */


        .point-theme{
            background:#009cc7;
        }


        .point-theme.green{
            background:#00a86b;
        }


        .point-theme.orange{
            background:#f39c12;
        }


        .point-theme.red{
            background:#e34b3d;
        }



        /* ===================
        LOUNGE COLORS
        =================== */


        .lounge-theme{
            background:#673ab7;
        }


        .lounge-theme.green{
            background:#008f5d;
        }


        .lounge-theme.orange{
            background:#ff8c00;
        }


        .lounge-theme.red{
            background:#c62828;
        }

        .vertical-plane svg {
            transform: rotate(90deg); /* makes it vertical */
            transform-origin: center;
        }
    </style>
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- PAGE TITLE --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <section class="admin__title">
        <h5>Ledger Report</h5>
    </section>


    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- FILTERS --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <section>
        <div class="search__filter">

            <div class="row align-items-center justify-content-end">
                <div class="col-auto">
                    <div class="row g-3 align-items-center">

                        {{-- Search --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable mb-1">Search</label>
                            <input type="text" wire:model="search" wire:keyup="applyFilters"
                                class="form-control select-md bg-white search-input"
                                placeholder="Search by customer name or phone" style="width: 300px;">
                        </div>

                        {{-- Start Date --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">Start Date</label>
                            <input type="date" wire:model="from" wire:change="applyFilters"
                                class="form-control select-md bg-white">
                        </div>

                        {{-- End Date --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">End Date</label>
                            <input type="date" wire:model="to" wire:change="applyFilters"
                                class="form-control select-md bg-white">
                        </div>

                        {{-- Type --}}
                        <div class="col-auto" style="margin-top: -27px;">
                            <label class="date_lable">Type</label>
                            <select wire:model="type" wire:change="applyFilters"
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
                            <select wire:model="channel" wire:change="applyFilters"
                                class="form-control select-md bg-white">
                                <option value="">All Channels</option>
                                <option value="store">Store</option>
                                <option value="sales_person">Sales Person</option>
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
                            <button wire:click="clearFilters" class="btn btn-outline-danger select-md">
                                Clear
                            </button>
                        </div>

                        {{-- Export CSV --}}
                        <div class="col-auto">
                            <a href="javascript:void(0)" wire:click="export" class="btn btn-outline-success select-md">
                                <i class="fas fa-file-csv me-1"></i> Export CSV
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    {{-- SUMMARY METRIC CARDS --}}
    {{-- ─────────────────────────────────────────────────────────────────────── --}}
    <div class="row g-3 my-2">


        {{-- Points Issued --}}
        <div class="col-6 col-md-3">
            <div class="card data-card points-card point-theme">

                <div class="card-header">

                 <div class="dash-big-icon">
                    <svg viewBox="0 0 24 24" width="100%" height="100%">
                        <!-- Shopping Cart Base -->
                        <path fill="currentColor" 
                            d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zM7 15h11.5c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.37-.66-.11-1.48-.87-1.48H5.21l-.94-2H1v2h2l3.6 7.59-1.35 2.44C4.52 15.37 5.48 17 7 17h13v-2H7z"/>
                        
                        <!-- Loyalty Star (Centered inside the cart basket) -->
                        <path fill="#fff" 
                            d="M13 7.7l1.1 2.2 2.4.4-1.7 1.7.4 2.4-2.2-1.2-2.2 1.2.4-2.4-1.7-1.7 2.4-.4z"/>
                    </svg>
                </div>


                    <div class="card-content">
                        <h2>Points Issued</h2>

                        <h3>
                            {{ $summary['points']['issued'] ?? 0 }}
                        </h3>
                    </div>
                </div>

            </div>
        </div>



        {{-- Points Redeemed --}}
        <div class="col-6 col-md-3">
            <div class="card data-card points-card point-theme green">
                <div class="card-header">
                    <div class="dash-big-icon">
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="35" fill="none" stroke="currentColor" stroke-width="7" />

                            <path d="M30 52l15 15 28-35" fill="none" stroke="currentColor" stroke-width="8" />
                        </svg>
                    </div>


                    <div class="card-content">
                        <h2>Points Redeemed</h2>

                        <h3>
                            {{ $summary['points']['redeemed'] ?? 0 }}
                        </h3>
                    </div>


                </div>
            </div>
        </div>




        {{-- Points Expired --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card point-theme orange">

                <div class="card-header">
                    <div class="dash-big-icon">
                        <svg viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="35" fill="none" stroke="currentColor" stroke-width="7" />

                            <path d="M35 35L65 65" stroke="currentColor" stroke-width="8" />

                            <path d="M65 35L35 65" stroke="currentColor" stroke-width="8" />
                        </svg>
                    </div>


                    <div class="card-content">
                        <h2>Points Expired</h2>

                        <h3>
                            {{ $summary['points']['expired'] ?? 0 }}
                        </h3>
                    </div>


                </div>
            </div>

        </div>




        {{-- Net Outstanding --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card point-theme red">


                <div class="card-header">


                    <div class="dash-big-icon">


                        <svg viewBox="0 0 100 100">

                            <rect x="25" y="20" width="50" height="60" rx="8" fill="none" stroke="currentColor"
                                stroke-width="7" />


                            <path d="M38 40h25" stroke="currentColor" stroke-width="6" />


                            <path d="M38 55h18" stroke="currentColor" stroke-width="6" />

                        </svg>


                    </div>



                    <div class="card-content">

                        <h2>Net Outstanding</h2>

                        <h3>
                            {{ $summary['points']['net_outstanding'] ?? 0 }}
                        </h3>
                    </div>
                </div>

            </div>

        </div>






        {{-- Lounge Issued --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card lounge-theme">


                <div class="card-header">


                    <div class="dash-big-icon vertical-plane">

                        <svg viewBox="0 0 24 24" width="100%" height="100%">
                            <path fill="currentColor"
                                d="M21 16v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V9L2 14v2l8-2.5V19l-2 1.5V22l3-1 3 1v-1.5L13 19v-5.5L21 16z"/>
                        </svg>

                    </div>


                    <div class="card-content">

                        <h2>Lounge Issued</h2>

                        <h3>
                            {{ $summary['lounge']['issued'] ?? 0 }}
                        </h3>


                    </div>


                </div>

            </div>

        </div>






        {{-- Lounge Redeemed --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card lounge-theme green">


                <div class="card-header">


                    <div class="dash-big-icon">


                        <svg viewBox="0 0 100 100">

                            <circle cx="50" cy="50" r="35" fill="none" stroke="currentColor" stroke-width="7" />


                            <path d="M30 52L45 67L72 35" fill="none" stroke="currentColor" stroke-width="8" />


                        </svg>


                    </div>



                    <div class="card-content">

                        <h2>Lounge Redeemed</h2>

                        <h3>
                            {{ $summary['lounge']['redeemed'] ?? 0 }}
                        </h3>


                    </div>



                </div>

            </div>

        </div>







        {{-- Lounge Expired --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card lounge-theme orange">


                <div class="card-header">


                    <div class="dash-big-icon">


                        <svg viewBox="0 0 100 100">

                            <circle cx="50" cy="50" r="35" fill="none" stroke="currentColor" stroke-width="7" />


                            <path d="M35 35L65 65" stroke="currentColor" stroke-width="8" />


                            <path d="M65 35L35 65" stroke="currentColor" stroke-width="8" />


                        </svg>


                    </div>



                    <div class="card-content">

                        <h2>Lounge Expired</h2>

                        <h3>
                            {{ $summary['lounge']['expired'] ?? 0 }}
                        </h3>


                    </div>



                </div>

            </div>

        </div>






        {{-- Lounge Outstanding --}}
        <div class="col-6 col-md-3">

            <div class="card data-card points-card lounge-theme red">


                <div class="card-header">


                    <div class="dash-big-icon">


                        <svg viewBox="0 0 100 100">

                            <circle cx="50" cy="50" r="35" fill="none" stroke="currentColor" stroke-width="7" />


                            <path d="M50 30V52L68 68" fill="none" stroke="currentColor" stroke-width="7" />


                        </svg>


                    </div>



                    <div class="card-content">

                        <h2>Lounge Outstanding</h2>

                        <h3>
                            {{ $summary['lounge']['net_outstanding'] ?? 0 }}
                        </h3>


                    </div>


                </div>

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
    {{-- POINTS TABLE --}}
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
                                @if($sortField === 'customer_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅
                                @endif
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
                                ? (in_array($txn->source, ['bonus','manual_adjustment','adjustment']) ? 'adjusted' :
                                'earned')
                                : 'redeemed');
                                $typeClass = match($typeLabel) {
                                'earned' => 'bg-success',
                                'redeemed' => 'bg-warning text-dark',
                                'expired' => 'bg-secondary',
                                'adjusted' => 'bg-info',
                                default => 'bg-light text-dark',
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
    {{-- LOUNGE TABLE --}}
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
                                @if($sortField === 'customer_name') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅
                                @endif
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                Type
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10"
                                style="cursor:pointer;" wire:click="sortBy('lounge_visits')">
                                Visits
                                @if($sortField === 'lounge_visits') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @else ⇅
                                @endif
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
                                'earned' => 'text-white',
                                'redeemed' => 'text-white',
                                'expired' => 'bg-secondary',
                                default => 'bg-light text-dark',
                                };
                                $lTypeBg = match($lTypeLabel) {
                                'earned' => '#534AB7',
                                'redeemed' => '#993556',
                                default => '',
                                };
                                @endphp
                                <span class="badge {{ $lTypeClass }}" @if($lTypeBg) style="background:{{ $lTypeBg }}"
                                    @endif>
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