<div class="container">
    <style>
        .wallet-negative {
            color: #fff;
        }

        /* Hide details by default */
        .store_details_column {
            display: none;
        }
    </style>
    <section class="admin__title">
        <h5>Cashbook</h5>
    </section>
    <section>
        <ul class="breadcrumb_menu">
            <li>Accounting</li>
            <li><a href=""> Cashbook</a></li>
            <li class="back-button"></li>
        </ul>
    </section>
    <div class="search__filter">
        <div class="row justify-content-end">
            <div class="col-auto">
                <div class="d-flex align-items-center gap-2">
                    @php
                        $user = Auth::guard('admin')->user();
                        $isAuthorized = $user && ($user->is_super_admin == 1 || in_array($user->designation, [1, 17]));
                    @endphp
                    @if ($isAuthorized)
                    <div class="mb-4">
                        <label for="searchStaff" class="form-label mb-0">Branch</label>
                        <div class="d-flex gap-2">
                            <!-- LEFT SELECT BOX -->
                            <select wire:model="staff_branch" wire:change="selectBranch" class="form-select select-md bg-white" style="width: 120px;">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                 <option value="{{$branch->id}}">{{$branch->name}}</option>
                                @endforeach
                            </select>
                            <div class="position-relative w-100">
                                <input type="text" wire:model.debounce.300ms="searchStaff"
                                    class="form-control select-md bg-white" placeholder="Staff name"
                                    wire:keyup="SearchStaff($event.target.value)">

                                @if (!empty($staffSuggestions))
                                <ul class="list-group position-absolute z-index-1 w-100"
                                    style="max-height: 200px; overflow-y:auto;">
                                    @forelse ($staffSuggestions as $staff)
                                    <li class="list-group-item list-group-item-action" style="cursor: pointer;"
                                        wire:click="selectStaff({{ $staff->id }}, '{{ $staff->name }}')">
                                        {{ $staff->name }}
                                    </li>
                                    @empty
                                    <li class="list-group-item text-muted">No results found</li>
                                    @endforelse
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    {{-- Start Date --}}
                    <div class="mb-4">
                        <label for="start_date" class="form-label mb-1">Start Date</label>
                        <input type="date" wire:model="start_date" id="start_date"
                            wire:change="AddStartDate($event.target.value)" class="form-control select-md bg-white">
                    </div>
                    <!-- End Date -->
                    <div class="mb-4">
                        <label for="end_date" class="form-label mb-1">End Date</label>
                        <input type="date" wire:model="end_date" id="end_date" class="form-control select-md bg-white"
                            wire:change="AddEndDate($event.target.value)">
                    </div>
                    {{-- Clear Button --}}
                    <div class="mb-0">
                        <label class="form-label mb-1 d-block">&nbsp;</label> <!-- invisible label to align button -->
                        <a href="{{ route('admin.accounting.cashbook_module') }}"
                            class="btn btn-outline-danger select-md">Clear</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="filter">
        <div class="row align-items-center justify-content-end">
            <div class="col-auto">
                {{-- <p class="text-sm font-weight-bold">{{$total}} Items</p> --}}
            </div>
        </div>
    </div>
    {{-- <div class="card"> --}}
        {{-- <div class="card-body"> --}}
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
            <div class="row mb-3">
                <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-first gradient-style-second">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"
                                    class="">
                                    <g>
                                        <path
                                            d="M262.75 235.08c1.69 1.77 2.53 4.22 2.53 7.34s-.84 5.55-2.51 7.29-4.16 2.61-7.47 2.61h-11.01v-19.89h11.01c3.27 0 5.76.89 7.44 2.66zm32.38 55.44v.75c0 5.52-4.48 10-10 10h-18.65c-3.79 0-7.25-2.14-8.95-5.54l-3.23-6.47v2.01c0 5.52-4.48 10-10 10h-17.41c-5.52 0-10-4.48-10-10v-72.24c0-5.52 4.48-10 10-10h28.43c10.82 0 19.66 2.64 26.28 7.84 7.24 5.7 11.06 13.98 11.06 23.97 0 6.88-1.53 12.84-4.54 17.73a28.378 28.378 0 0 1-5.09 6.11l10.99 21.25c.73 1.42 1.12 2.99 1.12 4.59zm-10 0-15.08-29.17c4.33-2.05 7.52-4.72 9.55-8.03s3.05-7.47 3.05-12.49c0-6.94-2.41-12.31-7.24-16.11s-11.53-5.7-20.09-5.7h-28.43v72.24h17.41v-25.55h9.43l12.75 25.55h18.65zm226.88-164.24v259.45c0 8.16-6.64 14.8-14.8 14.8H300.86L193.62 507.77c-2.72 2.72-6.35 4.22-10.2 4.22s-7.48-1.5-10.2-4.22L65.98 400.53H14.8c-8.16 0-14.8-6.64-14.8-14.8V126.27c0-8.16 6.64-14.8 14.8-14.8h196.35L318.4 4.23C321.12 1.51 324.75 0 328.6 0s7.48 1.5 10.2 4.23l107.24 107.24h51.15c8.16 0 14.8 6.64 14.8 14.8zm-286.7-14.8h23.41l72.82-72.82c1.95-1.95 4.51-2.93 7.07-2.93s5.12.98 7.07 2.93l72.82 72.82h23.41L331.73 11.3a4.424 4.424 0 0 0-6.26 0L225.3 111.47zm169.05 0-65.75-65.75-65.75 65.75h10.89l19.85-19.85c2.72-2.72 6.34-4.22 10.19-4.22 4.52 0 8.84 2.16 11.55 5.78.66.88 1.41 1.7 2.24 2.44 2.99 2.69 6.9 4.17 11.02 4.17 4.25 0 8.26-1.56 11.3-4.4.73-.68 1.39-1.42 1.97-2.2 2.81-3.75 7.02-5.89 11.55-5.89 3.78 0 7.38 1.51 10.13 4.26l19.92 19.92h10.89zm-37.88-12.85c-2-2-4.91-1.7-6.6.55-.93 1.24-1.98 2.41-3.15 3.51-9.96 9.32-25.68 9.49-35.83.37-1.34-1.2-2.52-2.5-3.56-3.88a4.426 4.426 0 0 0-6.67-.49l-12.78 12.78h81.44l-12.85-12.85zm-69.76 301.92h-23.41l-72.82 72.82c-1.88 1.88-4.42 2.93-7.07 2.93s-5.2-1.05-7.07-2.93l-72.82-72.82H80.12l100.17 100.17a4.424 4.424 0 0 0 6.26 0zm-169.05 0 65.75 65.75 65.75-65.75h-10.89l-19.85 19.85a14.319 14.319 0 0 1-10.19 4.22c-4.52 0-8.84-2.16-11.55-5.78-.65-.87-1.41-1.69-2.24-2.44-2.99-2.69-6.9-4.17-11.01-4.17s-8.26 1.56-11.3 4.4c-.73.68-1.39 1.42-1.97 2.2-2.73 3.64-7.06 5.82-11.58 5.82a14.2 14.2 0 0 1-10.1-4.19l-19.92-19.92h-10.89zm37.89 12.85c1.9 1.9 4.99 1.59 6.6-.55a26.57 26.57 0 0 1 3.15-3.51c9.96-9.32 25.68-9.49 35.83-.38 1.34 1.2 2.52 2.5 3.56 3.88a4.426 4.426 0 0 0 6.67.49l12.78-12.78h-81.43l12.85 12.85zM502 126.27c0-2.65-2.15-4.8-4.8-4.8H14.8c-2.65 0-4.8 2.15-4.8 4.8v259.46c0 2.65 2.15 4.8 4.8 4.8h482.4c2.65 0 4.8-2.15 4.8-4.8zM52.53 310.25c-2.89-2.78-4.48-6.53-4.48-10.55v-87.39c0-8.1 6.63-14.69 14.78-14.69.71 0 1.43.05 2.13.15.88.13 1.79.19 2.7.19.22 0 .44 0 .66-.01 9.66-.32 17.62-8.12 18.14-17.76.07-1.26.01-2.53-.16-3.77-.61-4.25.66-8.56 3.47-11.81 2.81-3.24 6.88-5.1 11.17-5.1h310.12c4.29 0 8.36 1.86 11.17 5.1 2.82 3.25 4.08 7.55 3.47 11.81-.17 1.23-.23 2.49-.16 3.77.52 9.63 8.48 17.43 18.14 17.75.22 0 .44.01.66.01.91 0 1.82-.06 2.7-.19.74-.11 1.49-.16 2.23-.16 8.23 0 14.68 6.46 14.68 14.7v87.39c0 8.1-6.63 14.69-14.78 14.69-.71 0-1.43-.05-2.14-.15-.88-.13-1.79-.19-2.7-.19-.22 0-.44 0-.66.01-9.65.32-17.62 8.12-18.14 17.76-.07 1.27-.01 2.53.16 3.76.61 4.25-.66 8.56-3.47 11.81-2.81 3.24-6.88 5.1-11.17 5.1H100.94c-4.29 0-8.36-1.86-11.17-5.1a14.795 14.795 0 0 1-3.47-11.81c.17-1.23.23-2.5.16-3.76-.52-9.63-8.49-17.43-18.14-17.76-.22 0-.44-.01-.66-.01-.91 0-1.82.06-2.7.19-.71.1-1.42.15-2.14.15-3.87 0-7.52-1.47-10.3-4.14zm5.52-10.55c0 2.91 2.61 5.06 5.49 4.64 1.66-.24 3.37-.33 5.11-.28 14.8.49 27 12.43 27.79 27.22.1 1.95.02 3.86-.25 5.71-.41 2.9 1.82 5.49 4.75 5.49h310.12c2.93 0 5.16-2.59 4.75-5.49-.26-1.85-.35-3.76-.25-5.71.79-14.79 12.99-26.72 27.79-27.22 1.74-.06 3.45.04 5.11.28 2.89.41 5.49-1.73 5.49-4.64v-87.39c0-3.07-2.46-5.08-5.49-4.64-1.66.24-3.37.33-5.11.28-14.8-.49-27-12.43-27.79-27.22-.1-1.95-.02-3.86.25-5.71.41-2.9-1.82-5.49-4.75-5.49H100.94c-2.93 0-5.16 2.59-4.75 5.49.26 1.85.35 3.76.25 5.71-.79 14.79-12.99 26.72-27.79 27.22-1.74.06-3.45-.04-5.11-.28-2.88-.41-5.49 1.73-5.49 4.64zM183.93 256c0-39.74 32.33-72.07 72.07-72.07s72.07 32.33 72.07 72.07-32.33 72.07-72.07 72.07-72.07-32.33-72.07-72.07zm10 0c0 34.28 27.79 62.07 62.07 62.07s62.07-27.79 62.07-62.07-27.79-62.07-62.07-62.07-62.07 27.79-62.07 62.07zm-96.96 0c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S96.97 269.26 96.97 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02-14.02 6.29-14.02 14.02zm376.21-105.7v211.41c0 5.52-4.48 10-10 10H38.83c-5.52 0-10-4.48-10-10V150.3c0-5.52 4.48-10 10-10h434.34c5.52 0 10 4.48 10 10zm-10 0H38.83v211.41h434.34V150.3zM367 256c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S367 269.26 367 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02S377 248.27 377 256z"
                                            fill="#075c36" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Collection</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totalCollections, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        {{-- <div class="card-footer p-3">

                        </div> --}}
                    </div>
                </div>
                {{-- Total Expenses --}}
                {{-- @if ($totalCollections > 0 || $totalExpenses > 0) --}}
                <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card  gradient-style-fourth">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 682.667 682.667" style="enable-background:new 0 0 512 512"
                                    xml:space="preserve" class="">
                                    <g>
                                        <defs>
                                            <clipPath id="a" clipPathUnits="userSpaceOnUse">
                                                <path d="M0 512h512V0H0Z" fill="#9b2214" opacity="1"
                                                    data-original="#000000"></path>
                                            </clipPath>
                                        </defs>
                                        <g clip-path="url(#a)" transform="matrix(1.33333 0 0 -1.33333 0 682.667)">
                                            <path d="M0 0h223.522c12.296 0 22.264 9.968 22.264 22.264v86.309"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(232.593 52.338)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0h-60.689c-12.296 0-22.264-9.968-22.264-22.264V-169.66"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(208.911 504.5)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0v287.147c0 12.296-9.968 22.264-22.264 22.264H-235.29"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(478.379 195.089)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M435.2 377.006H169.138v73.718H435.2Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                fill="none" stroke="#9b2214" stroke-width="15" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-miterlimit="10" stroke-dasharray="none"
                                                stroke-opacity="" data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0a14.318 14.318 0 0 0 12.391 7.131h18.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.326-14.326-14.326h-12.57"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(244.538 321.791)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(347.41 300.27)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(419.32 300.27)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h14.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.326-14.326-14.326H-4c-7.912 0-14.326 6.414-14.326 14.326"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(260.929 255.996)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(347.41 227.343)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.414-14.326 14.326 0 7.912 6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.414 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(419.32 227.343)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0v0c0 7.912 6.414 14.326 14.326 14.326h18.57c7.913 0 14.327-6.414 14.327-14.326 0-7.912-6.414-14.327-14.327-14.327h-16.57"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(242.602 183.069)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(347.41 168.742)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(419.32 168.742)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h12.57c7.912 0 14.326-6.414 14.326-14.326 0-7.912-6.414-14.327-14.326-14.327H-4"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(262.929 138.794)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(347.41 110.142)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0h-18.57c-7.912 0-14.326 6.415-14.326 14.327s6.414 14.326 14.326 14.326H0c7.912 0 14.326-6.414 14.326-14.326C14.326 6.415 7.912 0 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(419.32 110.142)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0v-41.878c0-15.808 34.533-28.977 80.291-31.901"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(33.62 81.886)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0c47.836 2.361 84.504 15.852 84.504 32.141v41.878"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(148.089 7.867)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0c3.864-3.469 5.972-7.213 5.972-11.119 0-17.953-44.542-32.507-99.487-32.507-54.944 0-99.486 14.554-99.486 32.507 0 9.681 12.95 18.373 33.498 24.328"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(226.622 93.004)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0v-41.878c0-17.954 44.542-32.507 99.486-32.507 54.945 0 99.487 14.553 99.487 32.507V0"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(61.892 160.733)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0c20.773-5.958 33.886-14.698 33.886-24.44 0-17.953-44.542-32.507-99.486-32.507-54.945 0-99.486 14.554-99.486 32.507 0 3.962 2.169 7.759 6.141 11.27"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(226.978 185.173)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0v-41.878c0-17.954 44.542-32.508 99.486-32.508 54.945 0 99.487 14.554 99.487 32.508v38.554"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(33.62 239.58)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0c-5.095-16.392-47.462-29.184-98.973-29.184-54.944 0-99.486 14.554-99.486 32.508 0 8.343 9.618 15.951 25.429 21.707"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(232.08 236.257)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0v-41.878c0-17.954 44.542-32.508 99.486-32.508 54.945 0 99.487 14.554 99.487 32.508V0"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(61.892 302.332)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path
                                                d="M0 0c0-17.953-44.542-32.507-99.486-32.507-54.945 0-99.487 14.554-99.487 32.507 0 17.954 44.542 32.508 99.487 32.508C-44.542 32.508 0 17.954 0 0Z"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(260.864 302.332)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0h25.358"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(68.814 356.426)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0h25.358"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(68.814 379.666)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                            <path d="M0 0h-.1"
                                                style="stroke-width:15;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10;stroke-dasharray:none;stroke-opacity:1"
                                                transform="translate(94.222 405.45)" fill="none" stroke="#9b2214"
                                                stroke-width="15" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-miterlimit="10" stroke-dasharray="none" stroke-opacity=""
                                                data-original="#000000" opacity="1" class=""></path>
                                        </g>
                                    </g>
                                </svg>
                            </div>

                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Expenses</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totalExpenses, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        {{-- <div class="card-footer p-3 d-flex justify-content-between align-items-center">
                            <p class="mb-0"><span class="text-danger text-sm font-weight-bolder">-2%</span>
                                than
                                yesterday</p>
                        </div> --}}
                    </div>
                </div>

                <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-third">
                        <div class="card-header p-3 pt-2">

                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"
                                    class="">
                                    <g>
                                        <path
                                            d="M487.59 299.894h-7.662v-69.015c0-10.926-5.438-20.6-13.743-26.478v-9.397c0-13.631-11.089-24.72-24.72-24.72h-5.721l21.889-32.12c7.337-10.766 4.547-25.495-6.219-32.833L302.883 4.111C297.668.556 291.382-.756 285.178.421a23.474 23.474 0 0 0-15.128 9.91L141.849 198.454h-35.675l8.607-87.13c.097-.982.735-1.833 1.625-2.168 11.089-4.181 20.699-11.989 27.059-21.985.501-.787 1.446-1.237 2.424-1.138l55.955 5.528a7.295 7.295 0 1 0 1.434-14.52l-55.955-5.528c-6.495-.636-12.689 2.357-16.169 7.825-4.676 7.35-11.742 13.09-19.895 16.164-6.137 2.314-10.351 7.826-10.999 14.387l-8.748 88.564h-9.303L95.821 60.649c.49-4.96 4.917-8.604 9.883-8.107l113.074 11.17a7.295 7.295 0 1 0 1.434-14.52l-113.074-11.17C94.174 36.743 82.581 46.248 81.3 59.214l-10.972 111.07H51.643c-13.63 0-24.72 11.089-24.72 24.72v6.41c-11.151 5.134-18.915 16.406-18.915 29.465v248.695c0 17.88 14.546 32.426 32.427 32.426h407.067c17.88 0 32.426-14.546 32.426-32.426V410.56h7.662c9.044 0 16.401-7.357 16.401-16.4v-77.865c.001-9.044-7.357-16.401-16.401-16.401zm-46.125-115.018c5.586-.001 10.129 4.543 10.129 10.129v3.717a32.512 32.512 0 0 0-4.092-.267h-30.955l9.253-13.579h15.665zM282.107 18.547a8.984 8.984 0 0 1 5.787-3.791 8.98 8.98 0 0 1 6.772 1.411l148.531 101.22c4.118 2.807 5.186 8.441 2.38 12.559l-46.686 68.506h-11.204l22.889-33.587c3.712-5.447 4.018-12.379.796-18.092-4.282-7.59-6.018-16.527-4.888-25.166.84-6.427-1.988-12.701-7.38-16.375L322.106 52.76c-5.393-3.675-12.267-4.012-17.941-.879-7.625 4.21-16.578 5.863-25.208 4.655-6.4-.898-12.891 1.992-16.548 7.357l-91.699 134.56h-11.204L282.107 18.547zm3.213 123.891c-31.771 0-57.851 24.773-59.976 56.015h-36.979L274.464 72.11h.001a2.605 2.605 0 0 1 2.133-1.147c.111 0 .222.008.333.023 11.738 1.642 23.913-.604 34.285-6.331.816-.452 1.864-.387 2.672.163l76.999 52.473c.806.55 1.249 1.502 1.129 2.426-1.537 11.749.825 23.903 6.648 34.225.467.829.41 1.891-.144 2.706l-28.488 41.804h-24.733c-2.127-31.241-28.206-56.014-59.979-56.014zm45.348 56.016h-90.695c2.09-23.189 21.624-41.424 45.347-41.424 23.724-.001 43.259 18.235 45.348 41.424zm-289.154-3.45c0-5.586 4.543-10.13 10.129-10.13h17.244l-1.341 13.579H41.514v-3.449zm-18.915 35.875c0-9.834 8.001-17.835 17.836-17.835h407.067c9.835 0 17.835 8.001 17.835 17.835v20.244H22.599v-20.244zm442.738 248.695c0 9.834-8 17.835-17.835 17.835H40.435c-9.835 0-17.836-8.001-17.836-17.835V459.33h39.342a7.295 7.295 0 1 0 0-14.59H22.599V265.714h442.738v34.18H395.97c-21.856 0-40.788 12.74-49.773 31.181-.03.062-.064.122-.095.184-.076.157-.142.32-.216.478-1.07 2.273-2 4.626-2.757 7.054l-.006.02a55.151 55.151 0 0 0-2.486 16.416c0 30.511 24.822 55.333 55.332 55.333h69.367v34.18H108.655a7.295 7.295 0 1 0 0 14.59h356.682v20.244zm24.064-85.415c0 .998-.812 1.81-1.811 1.81h-91.618c-15.445 0-28.911-8.639-35.818-21.338a40.21 40.21 0 0 1-2.448-5.41c-1.602-4.366-2.476-9.08-2.476-13.994a40.57 40.57 0 0 1 4.023-17.648c.287-.593.587-1.179.901-1.756 6.907-12.699 20.373-21.338 35.818-21.338h91.618c.998 0 1.811.812 1.811 1.81v77.864z"
                                            fill="#a36400" opacity="1" data-original="#000000" class=""></path>
                                        <path
                                            d="M397.681 325.627c-16.322 0-29.6 13.279-29.6 29.6s13.278 29.599 29.6 29.599c16.322 0 29.6-13.278 29.6-29.599 0-16.321-13.278-29.6-29.6-29.6zm0 44.608c-8.276 0-15.009-6.733-15.009-15.008 0-8.275 6.733-15.009 15.009-15.009s15.009 6.733 15.009 15.009-6.733 15.008-15.009 15.008z"
                                            fill="#a36400" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>

                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Wallet</h2>

                                <h3
                                    class="mb-0 dashboard-counter {{ $totalWallet < 0 ? 'wallet-negative' : 'dark-text' }}">
                                    {{ number_format($totalWallet, 2) }}
                                </h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-fifth">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"
                                    class="">
                                    <g>
                                        <path
                                            d="M262.75 235.08c1.69 1.77 2.53 4.22 2.53 7.34s-.84 5.55-2.51 7.29-4.16 2.61-7.47 2.61h-11.01v-19.89h11.01c3.27 0 5.76.89 7.44 2.66zm32.38 55.44v.75c0 5.52-4.48 10-10 10h-18.65c-3.79 0-7.25-2.14-8.95-5.54l-3.23-6.47v2.01c0 5.52-4.48 10-10 10h-17.41c-5.52 0-10-4.48-10-10v-72.24c0-5.52 4.48-10 10-10h28.43c10.82 0 19.66 2.64 26.28 7.84 7.24 5.7 11.06 13.98 11.06 23.97 0 6.88-1.53 12.84-4.54 17.73a28.378 28.378 0 0 1-5.09 6.11l10.99 21.25c.73 1.42 1.12 2.99 1.12 4.59zm-10 0-15.08-29.17c4.33-2.05 7.52-4.72 9.55-8.03s3.05-7.47 3.05-12.49c0-6.94-2.41-12.31-7.24-16.11s-11.53-5.7-20.09-5.7h-28.43v72.24h17.41v-25.55h9.43l12.75 25.55h18.65zm226.88-164.24v259.45c0 8.16-6.64 14.8-14.8 14.8H300.86L193.62 507.77c-2.72 2.72-6.35 4.22-10.2 4.22s-7.48-1.5-10.2-4.22L65.98 400.53H14.8c-8.16 0-14.8-6.64-14.8-14.8V126.27c0-8.16 6.64-14.8 14.8-14.8h196.35L318.4 4.23C321.12 1.51 324.75 0 328.6 0s7.48 1.5 10.2 4.23l107.24 107.24h51.15c8.16 0 14.8 6.64 14.8 14.8zm-286.7-14.8h23.41l72.82-72.82c1.95-1.95 4.51-2.93 7.07-2.93s5.12.98 7.07 2.93l72.82 72.82h23.41L331.73 11.3a4.424 4.424 0 0 0-6.26 0L225.3 111.47zm169.05 0-65.75-65.75-65.75 65.75h10.89l19.85-19.85c2.72-2.72 6.34-4.22 10.19-4.22 4.52 0 8.84 2.16 11.55 5.78.66.88 1.41 1.7 2.24 2.44 2.99 2.69 6.9 4.17 11.02 4.17 4.25 0 8.26-1.56 11.3-4.4.73-.68 1.39-1.42 1.97-2.2 2.81-3.75 7.02-5.89 11.55-5.89 3.78 0 7.38 1.51 10.13 4.26l19.92 19.92h10.89zm-37.88-12.85c-2-2-4.91-1.7-6.6.55-.93 1.24-1.98 2.41-3.15 3.51-9.96 9.32-25.68 9.49-35.83.37-1.34-1.2-2.52-2.5-3.56-3.88a4.426 4.426 0 0 0-6.67-.49l-12.78 12.78h81.44l-12.85-12.85zm-69.76 301.92h-23.41l-72.82 72.82c-1.88 1.88-4.42 2.93-7.07 2.93s-5.2-1.05-7.07-2.93l-72.82-72.82H80.12l100.17 100.17a4.424 4.424 0 0 0 6.26 0zm-169.05 0 65.75 65.75 65.75-65.75h-10.89l-19.85 19.85a14.319 14.319 0 0 1-10.19 4.22c-4.52 0-8.84-2.16-11.55-5.78-.65-.87-1.41-1.69-2.24-2.44-2.99-2.69-6.9-4.17-11.01-4.17s-8.26 1.56-11.3 4.4c-.73.68-1.39 1.42-1.97 2.2-2.73 3.64-7.06 5.82-11.58 5.82a14.2 14.2 0 0 1-10.1-4.19l-19.92-19.92h-10.89zm37.89 12.85c1.9 1.9 4.99 1.59 6.6-.55a26.57 26.57 0 0 1 3.15-3.51c9.96-9.32 25.68-9.49 35.83-.38 1.34 1.2 2.52 2.5 3.56 3.88a4.426 4.426 0 0 0 6.67.49l12.78-12.78h-81.43l12.85 12.85zM502 126.27c0-2.65-2.15-4.8-4.8-4.8H14.8c-2.65 0-4.8 2.15-4.8 4.8v259.46c0 2.65 2.15 4.8 4.8 4.8h482.4c2.65 0 4.8-2.15 4.8-4.8zM52.53 310.25c-2.89-2.78-4.48-6.53-4.48-10.55v-87.39c0-8.1 6.63-14.69 14.78-14.69.71 0 1.43.05 2.13.15.88.13 1.79.19 2.7.19.22 0 .44 0 .66-.01 9.66-.32 17.62-8.12 18.14-17.76.07-1.26.01-2.53-.16-3.77-.61-4.25.66-8.56 3.47-11.81 2.81-3.24 6.88-5.1 11.17-5.1h310.12c4.29 0 8.36 1.86 11.17 5.1 2.82 3.25 4.08 7.55 3.47 11.81-.17 1.23-.23 2.49-.16 3.77.52 9.63 8.48 17.43 18.14 17.75.22 0 .44.01.66.01.91 0 1.82-.06 2.7-.19.74-.11 1.49-.16 2.23-.16 8.23 0 14.68 6.46 14.68 14.7v87.39c0 8.1-6.63 14.69-14.78 14.69-.71 0-1.43-.05-2.14-.15-.88-.13-1.79-.19-2.7-.19-.22 0-.44 0-.66.01-9.65.32-17.62 8.12-18.14 17.76-.07 1.27-.01 2.53.16 3.76.61 4.25-.66 8.56-3.47 11.81-2.81 3.24-6.88 5.1-11.17 5.1H100.94c-4.29 0-8.36-1.86-11.17-5.1a14.795 14.795 0 0 1-3.47-11.81c.17-1.23.23-2.5.16-3.76-.52-9.63-8.49-17.43-18.14-17.76-.22 0-.44-.01-.66-.01-.91 0-1.82.06-2.7.19-.71.1-1.42.15-2.14.15-3.87 0-7.52-1.47-10.3-4.14zm5.52-10.55c0 2.91 2.61 5.06 5.49 4.64 1.66-.24 3.37-.33 5.11-.28 14.8.49 27 12.43 27.79 27.22.1 1.95.02 3.86-.25 5.71-.41 2.9 1.82 5.49 4.75 5.49h310.12c2.93 0 5.16-2.59 4.75-5.49-.26-1.85-.35-3.76-.25-5.71.79-14.79 12.99-26.72 27.79-27.22 1.74-.06 3.45.04 5.11.28 2.89.41 5.49-1.73 5.49-4.64v-87.39c0-3.07-2.46-5.08-5.49-4.64-1.66.24-3.37.33-5.11.28-14.8-.49-27-12.43-27.79-27.22-.1-1.95-.02-3.86.25-5.71.41-2.9-1.82-5.49-4.75-5.49H100.94c-2.93 0-5.16 2.59-4.75 5.49.26 1.85.35 3.76.25 5.71-.79 14.79-12.99 26.72-27.79 27.22-1.74.06-3.45-.04-5.11-.28-2.88-.41-5.49 1.73-5.49 4.64zM183.93 256c0-39.74 32.33-72.07 72.07-72.07s72.07 32.33 72.07 72.07-32.33 72.07-72.07 72.07-72.07-32.33-72.07-72.07zm10 0c0 34.28 27.79 62.07 62.07 62.07s62.07-27.79 62.07-62.07-27.79-62.07-62.07-62.07-62.07 27.79-62.07 62.07zm-96.96 0c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S96.97 269.26 96.97 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02-14.02 6.29-14.02 14.02zm376.21-105.7v211.41c0 5.52-4.48 10-10 10H38.83c-5.52 0-10-4.48-10-10V150.3c0-5.52 4.48-10 10-10h434.34c5.52 0 10 4.48 10 10zm-10 0H38.83v211.41h434.34V150.3zM367 256c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S367 269.26 367 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02S377 248.27 377 256z"
                                            fill="#ffffff" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Cash</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totalcashCollections, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        {{-- <div class="card-footer p-3">

                        </div> --}}
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-sixth">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"
                                    class="">
                                    <g>
                                        <path
                                            d="M262.75 235.08c1.69 1.77 2.53 4.22 2.53 7.34s-.84 5.55-2.51 7.29-4.16 2.61-7.47 2.61h-11.01v-19.89h11.01c3.27 0 5.76.89 7.44 2.66zm32.38 55.44v.75c0 5.52-4.48 10-10 10h-18.65c-3.79 0-7.25-2.14-8.95-5.54l-3.23-6.47v2.01c0 5.52-4.48 10-10 10h-17.41c-5.52 0-10-4.48-10-10v-72.24c0-5.52 4.48-10 10-10h28.43c10.82 0 19.66 2.64 26.28 7.84 7.24 5.7 11.06 13.98 11.06 23.97 0 6.88-1.53 12.84-4.54 17.73a28.378 28.378 0 0 1-5.09 6.11l10.99 21.25c.73 1.42 1.12 2.99 1.12 4.59zm-10 0-15.08-29.17c4.33-2.05 7.52-4.72 9.55-8.03s3.05-7.47 3.05-12.49c0-6.94-2.41-12.31-7.24-16.11s-11.53-5.7-20.09-5.7h-28.43v72.24h17.41v-25.55h9.43l12.75 25.55h18.65zm226.88-164.24v259.45c0 8.16-6.64 14.8-14.8 14.8H300.86L193.62 507.77c-2.72 2.72-6.35 4.22-10.2 4.22s-7.48-1.5-10.2-4.22L65.98 400.53H14.8c-8.16 0-14.8-6.64-14.8-14.8V126.27c0-8.16 6.64-14.8 14.8-14.8h196.35L318.4 4.23C321.12 1.51 324.75 0 328.6 0s7.48 1.5 10.2 4.23l107.24 107.24h51.15c8.16 0 14.8 6.64 14.8 14.8zm-286.7-14.8h23.41l72.82-72.82c1.95-1.95 4.51-2.93 7.07-2.93s5.12.98 7.07 2.93l72.82 72.82h23.41L331.73 11.3a4.424 4.424 0 0 0-6.26 0L225.3 111.47zm169.05 0-65.75-65.75-65.75 65.75h10.89l19.85-19.85c2.72-2.72 6.34-4.22 10.19-4.22 4.52 0 8.84 2.16 11.55 5.78.66.88 1.41 1.7 2.24 2.44 2.99 2.69 6.9 4.17 11.02 4.17 4.25 0 8.26-1.56 11.3-4.4.73-.68 1.39-1.42 1.97-2.2 2.81-3.75 7.02-5.89 11.55-5.89 3.78 0 7.38 1.51 10.13 4.26l19.92 19.92h10.89zm-37.88-12.85c-2-2-4.91-1.7-6.6.55-.93 1.24-1.98 2.41-3.15 3.51-9.96 9.32-25.68 9.49-35.83.37-1.34-1.2-2.52-2.5-3.56-3.88a4.426 4.426 0 0 0-6.67-.49l-12.78 12.78h81.44l-12.85-12.85zm-69.76 301.92h-23.41l-72.82 72.82c-1.88 1.88-4.42 2.93-7.07 2.93s-5.2-1.05-7.07-2.93l-72.82-72.82H80.12l100.17 100.17a4.424 4.424 0 0 0 6.26 0zm-169.05 0 65.75 65.75 65.75-65.75h-10.89l-19.85 19.85a14.319 14.319 0 0 1-10.19 4.22c-4.52 0-8.84-2.16-11.55-5.78-.65-.87-1.41-1.69-2.24-2.44-2.99-2.69-6.9-4.17-11.01-4.17s-8.26 1.56-11.3 4.4c-.73.68-1.39 1.42-1.97 2.2-2.73 3.64-7.06 5.82-11.58 5.82a14.2 14.2 0 0 1-10.1-4.19l-19.92-19.92h-10.89zm37.89 12.85c1.9 1.9 4.99 1.59 6.6-.55a26.57 26.57 0 0 1 3.15-3.51c9.96-9.32 25.68-9.49 35.83-.38 1.34 1.2 2.52 2.5 3.56 3.88a4.426 4.426 0 0 0 6.67.49l12.78-12.78h-81.43l12.85 12.85zM502 126.27c0-2.65-2.15-4.8-4.8-4.8H14.8c-2.65 0-4.8 2.15-4.8 4.8v259.46c0 2.65 2.15 4.8 4.8 4.8h482.4c2.65 0 4.8-2.15 4.8-4.8zM52.53 310.25c-2.89-2.78-4.48-6.53-4.48-10.55v-87.39c0-8.1 6.63-14.69 14.78-14.69.71 0 1.43.05 2.13.15.88.13 1.79.19 2.7.19.22 0 .44 0 .66-.01 9.66-.32 17.62-8.12 18.14-17.76.07-1.26.01-2.53-.16-3.77-.61-4.25.66-8.56 3.47-11.81 2.81-3.24 6.88-5.1 11.17-5.1h310.12c4.29 0 8.36 1.86 11.17 5.1 2.82 3.25 4.08 7.55 3.47 11.81-.17 1.23-.23 2.49-.16 3.77.52 9.63 8.48 17.43 18.14 17.75.22 0 .44.01.66.01.91 0 1.82-.06 2.7-.19.74-.11 1.49-.16 2.23-.16 8.23 0 14.68 6.46 14.68 14.7v87.39c0 8.1-6.63 14.69-14.78 14.69-.71 0-1.43-.05-2.14-.15-.88-.13-1.79-.19-2.7-.19-.22 0-.44 0-.66.01-9.65.32-17.62 8.12-18.14 17.76-.07 1.27-.01 2.53.16 3.76.61 4.25-.66 8.56-3.47 11.81-2.81 3.24-6.88 5.1-11.17 5.1H100.94c-4.29 0-8.36-1.86-11.17-5.1a14.795 14.795 0 0 1-3.47-11.81c.17-1.23.23-2.5.16-3.76-.52-9.63-8.49-17.43-18.14-17.76-.22 0-.44-.01-.66-.01-.91 0-1.82.06-2.7.19-.71.1-1.42.15-2.14.15-3.87 0-7.52-1.47-10.3-4.14zm5.52-10.55c0 2.91 2.61 5.06 5.49 4.64 1.66-.24 3.37-.33 5.11-.28 14.8.49 27 12.43 27.79 27.22.1 1.95.02 3.86-.25 5.71-.41 2.9 1.82 5.49 4.75 5.49h310.12c2.93 0 5.16-2.59 4.75-5.49-.26-1.85-.35-3.76-.25-5.71.79-14.79 12.99-26.72 27.79-27.22 1.74-.06 3.45.04 5.11.28 2.89.41 5.49-1.73 5.49-4.64v-87.39c0-3.07-2.46-5.08-5.49-4.64-1.66.24-3.37.33-5.11.28-14.8-.49-27-12.43-27.79-27.22-.1-1.95-.02-3.86.25-5.71.41-2.9-1.82-5.49-4.75-5.49H100.94c-2.93 0-5.16 2.59-4.75 5.49.26 1.85.35 3.76.25 5.71-.79 14.79-12.99 26.72-27.79 27.22-1.74.06-3.45-.04-5.11-.28-2.88-.41-5.49 1.73-5.49 4.64zM183.93 256c0-39.74 32.33-72.07 72.07-72.07s72.07 32.33 72.07 72.07-32.33 72.07-72.07 72.07-72.07-32.33-72.07-72.07zm10 0c0 34.28 27.79 62.07 62.07 62.07s62.07-27.79 62.07-62.07-27.79-62.07-62.07-62.07-62.07 27.79-62.07 62.07zm-96.96 0c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S96.97 269.26 96.97 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02-14.02 6.29-14.02 14.02zm376.21-105.7v211.41c0 5.52-4.48 10-10 10H38.83c-5.52 0-10-4.48-10-10V150.3c0-5.52 4.48-10 10-10h434.34c5.52 0 10 4.48 10 10zm-10 0H38.83v211.41h434.34V150.3zM367 256c0-13.27 10.76-24.02 24.02-24.02s24.02 10.76 24.02 24.02-10.76 24.02-24.02 24.02S367 269.26 367 256zm10 0c0 7.73 6.29 14.02 14.02 14.02s14.02-6.29 14.02-14.02-6.29-14.02-14.02-14.02S377 248.27 377 256z"
                                            fill="#ffffff" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total NEFT</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totalneftCollections, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">

                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-fourth">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 16.933 16.933" style="enable-background:new 0 0 512 512"
                                    xml:space="preserve" class="">
                                    <g>
                                        <path
                                            d="M13.493 1.027a.824.824 0 0 0-.584.24l-.517.518a.641.641 0 0 0-.672.145L6.956 6.692a.265.265 0 0 0-.066.115l-.53 1.852a.265.265 0 0 0 .327.326l1.851-.53a.264.264 0 0 0 .116-.066l4.575-4.575.046.045c.21.21.21.538 0 .749l-1.291 1.29c-.265.25.126.64.375.374l1.289-1.291a1.061 1.061 0 0 0 0-1.496l-.088-.088a.65.65 0 0 0 0-.444l.518-.517a.83.83 0 0 0 0-1.168.825.825 0 0 0-.585-.24zm.001.526c.075 0 .15.03.21.09.12.12.12.298 0 .417l-.475.476-.42-.42.476-.473c.06-.06.134-.09.21-.09zm-1.247.75.795.795c.046.046.046.108 0 .154L10.32 5.976l-.949-.949 2.723-2.724a.107.107 0 0 1 .154 0zM8.996 5.4l.949.949-1.478 1.478-.948-.949zM1.323 6.88a.798.798 0 0 0-.794.793v7.408a.8.8 0 0 0 .794.795H15.61c.436 0 .794-.36.794-.795V7.672a.797.797 0 0 0-.794-.793h-4.763c-.36-.008-.36.538 0 .53h4.763c.152 0 .264.112.264.263v7.408a.259.259 0 0 1-.264.266H1.323a.26.26 0 0 1-.265-.266V7.672c0-.151.114-.263.265-.263h4.499c.36.008.36-.538 0-.53zm5.952.506.686.687L7 8.346zM3.171 8.463a.265.265 0 0 0-.262.267v.304c-.453.12-.792.53-.792 1.019 0 .581.477 1.059 1.058 1.059.295 0 .53.233.53.529s-.235.529-.53.529a.525.525 0 0 1-.53-.53c.009-.36-.536-.36-.528 0 0 .49.339.902.792 1.021v.304c0 .353.53.353.53 0v-.303c.454-.119.795-.531.795-1.021 0-.582-.478-1.059-1.059-1.059-.295 0-.53-.234-.53-.53s.235-.526.53-.526c.295 0 .53.231.53.527 0 .353.529.353.529 0 0-.49-.34-.901-.795-1.02V8.73a.264.264 0 0 0-.268-.267zm6.883.797v1.058c0 .147.12.265.266.264h4.232a.265.265 0 0 0 .264-.264V9.26a.265.265 0 0 0-.264-.263h-4.246a.266.266 0 0 0-.252.263zm.53.266h3.705v.527h-3.706zm-5.028.527c-.345.008-.345.521 0 .529h3.176c.345-.008.345-.522 0-.53zm0 1.588c-.36-.008-.36.537 0 .529h6.879c.36.008.36-.537 0-.53zm7.938 0c-.36-.008-.36.537 0 .529h1.058c.36.008.36-.537 0-.53zm-1.016 1.566c-.285.015-.613.109-.973.325-.603.362-.934.283-1.156.16-.222-.124-.322-.317-.322-.317-.158-.317-.633-.08-.475.237 0 0 .166.335.54.542.373.208.967.262 1.687-.17.603-.362.934-.283 1.156-.16a.856.856 0 0 1 .322.317c.151.335.651.085.473-.237 0 0-.164-.335-.537-.542a1.327 1.327 0 0 0-.715-.155zm-10.387.551c-.344.024-.32.538.026.53h5.29c.346-.009.346-.522 0-.53H2.092z"
                                            fill="#ffffffff" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Cheque</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totalchequeCollections, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">

                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card data-card gradient-style-first">
                        <div class="card-header p-3 pt-2">
                            <div class="dash-big-icon position-absolute size-small">
                                <svg xmlns="http://www.w3.org/2000/svg" version="1.1"
                                    xmlns:xlink="http://www.w3.org/1999/xlink" width="512" height="512" x="0" y="0"
                                    viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve"
                                    class="">
                                    <g>
                                        <path
                                            d="M508.168 131.73 380.27 3.832A12.989 12.989 0 0 0 371.02 0a13.014 13.014 0 0 0-9.254 3.832L291.43 74.168a7.42 7.42 0 0 0 0 10.488 7.42 7.42 0 0 0 10.492 0L371.02 15.56 496.44 140.98 270.47 366.953l-26.332-26.328a92.165 92.165 0 0 0 3.129-3.723l4.043-5.113c2.562.7 5.363.426 7.851-.922 6.977-3.765 15.64-3.765 22.613 0 1.618.875 3.364 1.301 5.09 1.301a10.49 10.49 0 0 0 7.438-3.098l17.207-17.207a7.42 7.42 0 0 0 0-10.488 7.42 7.42 0 0 0-10.488 0l-15.051 15.047a38.498 38.498 0 0 0-20.164-2.969l5.242-6.625 33.484-33.484.004-.004 29.836-29.836a53.795 53.795 0 0 0 24.563-14.059c21.054-21.054 21.054-55.32-.004-76.379-21.055-21.054-55.317-21.054-76.375.004-14.043 14.043-19.106 34.907-13.532 53.676-6.222 3.766-11.882 8.05-16.515 12.684l-29.602 29.61c-1.902 1.905-4.457 2.952-7.187 2.952h-.242c-4.024 0-9.786-4.496-11.422-8.73v-25.707L355.515 66.09a38.426 38.426 0 0 0 31.008 0l59.387 59.387a38.457 38.457 0 0 0 0 31.007L325.82 276.57a7.424 7.424 0 0 0 0 10.492 7.41 7.41 0 0 0 5.246 2.172 7.41 7.41 0 0 0 5.246-2.172l122.247-122.25c3.324-3.328 4.046-8.363 1.796-12.527-3.77-6.973-3.77-15.637-.003-22.605 2.257-4.168 1.535-9.207-1.793-12.532l-63.704-63.703c-3.332-3.328-8.363-4.05-12.53-1.8-6.973 3.77-15.638 3.773-22.61 0-4.164-2.25-9.2-1.528-12.531 1.8L204.05 196.578v-14.05l72.785-72.786c2.012-2.012 2.695-5.004 1.754-7.695s-3.344-4.602-6.176-4.918c-12.379-1.387-24.55-2.387-34.336-3.188-6.707-.55-12.008-.984-15.168-1.367-9.742-1.164-19.238 2.192-29.02 10.262l-64.73 51.066c-.043.035-.09.07-.133.11-10.359 8.574-17.73 19.96-21.312 32.937-6.711 24.309-21.211 68.512-30.348 86.52l-31.535 31.539-1.176-1.18c-3.097-3.098-7.219-4.805-11.597-4.805s-8.5 1.707-11.598 4.805l-16.656 16.66A16.29 16.29 0 0 0 0 332.082c0 4.383 1.707 8.5 4.805 11.598l94.664 94.664a16.29 16.29 0 0 0 11.593 4.804c4.383 0 8.5-1.707 11.598-4.804l16.656-16.66c3.098-3.094 4.805-7.215 4.805-11.594s-1.707-8.5-4.8-11.598l-1.212-1.207 7.86-7.86a28.519 28.519 0 0 1 12.191-7.206c.102-.028.2-.059.3-.094l47.548-16.012a89.576 89.576 0 0 0 27.433-15.207l27.774 27.774c2.472 2.472 5.758 3.832 9.254 3.832s6.777-1.36 9.25-3.832l228.449-228.446A12.998 12.998 0 0 0 512 140.98c0-3.492-1.36-6.78-3.832-9.25zM299.289 257.605l-5.434-5.437a4.887 4.887 0 0 1 0-6.902l10.614-10.614a16.422 16.422 0 0 1 11.648-4.816c2.969 0 5.938.797 8.555 2.387zm-6.246-94.046c15.273-15.278 40.125-15.274 55.398-.004 14.266 14.265 15.204 36.879 2.829 52.25-.024-5.516-2.23-10.657-6.524-15.067-4.73-5.648-12.45-8.914-22.351-9.445-11.993-.652-26.438 2.652-39.864 8.492-2.832-12.879.996-26.71 10.512-36.226zM128.828 411.195l-16.656 16.66c-.379.38-.813.461-1.11.461-.292 0-.726-.082-1.105-.46L15.293 333.19c-.383-.379-.461-.812-.461-1.109s.078-.727.461-1.105l16.656-16.66c.38-.38.813-.462 1.11-.462s.726.082 1.105.461l94.664 94.664c.383.38.461.813.461 1.11s-.078.726-.46 1.105zm115.203-94.117-8.398 10.621a74.598 74.598 0 0 1-7.031 7.719c-.028.027-.059.05-.086.078l-.055.059a74.62 74.62 0 0 1-27.227 16.515l-47.367 15.95a43.412 43.412 0 0 0-18.387 10.918l-7.859 7.859-71.3-71.3 32.355-32.356a7.536 7.536 0 0 0 1.32-1.797c9.7-18.489 24.824-64.406 32.016-90.446 2.761-10.003 8.433-18.789 16.406-25.406l64.727-51.062c.043-.035.09-.07.132-.11 6.688-5.53 12.2-7.695 17.868-7.015 3.437.414 8.859.855 15.718 1.422 5.516.453 11.801.964 18.434 1.578l-63.91 63.91a7.416 7.416 0 0 0-2.172 5.242l.004 64.953c0 .645.086 1.29.254 1.91 2.914 10.926 15.066 20.508 26.004 20.508h.242c6.691 0 12.972-2.594 17.68-7.305l29.597-29.601c4.852-4.852 11.145-9.3 18.05-13.008.028-.012.056-.02.083-.031.316-.14.61-.305.894-.48 12.97-6.778 27.93-10.938 39.575-10.298 5.68.305 9.89 1.817 11.855 4.254.168.207.348.407.531.594 2.625 2.625 2.618 4.742 2.32 6.29-.234 1.198-.784 2.51-1.593 3.874-12.242-9.05-29.645-8.039-40.73 3.047l-10.614 10.613c-3.722 3.727-5.773 8.676-5.773 13.942s2.05 10.215 5.773 13.937l5.438 5.438-28.555 28.55a6.863 6.863 0 0 0-.57.645l-15.63 19.766c-.007.007-.015.015-.019.023zm0 0"
                                            fill="#ffffff" opacity="1" data-original="#000000" class=""></path>
                                    </g>
                                </svg>
                            </div>
                            <div class="text-end pt-1">
                                <h2 class="dashboard-heading mb-0">Total Digital Payment</h2>
                                <h3 class="mb-0 dashboard-counter">{{ number_format($totaldigitalCollections, 2) }}</h3>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">

                    </div>
                </div>

            </div>
            {{-- Add Payment Receipt button --}}
            <div class="d-flex justify-content-end mt-4">
                <a href="{{route('admin.accounting.add_payment_receipt')}}" class="btn btn-sm btn-success select-md">
                    <i class="material-icons">add</i> Add Payment Receipt
                </a>
            </div>
            {{-- Payment Collection table --}}
            <div class="card">
                <div class="card-header">
                    <h5>Payment Collection Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Voucher No</th>
                                    <th>Payment Date</th>
                                    <th>Collected By</th>
                                    <th>Customer</th>
                                    <th>Collection Amount</th>
                                    <th>Collected From</th>
                                    <th>Approval</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentCollections as $index => $collection)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $collection->voucher_no }}</td>
                                    <td>{{ \Carbon\Carbon::parse($collection->created_at)->format('d-m-Y') }}</td>
                                    <td>{{ optional($collection->user)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($collection->customer)->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($collection->collection_amount, 2) }}</td>
                                    <td>{{ $collection->payment_type }}</td>
                                    <td>
                                        @if($collection->is_ledger_added)
                                        <span class="badge bg-success">Approved</span>
                                        @endif

                                    </td>
                                    <td>
                                        @if (!empty($collection->is_ledger_added))
                                        <a href="#" wire:click="$dispatch('confirm-revoke',{{ $collection->id }})"
                                            class="btn btn-outline-warning select-md btn_outline">Revoke</a>
                                        @endif
                                        <button wire:click="downloadInvoice({{ $collection->id }})"
                                            class="btn btn-outline-primary select-md btn_outline">Download
                                            Receipt</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No collection records found in selected date
                                        range.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            {{-- {{ $paymentCollections->links() }} --}}
                        </nav>
                    </div>
                </div>
            </div>
            {{-- Add Expense button --}}
            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('admin.accounting.add_depot_expense') }}" class="btn btn-sm btn-success select-md"><i
                        class="material-icons">add</i>Add Expense</a>

                </a>
            </div>
            {{-- Expense table --}}
            <div class="card">
                <div class="card-header">
                    <h5>Expenses List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Expense Date</th>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $i = empty(Request::get('page')) || Request::get('page') == 1 ? 1 :
                                (((Request::get('page')-1)*$paginate)+1);
                                @endphp
                                @forelse ($paymentExpenses as $key=>$item)
                                @php
                                $ExpenseAt = "";
                                $ExpenseType = "";

                                $expenseData =($item->staff_id ? DB::table('users')->where('id',
                                $item->staff_id)->first() :
                                ($item->customer_id ? DB::table('users')->where('id', $item->customer_id)->first() :
                                ($item->supplier_id ? DB::table('suppliers')->where('id', $item->supplier_id)->first() :
                                null)));



                                $expenseType = $item->expense_id ? DB::table('expences')->where('id',
                                $item->expense_id)->first() : null;
                                $ExpenseType = $expenseType ? $expenseType->title : "";
                                @endphp
                                <tr class="store_details_row">
                                    <td>{{$i}}</td>
                                    <td>@if($item->payment_date){{date('d/m/Y', strtotime($item->payment_date))}}@endif
                                    </td>
                                    <td>{{ $item->voucher_no }}</td>
                                    <td>Rs. {{number_format((float)$item->amount, 2, '.', '')}} (
                                        {{ucwords($item->bank_cash)}} )</td>
                                    <td>
                                        <a href="{{ route('admin.accounting.edit_depot_expense', $item->id) }}"
                                            class="btn btn-outline-success select-md">Edit</a>
                                    </td>
                                </tr>
                                <tr>

                                    <td colspan="5" class="store_details_column">

                                        <div class="store_details">

                                            <table class="table">

                                                <tr>

                                                    <td><span>Amount: <strong>Rs. {{number_format((float)$item->amount,
                                                                2, '.', '')}}</strong></span></td>

                                                    @php
                                                    $expenseAt = '';

                                                    if ($item->stuff_id && $item->staff) {
                                                    $expenseAt = 'Staff Name: <strong>' . ucwords($item->staff->name)
                                                        .'</strong>';
                                                    } elseif ($item->customer_id && $item->customer) {
                                                    $expenseAt = 'Customer Name: <strong>' .
                                                        ucwords($item->customer->name) .'</strong>';
                                                    } elseif ($item->supplier_id && $item->supplier) {
                                                    $expenseAt = 'Supplier Name: <strong>' .
                                                        ucwords($item->supplier->name) .'</strong>';
                                                    }
                                                    @endphp

                                                    <td>{!! $expenseAt !!}</strong></span></td>


                                                    @if (!empty($item->payment_mode))

                                                    <td><span>Payment Mode: <strong>{{
                                                                ucwords($item->payment_mode)}}</strong></span></td>

                                                    @endif

                                                    @if (!empty($item->bank_name))

                                                    <td><span>Bank: <strong>{{
                                                                ucwords($item->bank_name)}}</strong></span></td>

                                                    @endif

                                                    @if (!empty($item->chq_utr_no))

                                                    <td><span>Cheque / UTR No: <strong>{{
                                                                ucwords($item->chq_utr_no)}}</strong></span></td>

                                                    @endif

                                                    @if (!empty($item->narration))

                                                    <td><span>Narration: <strong>{{
                                                                ucwords($item->narration)}}</strong></span></td>

                                                    @endif

                                                </tr>

                                                <tr>

                                                    @if (!empty($item->created_by))

                                                    <td><span>Created By: <strong>{{
                                                                ucwords($item->creator?$item->creator->name:"
                                                                ")}}</strong></span></td>

                                                    <td><span>Created At: <strong>{{ date('d/m/Y h:i A',
                                                                strtotime($item->created_at)) }}</strong></span></td>
                                                    @endif
                                                    @if($ExpenseAt)
                                                    <td><span>Expense At: <strong>{{ $ExpenseAt }}</strong></span></td>
                                                    @endif
                                                    @if($ExpenseType)
                                                    <td><span>Expense: <strong>{{ $ExpenseType }}</strong></span></td>
                                                    @endif
                                                </tr>

                                                <tr>

                                                    @if (!empty($item->updater))

                                                    <td><span>Updated By: <strong>{{
                                                                ucwords($item->updater->name)}}</strong></span></td>

                                                    <td><span>Updated At: <strong>{{ date('d/m/Y h:i A',
                                                                strtotime($item->updated_at)) }}</strong></span></td>

                                                    @endif

                                                </tr>

                                            </table>

                                        </div>

                                    </td>

                                </tr>
                                @php $i++; @endphp
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No collection records found in selected date
                                        range.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            {{--
        </div> --}}
        {{-- </div> --}}
    <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
                $(document).on('click', '.store_details_row', function () {
                $(this).next("tr").find(".store_details_column").toggle();
            });
        });
    window.addEventListener('confirm-revoke', event => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will revoke the payment and reset linked invoices and payments!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, revoke it!'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('revoke-payment-confirmed', { id: event.detail });
            }
        });
    });


</script>

</div>