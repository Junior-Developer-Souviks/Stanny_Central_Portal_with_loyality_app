<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['parent_name' => 'customer_management', 'name' => 'customer_listing', 'route' => 'customers.index'],
            ['parent_name' => 'customer_management', 'name' => 'customer_create', 'route' => 'admin.user-address-form'],
            ['parent_name' => 'customer_management', 'name' => 'customer_update', 'route' => 'admin.customers.edit'],
            ['parent_name' => 'customer_management', 'name' => 'customer_details', 'route' => 'admin.customers.details'],

            ['parent_name' => 'supplier_management', 'name' => 'supplier_listing', 'route' => 'suppliers.index'], // No specific route for this
            ['parent_name' => 'supplier_management', 'name' => 'supplier_create', 'route' => 'suppliers.add'], // No specific route for this
            ['parent_name' => 'supplier_management', 'name' => 'supplier_update', 'route' => 'suppliers.edit'], // No specific route for this
            ['parent_name' => 'supplier_management', 'name' => 'supplier_details', 'route' => 'suppliers.details'],
            
            ['parent_name' => 'purchase_order_management', 'name' => 'purchase_order_listing', 'route' => 'purchase_order.index'], // No specific route for this
            ['parent_name' => 'purchase_order_management', 'name' => 'purchase_order_create', 'route' => 'purchase_order.create'], // No specific route for this
            ['parent_name' => 'purchase_order_management', 'name' => 'purchase_order_edit', 'route' => 'purchase_order.edit'], // No specific route for this
            ['parent_name' => 'purchase_order_management', 'name' => 'purchase_order_details', 'route' => 'purchase_order.details'], // No specific route for this
            ['parent_name' => 'purchase_order_management', 'name' => 'purchase_order_generate_grn', 'route' => 'purchase_order.generate_grn'],
            
            
            ['parent_name' => 'stock_management', 'name' => 'view_stock_logs', 'route' => 'stock.index'],

            ['parent_name' => 'product_management', 'name' => 'catalogue_details', 'route' => 'product.catalogue'],
            ['parent_name' => 'product_management', 'name' => 'collection_details', 'route' => 'admin.collections.index'],
            ['parent_name' => 'product_management', 'name' => 'category_details', 'route' => 'admin.categories'],
            ['parent_name' => 'product_management', 'name' => 'fabric_details', 'route' => 'admin.fabrics.index'],
            ['parent_name' => 'product_management', 'name' => 'fabric_category', 'route' => 'admin.fabrics.category'],

            ['parent_name' => 'product_management', 'name' => 'product_listing', 'route' => 'product.view'],
            ['parent_name' => 'product_management', 'name' => 'product_create', 'route' => 'product.add'],
            ['parent_name' => 'product_management', 'name' => 'product_update', 'route' => 'product.update'],
            ['parent_name' => 'product_management', 'name' => 'product_wise_measurements', 'route' => 'measurements.index'],
            ['parent_name' => 'product_management', 'name' => 'product_wise_fabrics', 'route' => 'product_fabrics.index'],

            ['parent_name' => 'branch_management', 'name' => 'branch_listing', 'route' => 'branch.index'],
            ['parent_name' => 'branch_management', 'name' => 'designation_listing', 'route' => 'staff.designation'],
            ['parent_name' => 'branch_management', 'name' => 'staff_listing', 'route' => 'staff.index'],
            ['parent_name' => 'branch_management', 'name' => 'staff_create', 'route' => 'staff.add'],
            ['parent_name' => 'branch_management', 'name' => 'staff_update', 'route' => 'staff.update'],
            ['parent_name' => 'branch_management', 'name' => 'staff_details', 'route' => 'staff.view'],
            ['parent_name' => 'branch_management', 'name' => 'staff_bill_books', 'route' => 'salesman.index'],

            ['parent_name' => 'expense_management', 'name' => 'recurring_expense', 'route' => 'expense.index'],
            ['parent_name' => 'expense_management', 'name' => 'non_recurring_expense', 'route' => 'expense.index'],

            ['parent_name' => 'accounting_management', 'name' => 'payment_collection_listing', 'route' => 'admin.accounting.payment_collection'],
            ['parent_name' => 'accounting_management', 'name' => 'add_payment_receipt', 'route' => 'admin.accounting.add_payment_receipt'],
            ['parent_name' => 'accounting_management', 'name' => 'approve_payment_receipt', 'route' => 'admin.accounting.payment_receipt_detail'],
            ['parent_name' => 'accounting_management', 'name' => 'customer_opening_balance_listing', 'route' => 'admin.accounting.list_opening_balance'],
            ['parent_name' => 'accounting_management', 'name' => 'customer_opening_balance_create', 'route' => 'admin.accounting.add_opening_balance'],
            ['parent_name' => 'accounting_management', 'name' => 'depo_expense_listing', 'route' => 'admin.accounting.list.depot_expense'],
            ['parent_name' => 'accounting_management', 'name' => 'depo_expense_approval', 'route' => 'admin.accounting.expense.details'],
            ['parent_name' => 'accounting_management', 'name' => 'depo_expense_create', 'route' => 'admin.accounting.add_depot_expense'],
            ['parent_name' => 'accounting_management', 'name' => 'depo_expense_update', 'route' => 'admin.accounting.edit_depot_expense'],
            ['parent_name' => 'accounting_management', 'name' => 'cashbook_module', 'route' => 'admin.accounting.cashbook_module'],
            ['parent_name' => 'accounting_management', 'name' => 'daily_cash_entry', 'route' => 'admin.accounting.daily-cash-entry'],


            ['parent_name' => 'report_management', 'name' => 'user_ledger_listing', 'route' => 'admin.report.user_ledger'],

            ['parent_name' => 'order_management', 'name' => 'order_listing', 'route' => 'admin.order.index'],
            ['parent_name' => 'order_management', 'name' => 'order_create', 'route' => 'admin.order.new'],
            ['parent_name' => 'order_management', 'name' => 'order_update', 'route' => 'admin.order.edit'],
            ['parent_name' => 'order_management', 'name' => 'order_detail', 'route' => 'admin.order.view'],
            ['parent_name' => 'order_management', 'name' => 'order_confirm', 'route' => 'admin.order.add_order_slip'],
            ['parent_name' => 'order_management', 'name' => 'order_invoice_listing', 'route' => 'admin.order.invoice.index'],
            ['parent_name' => 'order_management', 'name' => 'order_cancel_listing', 'route' => 'admin.order.cancel-order.index'],
            ['parent_name' => 'order_management', 'name' => 'instant_invoices', 'route' => 'admin.order.invoice.add'],
            ['parent_name' => 'order_management', 'name' => 'proformas', 'route' => 'admin.order.proformas.index'],

            ['parent_name' => 'production_management', 'name' => 'production_order_listing', 'route' => 'production.order.index'],

            ['parent_name' => 'master_management', 'name' => 'business_type_index', 'route' => 'business_type.index'],
            ['parent_name' => 'master_management', 'name' => 'country_index', 'route' => 'country.index'],

            ['parent_name' => 'loyality_management', 'name' => 'loyality_rule_index', 'route' => 'loyality-rule.loyality_rule'],
            ['parent_name' => 'loyality_management', 'name' => 'loyality_rule_settings', 'route' => 'loyality-rule.settings'],

            ['parent_name' => 'banner_management', 'name' => 'banner_list', 'route' => 'banners.list'],

            ['parent_name' => 'loyalty_ledger', 'name' => 'loyalty_ledger', 'route' => 'loyalty-ledger.index'],

            ['parent_name' => 'customer_marketing', 'name' => 'customer_marketing_list', 'route' => 'customer.marketing.list'],

            ['parent_name' => 'customer_marketing', 'name' => 'customer_marketing_photo_assign', 'route' => 'customer.marketing.photo'],

            


        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'parent_name' => $permission['parent_name'],
                    'route' => $permission['route']
                ]
            );
        }
    }
}
