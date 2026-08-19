<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Keep a single stock row per product (prefer latest quantity row).
            DB::statement("
                UPDATE purchase_stock_adjustments psa
                INNER JOIN (
                    SELECT
                        product_id,
                        COALESCE(
                            MAX(CASE WHEN type = 'quantity' THEN id END),
                            MAX(id)
                        ) AS keep_id
                    FROM purchase_stock_adjustments
                    WHERE product_id IS NOT NULL
                    GROUP BY product_id
                ) keep_rows ON keep_rows.keep_id = psa.id
                LEFT JOIN (
                    SELECT pi.product_id, SUM(pi.quantity) AS quantity_in
                    FROM purchase_items pi
                    INNER JOIN purchase_orders po ON po.id = pi.purchase_order_id
                    WHERE pi.product_id IS NOT NULL
                        AND po.delivery_status = 'delivered'
                    GROUP BY pi.product_id
                ) purchase_in ON purchase_in.product_id = psa.product_id
                LEFT JOIN (
                    SELECT ii.product_id, SUM(ii.quantity) AS quantity_out
                    FROM invoice_items ii
                    INNER JOIN invoices i ON i.id = ii.invoice_id
                    WHERE ii.product_id IS NOT NULL
                        AND i.status IN ('paid', 'partial', 'unpaid')
                    GROUP BY ii.product_id
                ) invoice_out ON invoice_out.product_id = psa.product_id
                SET psa.type = 'quantity',
                    psa.net_quantity = COALESCE(purchase_in.quantity_in, 0) - COALESCE(invoice_out.quantity_out, 0),
                    psa.quantity_adjustment = 0,
                    psa.status = 'converted',
                    psa.updated_at = NOW()
            ");

            DB::statement("
                DELETE psa
                FROM purchase_stock_adjustments psa
                INNER JOIN (
                    SELECT
                        product_id,
                        COALESCE(
                            MAX(CASE WHEN type = 'quantity' THEN id END),
                            MAX(id)
                        ) AS keep_id
                    FROM purchase_stock_adjustments
                    WHERE product_id IS NOT NULL
                    GROUP BY product_id
                ) keep_rows ON keep_rows.product_id = psa.product_id
                WHERE psa.id <> keep_rows.keep_id
            ");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data correction migration; no down migration.
    }
};
