<?php

namespace Modules\Affiliate\Views\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AffiliateOption extends Component
{
    public $user;
    public $selected;
    public $affiliateId;
    public $additionalText;

    /**
     * Create a new component instance.
     */
    public function __construct($user, $selected = false, $affiliateId = null, $additionalText = null)
    {
        $this->user = $user;
        $this->selected = $selected;
        $this->affiliateId = $affiliateId;
        $this->additionalText = $additionalText;
    }

    /**
     * Get the view/contents that represent the component.
     */
    public function render(): View|string
    {
        return view('affiliate::components.affiliate-option');
    }

}
