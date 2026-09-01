<?php

namespace Modules\ProductLedger\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class ProductLedgerDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->editColumn('transaction_date', function ($row) {
                return Carbon::parse($row->transaction_date)->format(company()->date_format . ' ' . company()->time_format);
            })
            ->editColumn('product_name', function ($row) {
                return '<a href="' . route('product-ledger.show', $row->product_id) . '" class="text-darkest-grey">' . htmlspecialchars($row->product_name) . '</a>';
            })
            ->editColumn('quantity', function ($row) {
                if ($row->quantity < 0) {
                    return '<span class="text-red font-weight-bold">' . $row->quantity . '</span>';
                }
                return '<span class="text-success font-weight-bold">+' . $row->quantity . '</span>';
            })
            ->editColumn('price', function ($row) {
                return currency_format($row->price);
            })
            ->editColumn('transaction_type', function ($row) {
                switch ($row->transaction_type) {
                    case 'Sale':
                        return '<span class="badge badge-danger">Sale</span>';
                    case 'Purchase':
                        return '<span class="badge badge-success">Purchase</span>';
                    case 'Adjustment':
                        return '<span class="badge badge-info">Stock Adjust</span>';
                    case 'Opening Stock':
                        return '<span class="badge badge-primary">Opening Stock</span>';
                    default:
                        return '<span class="badge badge-secondary">' . $row->transaction_type . '</span>';
                }
            })
            ->rawColumns(['quantity', 'transaction_type', 'product_name']);
    }

    public function query()
    {
        $request = $this->request();

        // 1. Sales query
        $salesQuery = DB::table('invoice_items')
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->leftJoin('users', 'users.id', '=', 'invoices.client_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.company_id', company()->id)
            ->select(
                'invoice_items.id as id',
                'invoices.issue_date as transaction_date',
                'products.name as product_name',
                'products.sku as product_sku',
                DB::raw("'Sale' as transaction_type"),
                'invoices.invoice_number as reference',
                'users.name as party',
                DB::raw('(invoice_items.quantity * -1) as quantity'),
                'invoice_items.unit_price as price',
                'invoice_items.product_id'
            );

        // 2. Purchases query
        if (Schema::hasTable('purchase_items')) {
            $purchasesQuery = DB::table('purchase_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_items.purchase_order_id')
                ->leftJoin('purchase_vendors', 'purchase_vendors.id', '=', 'purchase_orders.vendor_id')
                ->join('products', 'products.id', '=', 'purchase_items.product_id')
                ->where('purchase_orders.company_id', company()->id)
                ->select(
                    'purchase_items.id as id',
                    'purchase_orders.purchase_date as transaction_date',
                    'products.name as product_name',
                    'products.sku as product_sku',
                    DB::raw("'Purchase' as transaction_type"),
                    'purchase_orders.purchase_order_number as reference',
                    'purchase_vendors.primary_name as party',
                    'purchase_items.quantity as quantity',
                    'purchase_items.unit_price as price',
                    'purchase_items.product_id'
                );
        } else {
            $purchasesQuery = DB::table('invoice_items')
                ->select(
                    'invoice_items.id as id',
                    'invoices.issue_date as transaction_date',
                    'products.name as product_name',
                    'products.sku as product_sku',
                    DB::raw("'Purchase' as transaction_type"),
                    DB::raw("NULL as reference"),
                    DB::raw("NULL as party"),
                    DB::raw("0 as quantity"),
                    DB::raw("0 as price"),
                    'invoice_items.product_id'
                )->whereRaw('1 = 0');
        }

        // 3. Adjustments query
        if (Schema::hasTable('purchase_stock_adjustments')) {
            $adjustmentsQuery = DB::table('purchase_stock_adjustments')
                ->join('products', 'products.id', '=', 'purchase_stock_adjustments.product_id')
                ->leftJoin('purchase_stock_adjustment_reasons', 'purchase_stock_adjustment_reasons.id', '=', 'purchase_stock_adjustments.reason_id')
                ->where('purchase_stock_adjustments.company_id', company()->id)
                ->select(
                    'purchase_stock_adjustments.id as id',
                    'purchase_stock_adjustments.date as transaction_date',
                    'products.name as product_name',
                    'products.sku as product_sku',
                    DB::raw("'Adjustment' as transaction_type"),
                    'purchase_stock_adjustments.reference_number as reference',
                    'purchase_stock_adjustment_reasons.name as party',
                    'purchase_stock_adjustments.quantity_adjustment as quantity',
                    'purchase_stock_adjustments.changed_value as price',
                    'purchase_stock_adjustments.product_id'
                );
        } else {
            $adjustmentsQuery = DB::table('invoice_items')
                ->select(
                    'invoice_items.id as id',
                    'invoices.issue_date as transaction_date',
                    'products.name as product_name',
                    'products.sku as product_sku',
                    DB::raw("'Adjustment' as transaction_type"),
                    DB::raw("NULL as reference"),
                    DB::raw("NULL as party"),
                    DB::raw("0 as quantity"),
                    DB::raw("0 as price"),
                    'invoice_items.product_id'
                )->whereRaw('1 = 0');
        }

        // 4. Opening stock query
        $openingStockQuery = DB::table('products')
            ->where('products.company_id', company()->id)
            ->select(
                'products.id as id',
                'products.created_at as transaction_date',
                'products.name as product_name',
                'products.sku as product_sku',
                DB::raw("'Opening Stock' as transaction_type"),
                DB::raw("'Initial' as reference"),
                DB::raw("'Initial Entry' as party"),
                'products.opening_stock as quantity',
                'products.price as price',
                'products.id as product_id'
            )
            ->where('products.opening_stock', '>', 0);

        // Apply filters to subqueries before union
        if ($request->product_id && $request->product_id != 'all') {
            $salesQuery->where('invoice_items.product_id', $request->product_id);
            if (Schema::hasTable('purchase_items')) {
                $purchasesQuery->where('purchase_items.product_id', $request->product_id);
            }
            if (Schema::hasTable('purchase_stock_adjustments')) {
                $adjustmentsQuery->where('purchase_stock_adjustments.product_id', $request->product_id);
            }
            $openingStockQuery->where('products.id', $request->product_id);
        }

        if ($request->startDate) {
            $startDate = Carbon::createFromFormat(company()->date_format, $request->startDate)->startOfDay()->format('Y-m-d H:i:s');
            $salesQuery->where('invoices.issue_date', '>=', $startDate);
            if (Schema::hasTable('purchase_items')) {
                $purchasesQuery->where('purchase_orders.purchase_date', '>=', $startDate);
            }
            if (Schema::hasTable('purchase_stock_adjustments')) {
                $adjustmentsQuery->where('purchase_stock_adjustments.date', '>=', $startDate);
            }
            $openingStockQuery->where('products.created_at', '>=', $startDate);
        }

        if ($request->endDate) {
            $endDate = Carbon::createFromFormat(company()->date_format, $request->endDate)->endOfDay()->format('Y-m-d H:i:s');
            $salesQuery->where('invoices.issue_date', '<=', $endDate);
            if (Schema::hasTable('purchase_items')) {
                $purchasesQuery->where('purchase_orders.purchase_date', '<=', $endDate);
            }
            if (Schema::hasTable('purchase_stock_adjustments')) {
                $adjustmentsQuery->where('purchase_stock_adjustments.date', '<=', $endDate);
            }
            $openingStockQuery->where('products.created_at', '<=', $endDate);
        }

        if ($request->searchText != '') {
            $salesQuery->where(function ($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->searchText . '%')
                  ->orWhere('invoices.invoice_number', 'like', '%' . $request->searchText . '%')
                  ->orWhere('products.sku', 'like', '%' . $request->searchText . '%');
            });
            if (Schema::hasTable('purchase_items')) {
                $purchasesQuery->where(function ($q) use ($request) {
                    $q->where('products.name', 'like', '%' . $request->searchText . '%')
                      ->orWhere('purchase_orders.purchase_order_number', 'like', '%' . $request->searchText . '%')
                      ->orWhere('products.sku', 'like', '%' . $request->searchText . '%');
                });
            }
            if (Schema::hasTable('purchase_stock_adjustments')) {
                $adjustmentsQuery->where(function ($q) use ($request) {
                    $q->where('products.name', 'like', '%' . $request->searchText . '%')
                      ->orWhere('purchase_stock_adjustments.reference_number', 'like', '%' . $request->searchText . '%')
                      ->orWhere('products.sku', 'like', '%' . $request->searchText . '%');
                });
            }
            $openingStockQuery->where(function ($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->searchText . '%')
                  ->orWhere('products.sku', 'like', '%' . $request->searchText . '%');
            });
        }

        // Combine using UNION
        $union = null;
        if ($request->type == 'all' || !$request->type) {
            $union = $salesQuery->unionAll($purchasesQuery)->unionAll($adjustmentsQuery)->unionAll($openingStockQuery);
        } elseif ($request->type == 'sale') {
            $union = $salesQuery;
        } elseif ($request->type == 'purchase') {
            $union = $purchasesQuery;
        } elseif ($request->type == 'adjustment') {
            $union = $adjustmentsQuery;
        }

        // Build main query wrapping the union in a subquery
        $query = DB::table(DB::raw("({$union->toSql()}) as ledger_sub"))
            ->mergeBindings($union)
            ->select('id', 'transaction_date', 'product_name', 'product_sku', 'transaction_type', 'reference', 'party', 'quantity', 'price', 'product_id');

        return $query;
    }

    public function html()
    {
        return $this->setBuilder('product-ledger-table')
            ->parameters([
                'order' => [[0, 'desc']], // Default sorting: Date desc
                'initComplete' => 'function () {
                   window.LaravelDataTables["product-ledger-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    protected function getColumns()
    {
        return [
            __('app.date') => ['data' => 'transaction_date', 'name' => 'transaction_date', 'title' => __('app.date')],
            __('app.menu.products') => ['data' => 'product_name', 'name' => 'product_name', 'title' => __('app.menu.products')],
            'sku' => ['data' => 'product_sku', 'name' => 'product_sku', 'title' => 'SKU'],
            'type' => ['data' => 'transaction_type', 'name' => 'transaction_type', 'title' => 'Type'],
            'reference' => ['data' => 'reference', 'name' => 'reference', 'title' => 'Reference'],
            'party' => ['data' => 'party', 'name' => 'party', 'title' => 'Client / Vendor / Reason'],
            'quantity' => ['data' => 'quantity', 'name' => 'quantity', 'title' => 'Quantity'],
            __('app.price') => ['data' => 'price', 'name' => 'price', 'title' => __('app.price')],
        ];
    }
}
