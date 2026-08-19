<?php

namespace Modules\Affiliate\DataTables;

use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Button;
use Modules\Affiliate\Entities\Referral;

class ReferralsDataTable extends BaseDataTable
{
    private $viewReferralPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewReferralPermission = user()->permission('view_referrals');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->editColumn('referral_id', fn($row) => $row->id);
        $datatables->editColumn('affiliate_id', fn($row) => $row->affiliate->user->name ?? '--');
        $datatables->editColumn('company_id', fn($row) => $row->company->company_name ?? '--');
        $datatables->editColumn('commissions', fn($row) => global_currency_format($row->commissions) ?? '--');
        $datatables->editColumn('created_date', fn($row) => $row->created_at->timezone(global_setting()->timezone)->translatedFormat(global_setting()->date_format));
        $datatables->addIndexColumn();
        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->rawColumns(['company_id', 'affiliate_id', 'commissions', 'created_date']);

        return $datatables;
    }

    /**
     * @param Referral $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Referral $model)
    {
        $request = $this->request();

        $model = $model->select('affiliate_referrals.*')
            ->join('companies', 'affiliate_referrals.company_id', '=', 'companies.id')
            ->join('affiliates', 'affiliate_referrals.affiliate_id', '=', 'affiliates.id')
            ->with('affiliate');

        if ($request->searchText != '') {
            $model->where(function ($query) {
                $query->where('companies.company_name', 'like', '%' . request('searchText') . '%')
                    ->orWhere('companies.company_email', 'like', '%' . request('searchText') . '%')
                    ->orWhereHas('affiliate.user', function ($query) {
                        $query->where('name', 'like', '%' . request('searchText') . '%');
                        $query->orWhere('email', 'like', '%' . request('searchText') . '%');
                    });
            });
        }

        if ($request->company_id !== null && $request->company_id != 'null' && $request->company_id != '' && $request->company_id != 'all') {
            $model = $model->where('company_id', $request->company_id);
        }

        if ($request->affiliate_id !== null && $request->affiliate_id != 'null' && $request->affiliate_id != '' && $request->affiliate_id != 'all') {
            $model = $model->where('affiliate_id', $request->affiliate_id);
        }

        if ($request->affiliateId) {
            $affiliateId = $request->affiliateId;
            $model = $model->where('affiliate_id', $affiliateId);
        }

        if ($request->affiliateUserId) {
            $affiliateUserId = $request->affiliateUserId;
            $model = $model->where('affiliates.user_id', $affiliateUserId);
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
        return $this->setBuilder('referrals-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["referrals-table"].buttons().container()
                    .appendTo( "#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        $data = [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'exportable' => false],
            __('app.id') => ['data' => 'referral_id', 'name' => 'id', 'title' => __('app.id')],
            __('affiliate::app.company') . ' ' . __('affiliate::app.referred') => ['data' => 'company_id', 'name' => 'company_id', 'title' => __('affiliate::app.company') . ' ' . __('affiliate::app.referred')],
            __('affiliate::app.affiliate') => ['data' => 'affiliate_id', 'name' => 'affiliate_id', 'title' => __('affiliate::app.affiliate')],
            __('affiliate::app.commissions') => ['data' => 'commissions', 'name' => 'commissions', 'title' => __('affiliate::app.commissions')],
            __('affiliate::app.dateCreated') => ['data' => 'created_date', 'name' => 'created_at', 'title' => __('affiliate::app.dateCreated')],
        ];

        return $data;
    }

}
