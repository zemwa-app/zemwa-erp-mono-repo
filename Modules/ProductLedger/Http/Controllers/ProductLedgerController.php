<?php

namespace Modules\ProductLedger\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use App\Models\Product;
use App\Models\InvoiceItems;
use Modules\ProductLedger\DataTables\ProductLedgerDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ProductLedgerController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Product Ledger';
        $this->middleware(function ($request, $next) {
            $viewPermission = user()->permission('view_product');
            abort_403(!in_array($viewPermission, ['all', 'added']));

            return $next($request);
        });
    }

    public function index(ProductLedgerDataTable $dataTable)
    {
        $this->products = Product::select('id', 'name', 'sku')->where('company_id', company()->id)->get();
        return $dataTable->render('productledger::index', $this->data);
    }

    public function showProductLedger($productId, ProductLedgerDataTable $dataTable)
    {
        $this->product = Product::where('id', $productId)->where('company_id', company()->id)->firstOrFail();
        $this->productId = $productId;
        $this->pageTitle = 'Product Ledger - ' . $this->product->name;
        
        return $dataTable->render('productledger::show', $this->data);
    }

    public function summary(Request $request)
    {
        $productId = $request->product_id;
        $startDate = $request->startDate;
        $endDate = $request->endDate;

        // Base sales query with tenant scope
        $sales = InvoiceItems::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.company_id', company()->id);

        // Base purchase query with tenant scope
        $purchases = Schema::hasTable('purchase_items')
            ? DB::table('purchase_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_items.purchase_order_id')
                ->where('purchase_orders.company_id', company()->id)
            : null;

        // Base stock adjustments query with tenant scope
        $adjustments = Schema::hasTable('purchase_stock_adjustments')
            ? DB::table('purchase_stock_adjustments')
                ->where('purchase_stock_adjustments.company_id', company()->id)
            : null;

        // Apply product filter
        if ($productId && $productId != 'all') {
            $sales->where('invoice_items.product_id', $productId);
            if ($purchases) {
                $purchases->where('purchase_items.product_id', $productId);
            }
            if ($adjustments) {
                $adjustments->where('purchase_stock_adjustments.product_id', $productId);
            }
        }

        // Apply date filters
        if ($startDate) {
            $start = Carbon::createFromFormat(company()->date_format, $startDate)->startOfDay()->format('Y-m-d H:i:s');
            $sales->where('invoices.issue_date', '>=', $start);
            if ($purchases) {
                $purchases->where('purchase_orders.purchase_date', '>=', $start);
            }
            if ($adjustments) {
                $adjustments->where('purchase_stock_adjustments.date', '>=', $start);
            }
        }
        if ($endDate) {
            $end = Carbon::createFromFormat(company()->date_format, $endDate)->endOfDay()->format('Y-m-d H:i:s');
            $sales->where('invoices.issue_date', '<=', $end);
            if ($purchases) {
                $purchases->where('purchase_orders.purchase_date', '<=', $end);
            }
            if ($adjustments) {
                $adjustments->where('purchase_stock_adjustments.date', '<=', $end);
            }
        }

        // 1. Total Sold (Sales quantity sum)
        $totalSold = (int)$sales->sum('invoice_items.quantity');

        // 2. Total Purchased
        $totalPurchased = $purchases ? (int)$purchases->sum('purchase_items.quantity') : 0;

        // 3. Total Adjusted
        $totalAdjusted = $adjustments ? (int)$adjustments->sum('purchase_stock_adjustments.quantity_adjustment') : 0;

        // 4. Current Stock (opening stock + all purchases + all adjustments - all sales)
        // Opening stock with tenant scope
        $openingStockQuery = Product::query()
            ->where('company_id', company()->id);
        if ($productId && $productId != 'all') {
            $openingStockQuery->where('id', $productId);
        }
        $openingStock = (int)$openingStockQuery->sum('opening_stock');

        // Absolute counts ignoring date filter for real-time stock status (scoped to tenant)
        $absSales = InvoiceItems::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->where('invoices.company_id', company()->id);
        $absPurchases = Schema::hasTable('purchase_items')
            ? DB::table('purchase_items')
                ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_items.purchase_order_id')
                ->where('purchase_orders.company_id', company()->id)
            : null;
        $absAdjustments = Schema::hasTable('purchase_stock_adjustments')
            ? DB::table('purchase_stock_adjustments')
                ->where('purchase_stock_adjustments.company_id', company()->id)
            : null;

        if ($productId && $productId != 'all') {
            $absSales->where('invoice_items.product_id', $productId);
            if ($absPurchases) {
                $absPurchases->where('purchase_items.product_id', $productId);
            }
            if ($absAdjustments) {
                $absAdjustments->where('purchase_stock_adjustments.product_id', $productId);
            }
        }

        $absSoldVal = (int)$absSales->sum('invoice_items.quantity');
        $absPurchasedVal = $absPurchases ? (int)$absPurchases->sum('purchase_items.quantity') : 0;
        $absAdjustedVal = $absAdjustments ? (int)$absAdjustments->sum('purchase_stock_adjustments.quantity_adjustment') : 0;

        $currentStock = $openingStock + $absPurchasedVal + $absAdjustedVal - $absSoldVal;

        return response()->json([
            'status' => 'success',
            'totalSold' => $totalSold,
            'totalPurchased' => $totalPurchased,
            'totalAdjusted' => $totalAdjusted,
            'currentStock' => $currentStock,
        ]);
    }
}
