<?php

namespace Modules\Purchase\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Entities\PurchaseInventory;
use Modules\Purchase\Entities\PurchaseNotificationSetting;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Entities\PurchaseStockAdjustment;

class NewPurchaseInventory extends BaseNotification
{

    /**
     * @var array<int|string>
     */
    private $productIds;

    private $purchaseInventory;

    private $emailSetting;

    /**
     * Create a new notification instance.
     *
     * @param  array<int|string>|int|string  $productIds
     * @return void
     */
    public function __construct($productIds, PurchaseInventory $purchaseInventory)
    {
        $this->productIds = is_array($productIds) ? $productIds : [$productIds];
        $this->purchaseInventory = $purchaseInventory;
        $this->company = $purchaseInventory->company;
        $this->emailSetting = PurchaseNotificationSetting::where('company_id', $purchaseInventory->company_id)
            ->where('slug', 'new-purchase-inventory')
            ->first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($this->emailSetting && $this->emailSetting->send_email == 'yes' && $notifiable->email != '') {
            array_push($via, 'mail');
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $content1 = [];

        foreach ($this->productIds as $productId) {
            $product = PurchaseProduct::find($productId);
            if (!$product) {
                continue;
            }

            $stock = PurchaseStockAdjustment::where('inventory_id', $this->purchaseInventory->id)
                ->where('product_id', $productId)
                ->first();

            if (!$stock) {
                continue;
            }

            if ($stock->type == 'quantity') {
                $content1[$product->name] = $stock->net_quantity . '(quantity)';
            }
            else {
                $content1[$product->name] = $stock->changed_value . '(value)';
            }
        }

        if ($content1 === []) {
            Log::warning('No matching stock rows for NewPurchaseInventory notification.', [
                'inventory_id' => $this->purchaseInventory->id,
                'product_ids' => $this->productIds,
            ]);
        }

        $url = route('purchase-inventory.show', $this->purchaseInventory->id);
        $url = getDomainSpecificUrl($url, $this->company);

        $content = __('purchase::email.purchaseInventory.text');
        $values = '';

        foreach ($content1 as $key => $abcd) {
            $values .= '<span>' . $key . ' : ' . $abcd . '</span><br>';
        }

        $newInventory = parent::build();

        $newInventory->subject(__('purchase::email.purchaseInventory.subject'))
            ->markdown('mail.email', [
                'url' => $url,
                'content' => $content . '<br>' . $values,
                'themeColor' => $notifiable->company->header_color,
                'actionText' => __('purchase::email.purchaseInventory.viewInventory'),
                'notifiableName' => $notifiable->name
            ]);

        return $newInventory;
    }

}
