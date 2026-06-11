<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3  bg-gradient-dark custom-sideber-design"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0 d-flex text-wrap align-items-center company-logo" href=" {{ route('admin.dashboard') }} ">
            <img src="{{ asset('assets') }}/img/pdf_logo.png" class="h-100" alt="main_logo">
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto  max-height-vh-100" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white {{ Route::currentRouteName() == 'admin.dashboard' ? 'active ' : '' }}" href="{{route('admin.dashboard')}}">
                    <!-- Default to the first route -->
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            @if ($this->hasPermissionByParent('customer_management'))
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/customers*') ? 'active ' : '' }}" href="{{route('customers.index')}}">
                    <!-- Default to the first route -->
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">group</i>
                    </div>
                    <span class="nav-link-text ms-1">Customer Management</span>
                </a>
            </li>
            @endif
            @if ($this->hasPermissionByParent('supplier_management'))
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/suppliers*') ? 'active ' : '' }}" href="{{route('suppliers.index')}}">
                    <!-- Default to the first route -->
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">store</i>
                    </div>
                    <span class="nav-link-text ms-1">Supplier Management</span>
                </a>
            </li>
            @endif
            {{-- Production management --}}
            @if ($this->hasPermissionByParent('production_management'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ Request::is('admin/production*') ? 'active ' : '' }}"
                        href="#OrderManagementSubmenu" data-bs-toggle="collapse"
                        aria-expanded="{{ Request::is('admin/orders*') ? 'true' : 'false' }}">
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">shopping_cart</i>
                        </div>
                        <span class="nav-link-text ms-1">Production Management</span>
                    </a>
                </li>
                <ul id="OrderManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ Request::is('admin/production*') ? 'show' : '' }}">
                <li class="nav-item">
                    <a class="nav-link text-white {{ Request::is('admin/production') ? 'active ' : '' }}"
                        href="{{route('production.order.index')}}">
                        Production Orders
                    </a>
                </li>
            </ul>
            @endif
            @if ($this->hasPermissionByParent('order_management'))
            {{-- Order Management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/orders*') ? 'active ' : '' }}"
                    href="#OrderManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ Request::is('admin/orders*') ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">shopping_cart</i>
                    </div>
                    <span class="nav-link-text ms-1">Order Management</span>
                </a>
            </li>

            <!-- Submenu -->
            <ul id="OrderManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ Request::is('admin/orders*') ? 'show' : '' }}">
                {{-- All Orders --}}
                @if ($this->hasPermission('order_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Request::is('admin/orders') ? 'active ' : '' }}"
                            href="{{route('admin.order.index')}}">
                            All Orders
                        </a>
                    </li>
                @endif
                {{--  Place Order --}}
                @if ($this->hasPermission('order_create'))
                    <a class="nav-link text-white {{ Request::is('admin/orders/new') ? 'active ' : '' }}"
                        href="{{route('admin.order.new')}}">
                        Place Order
                    </a>
                @endif
                {{--  Invoices --}}
                @if ($this->hasPermission('order_invoice_listing'))
                    <a class="nav-link text-white {{ Request::is('admin/orders/invoice') ? 'active ' : '' }}"
                        href="{{route('admin.order.invoice.index')}}">
                        Invoices
                    </a>
                @endif
                {{-- Instant Invoices --}}
                @if ($this->hasPermission('instant_invoices'))
                    <a class="nav-link text-white {{ Request::is('admin/orders/invoice/add') ? 'active ' : '' }}"
                        href="{{route('admin.order.invoice.add')}}">
                        Instant Invoices
                    </a>
                @endif
                {{-- Proformas --}}
                @if ($this->hasPermission('proformas'))
                    <a class="nav-link text-white {{ Request::is('admin/orders/proformas') ? 'active ' : '' }}"
                        href="{{route('admin.order.proformas.index')}}">
                        Proformas
                    </a>
                @endif
                {{-- Cancel Order --}}
                @if ($this->hasPermission('order_cancel_listing'))
                    <a class="nav-link text-white {{ Request::is('admin/orders/cancel-order') ? 'active ' : '' }}"
                        href="{{route('admin.order.cancel-order.index')}}">
                        Cancel Order
                    </a>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('accounting_management'))
            {{-- Expense management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin/accounting*') ? 'active bg-gradient-primary' : '' }}"
                    href="#AccountManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ request()->is('admin/accounting*') ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">account_balance</i>
                    </div>
                    <span class="nav-link-text ms-1">Account Management</span>
                </a>
            </li>
            <ul id="AccountManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ request()->is('admin/accounting*') ? 'show' : '' }}">
                {{-- Payment Collection --}}
                @if ($this->hasPermission('payment_collection_listing'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('admin/accounting/payment-collection') ? 'active' : '' }}"
                        href="{{ route('admin.accounting.payment_collection') }}">
                        Payment Collections
                    </a>
                </li> 
                @endif
                {{-- Add Payment Receipt --}}
                @if ($this->hasPermission('add_payment_receipt'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('admin/accounting/add-payment-receipt') ? 'active' : '' }}"
                        href="{{ route('admin.accounting.add_payment_receipt') }}">
                        Add Payment Receipt
                    </a>
                </li>
                @endif
                {{-- Depot Expense --}}
                 @if ($this->hasPermission('depo_expense_listing'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('admin/accounting/depot-expense/list') ? 'active' : '' }}"
                    href="{{ route('admin.accounting.list.depot_expense') }}">
                    Depot Expense
                    </a>
                </li>
                @endif
                {{-- Customer Opening Balance --}}
                @if ($this->hasPermission('customer_opening_balance_listing'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('admin/accounting/list-opening-balance') ? 'active' : '' }}"
                        href="{{ route('admin.accounting.list_opening_balance') }}">
                        Customer Opening Balance
                    </a>
                </li>
                @endif
                {{-- Cashbook Module --}}
                @if ($this->hasPermission('cashbook_module'))
                <li class="nav-item">
                   <a class="nav-link text-white {{ request()->is('admin/accounting/cashbook-module') ? 'active' : '' }}"
                       href="{{ route('admin.accounting.cashbook_module') }}">
                      Cashbook Module
                   </a>
                </li>
                @endif
                {{-- Daily Cash Entry --}}
                @if ($this->hasPermission('daily_cash_entry'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ request()->is('admin/accounting/daily-cash-entry') ? 'active' : '' }}"
                        href="{{ route('admin.accounting.daily-cash-entry') }}">
                       Daily Cash Entry
                    </a>
                </li>
                @endif
            </ul>
            @endif
            @if ($user->id==1)
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs text-white font-weight-bolder opacity-8">Master Modules</h6>
            </li>
            {{-- Business Type --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/business-type*') || in_array(Route::currentRouteName(), ['business_type.index']) ? 'active ' : '' }}"
                    href="#businessTypeSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['business_type.index']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">receipt</i>
                    </div>
                    <span class="nav-link-text ms-1">Master Management</span>
                </a>
            </li>
            <ul id="businessTypeSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['business_type.index','country.index']) ? 'show' : '' }}">
                {{--  Business Type --}}
                @if ($this->hasPermission('business_type_index'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'business_type.index' ? 'active ' : '' }}"
                            href="{{route('business_type.index')}}">
                            Business Type
                        </a>
                    </li>
                @endif
                {{-- Country --}}
                @if ($this->hasPermission('country_index'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'country.index' ? 'active ' : '' }}"
                            href="{{route('country.index')}}">
                            Country
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('purchase_order_management'))
            {{-- Purchase Order --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/purchase-order*') || in_array(Route::currentRouteName(), ['purchase_order.index','purchase_order.create']) ? 'active ' : '' }}"
                    href="#purchaseOrderSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['purchase_order.index','purchase_order.create']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">receipt_long</i>
                    </div>
                    <span class="nav-link-text ms-1">Purchase Order</span>
                </a>
            </li>
            <ul id="purchaseOrderSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['purchase_order.index','purchase_order.create','purchase_order.generate_grn']) ? 'show' : '' }}">
                @if ($this->hasPermission('purchase_order_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'purchase_order.index' ? 'active ' : '' }}"
                            href="{{route('purchase_order.index')}}">
                            PO
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('stock_management'))
            {{-- Stock Management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ in_array(Route::currentRouteName(), ['stock.index']) ? 'active ' : '' }}"
                    href="#StockManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['stock.index']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">inventory</i>
                    </div>
                    <span class="nav-link-text ms-1">Stock Management</span>
                </a>
            </li>

            <!-- Submenu -->
            <ul id="StockManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['stock.index','stock.adjustment']) ? 'show' : '' }}">
                @if ($this->hasPermission('view_stock_logs'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'stock.index' ? 'active ' : '' }}"
                            href="{{ route('stock.index') }}">
                            Stock Logs
                        </a>
                    </li>
                @endif
                @if ($this->hasPermission('stock_adjustment'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'stock.adjustment' ? 'active ' : '' }}"
                            href="{{ route('stock.adjustment') }}">
                            Stock Adjustment
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('product_management'))
            {{-- Product Management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ Request::is('admin/products*') || in_array(Route::currentRouteName(), ['product.view', 'product.gallery', 'product.add', 'product.update', 'admin.categories', 'admin.subcategories', 'measurements.index', 'product.fabrics','admin.collections.index']) ? 'active ' : '' }}"
                    href="#productManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['product.view','product.gallery','product.add','product.update','admin.categories','admin.subcategories','measurements.index','product.gallery','product.fabrics','admin.collections.index']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">category</i>
                    </div>
                    <span class="nav-link-text ms-1">Product Management</span>
                </a>
            </li>

            <!-- Submenu -->
            <ul id="productManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['product.view', 'product.gallery', 'product.add', 'product.update', 'admin.categories', 'admin.subcategories', 'measurements.index', 'product.fabrics','admin.collections.index','admin.fabrics.index','admin.fabrics.category','product.catalogue']) ? 'show' : '' }}">
                {{-- Catalogue --}}
                @if ($this->hasPermission('catalogue_details'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Request::is('admin/products/catalogue') ? 'active ' : '' }}"
                            href="{{route('product.catalogue')}}">
                            Catalogue
                        </a>
                    </li>
                @endif

                {{-- Collections --}}
                @if ($this->hasPermission('collection_details'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Request::is('admin/products/collections') ? 'active ' : '' }}"
                            href="{{route('admin.collections.index')}}">
                            Collections
                        </a>
                    </li>
                @endif

                {{-- Categories --}}
                @if ($this->hasPermission('category_details'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Request::is('admin/products/categories*') ? 'active ' : '' }}"
                            href="{{route('admin.categories')}}">
                            Categories
                        </a>
                    </li>
                @endif

                {{-- Fabrics --}}
                @if ($this->hasPermission('fabric_category'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'admin.fabrics.category' ? 'active ' : '' }}"
                            href="{{route('admin.fabrics.category')}}">
                            Fabrics Category
                        </a>
                    </li>
                @endif
                @if ($this->hasPermission('fabric_details'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'admin.fabrics.index' ? 'active ' : '' }}"
                            href="{{route('admin.fabrics.index')}}">
                            Fabrics
                        </a>
                    </li>
                @endif

                {{--  Products --}}
                @if ($this->hasPermission('product_listing'))    
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'product.view' ? 'active ' : '' }}"
                            href="{{route('product.view')}}">
                            Products
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('branch_management'))
            <li class="nav-item">
                <a class="nav-link text-white {{ in_array(Route::currentRouteName(), ['staff.designation','staff.index','staff.add','branch.index','salesman.index']) ? 'active ' : '' }}"
                    href="#StaffManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['staff.designation','staff.index','staff.add','branch.index','salesman.index']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">assignment_ind</i>
                    </div>
                    <span class="nav-link-text ms-1">Branch Management</span>
                </a>
            </li>

            <!-- Submenu -->
            <ul id="StaffManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['staff.designation','staff.index','staff.add','staff.update','staff.view','staff.task','staff.task.add','staff.cities.add','salesman.index','branch.index']) ? 'show' : '' }}">
                {{-- Branch --}}
                @if ($this->hasPermission('branch_listing'))    
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'branch.index' ? 'active ' : '' }}"
                            href="{{route('branch.index')}}">
                            Branch
                        </a>
                    </li>
                @endif
                    
                {{-- Designation --}}
                @if ($this->hasPermission('branch_listing'))    
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'staff.designation' ? 'active ' : '' }}"
                            href="{{route('staff.designation')}}">
                            Designation
                        </a>
                    </li>
                @endif
                {{-- Staff --}}
                @if ($this->hasPermission('staff_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ in_array(Route::currentRouteName(), ['staff.index','staff.add','staff.update','staff.view','staff.task','staff.task.add','staff.cities.add']) ? 'active ' : '' }}"
                            href="{{route('staff.index')}}">
                            Staff
                        </a>
                    </li>
                @endif
                {{-- Staff Bill Book --}}
                @if ($this->hasPermission('staff_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'salesman.index' ? 'active ' : '' }}"
                            href="{{route('salesman.index')}}">
                            Staff Bill Book
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            @if ($this->hasPermissionByParent('expense_management'))
            {{-- Expense management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ in_array(Route::currentRouteName(), ['expense.index']) ? 'active ' : '' }}"
                    href="#ExpenseManagementSubmenu" data-bs-toggle="collapse"
                    aria-expanded="{{ in_array(Route::currentRouteName(), ['expense.index']) ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">attach_money</i>
                    </div>
                    <span class="nav-link-text ms-1">Expense Management</span>
                </a>
            </li>
            <ul id="ExpenseManagementSubmenu"
                class="collapse list-unstyled ms-4 {{ in_array(Route::currentRouteName(), ['expense.index']) ? 'show' : '' }}">
                {{-- Recurring --}}
                @if ($this->hasPermission('staff_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'expense.index' && request()->get('parent_id') == 1 ? 'active ' : '' }}"
                            href="{{ route('expense.index', ['parent_id' => 1]) }}">
                            Recurring
                        </a>
                    </li>
                @endif
                {{-- Non Recurring --}}
                @if ($this->hasPermission('staff_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ Route::currentRouteName() == 'expense.index' && request()->get('parent_id') == 2 ? 'active ' : '' }}"
                            href="{{ route('expense.index', ['parent_id' => 2]) }}">
                            Non Recurring
                        </a>
                    </li>    
                @endif
            </ul>
            @endif

            @if ($this->hasPermissionByParent('report_management'))
            {{-- Report management --}}
            <li class="nav-item">
                <a class="nav-link text-white {{ request()->is('admin/report*') ? 'active bg-gradient-primary' : '' }}"
                    href="#ReportManagementSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->is('admin/report*') ? 'true' : 'false' }}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">account_balance</i>
                    </div>
                    <span class="nav-link-text ms-1">Report Management</span>
                </a>
            </li>
            <ul id="ReportManagementSubmenu" class="collapse list-unstyled ms-4 {{ request()->is('admin/report*') ? 'show' : '' }}">
                @if ($this->hasPermission('staff_listing'))
                    <li class="nav-item">
                        <a class="nav-link text-white {{ request()->is('admin/report/user-ledger') ? 'active' : '' }}"
                        href="{{ route('admin.report.user_ledger') }}">
                        User Ledger
                        </a>
                    </li>
                @endif
            </ul>
            @endif
            {{-- ToDo List --}}
            @if ($this->hasPermission('staff_listing'))
                <li class="nav-item">
                    <a class="nav-link text-white {{ Route::currentRouteName() == 'admin.todo-list' ? 'active ' : '' }}" href="{{route('todo-list.todo-list')}}">
                        <!-- Default to the first route -->
                        <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="material-icons opacity-10">dashboard</i>
                        </div>
                        <span class="nav-link-text ms-1">ToDo List</span>
                    </a>
                </li>
            @endif

        </ul>
    </div>
    
</aside>
