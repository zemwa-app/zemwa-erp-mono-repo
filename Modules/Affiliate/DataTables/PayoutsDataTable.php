<?php

namespace Modules\Affiliate\DataTables;

use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Modules\Affiliate\Entities\Payout;
use Modules\Affiliate\Enums\PaymentStatus;

class PayoutsDataTable extends BaseDataTable
{

    private $viewPayoutsPermission;
    private $editPayoutsPermission;
    private $deletePayoutsPermission;
    private $changePayoutsStatusPermission;

    public function __construct()
    {
        parent::__construct();

        $this->viewPayoutsPermission = user()->permission('view_payouts');
        $this->editPayoutsPermission = user()->permission('edit_payouts');
        $this->deletePayoutsPermission = user()->permission('delete_payouts');
        $this->changePayoutsStatusPermission = user()->permission('manage_payout_status');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->addColumn('affiliate_name', fn($row) => $row->affiliate->user->name)
            ->addColumn('balance', fn($row) => global_currency_format($row->balance))
            ->addColumn('amount_requested', fn($row) => global_currency_format($row->amount_requested))
            ->addColumn('payment_method', fn($row) => $row->payment_method->label())
            ->addColumn(('note'), fn($row) => $row->note ?? '-')
            ->editColumn('paid_at', fn($row) => $row->paid_at ? $row->paid_at->format(global_setting()->date_format) : '-')
            ->editColumn('created_at', fn($row) => $row->created_at->format(global_setting()->date_format))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->viewPayoutsPermission == 'all' || (user()->id == $row->affiliate->user_id)) {
                    $action .= '<a href="' . route('payout.show', $row->id) . '" class="openRightModal dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }

                if ($row->status == PaymentStatus::Pending) {

                    if ($this->editPayoutsPermission == 'all' || (user()->id == $row->affiliate->user_id)) {
                        $action .= '<a class="dropdown-item openRightModal" href="' . route('payout.edit', $row->id) . '" >
                            <i class="fa fa-edit mr-2"></i>
                            ' . trans('app.edit') . '
                        </a>';
                    }

                    if ($this->deletePayoutsPermission == 'all' || (user()->id == $row->affiliate->user_id)) {
                        $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-payout-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                    }
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('status', function ($row) {
                if ($this->changePayoutsStatusPermission == 'all') {

                    $select = '<select class="form-control select-picker change-payout-status" data-payout-id="' . $row->id . '">';

                    if ($row->status == PaymentStatus::Paid) {
                        $select .= '<option value="' . PaymentStatus::Paid->value . '" data-content="' . PaymentStatus::Paid->html() . '" selected>' . PaymentStatus::Paid->label() . '</option>';
                    }

                    if ($row->status == PaymentStatus::Pending) {
                        $select .= '<option value="' . PaymentStatus::Pending->value . '" data-content="' . PaymentStatus::Pending->html() . '" selected>' . PaymentStatus::Pending->label() . '</option>';
                        $select .= '<option value="' . PaymentStatus::Paid->value . '" data-content="' . PaymentStatus::Paid->html() . '">' . PaymentStatus::Paid->label() . '</option>';
                        $select .= '<option value="' . PaymentStatus::Canceled->value . '" data-content="' . PaymentStatus::Canceled->html() . '">' . PaymentStatus::Canceled->label() . '</option>';
                    }

                    if ($row->status == PaymentStatus::Canceled) {
                        $select .= '<option value="' . PaymentStatus::Canceled->value . '" data-content="' . PaymentStatus::Canceled->html() . '" selected>' . PaymentStatus::Canceled->label() . '</option>';
                    }

                    $select .= '</select>';

                    return $select;
                }

                if ($row->status == PaymentStatus::Paid) {
                    return PaymentStatus::Paid->html();
                }

                if ($row->status == PaymentStatus::Pending) {
                    return PaymentStatus::Pending->html();
                }

                if ($row->status == PaymentStatus::Canceled) {
                    return PaymentStatus::Canceled->html();
                }

            })
            ->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Payout $model)
    {
        $searchText = request('searchText');

        $model = $model->with('affiliate.user')
            ->withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->whereHas('affiliate.user', function ($query) use ($searchText) {
                $query->where('name', 'like', '%' . $searchText . '%');
            });

        if (request('affiliateUserId')) {
            $affiliateUserId = request('affiliateUserId');
            $model = $model->whereHas('affiliate', function ($query) use ($affiliateUserId) {
                // Filter based on the affiliate ID
                $query->where('user_id', $affiliateUserId);
            });
        }

        if (request('affiliateId')) {
            $affiliateId = request('affiliateId');
            $model = $model->where('affiliate_id', $affiliateId);
        }

        return $model;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('payout-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["payout-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".select-picker").selectpicker();
                }',
            ]);

        $dataTable->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => true],
            __('affiliate::app.affiliateName') => ['data' => 'affiliate_name', 'name' => 'affiliate.user.name', 'title' => __('affiliate::app.affiliateName'), 'sortable' => 'affiliate.user.name'],
            __('affiliate::app.balance') => ['data' => 'balance', 'name' => 'affiliate_payouts.balance', 'title' => __('affiliate::app.balance')],
            __('affiliate::app.amountRequested') => ['data' => 'amount_requested', 'name' => 'affiliate_payouts.amount_requested', 'title' => __('affiliate::app.amountRequested')],
            __('affiliate::app.paymentMethod') => ['data' => 'payment_method', 'name' => 'affiliate_payouts.payment_method', 'title' => __('affiliate::app.paymentMethod')],
            __('app.note') => ['data' => 'note', 'name' => 'affiliate_payouts.note', 'title' => __('affiliate::app.paymentDetails')],
            __('affiliate::app.paidAt') => ['data' => 'paid_at', 'name' => 'affiliate_payouts.paid_at', 'title' => __('affiliate::app.paidAt')],
            __('app.createdAt') => ['data' => 'created_at', 'name' => 'affiliate_payouts.created_at', 'title' => __('app.createdAt')],
            __('app.status') => ['data' => 'status', 'name' => 'affiliate_payouts.status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
