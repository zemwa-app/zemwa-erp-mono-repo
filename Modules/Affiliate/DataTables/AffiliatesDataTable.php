<?php

namespace Modules\Affiliate\DataTables;

use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;
use Modules\Affiliate\Enums\Status;
use Modules\Affiliate\Entities\Affiliate;

class AffiliatesDataTable extends BaseDataTable
{

    private $viewAffiliatesPermission;
    private $deleteAffiliatesPermission;
    private $manageAffiliateStatusPermission;

    public function __construct()
    {
        parent::__construct();

        $this->viewAffiliatesPermission = user()->permission('view_affiliates');
        $this->deleteAffiliatesPermission = user()->permission('delete_affiliates');
        $this->manageAffiliateStatusPermission = user()->permission('manage_affiliate_status');
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
            ->addColumn('name', fn($row) => $row->user->name)
            ->addColumn('email', fn($row) => $row->user->email)
            ->editColumn('balance', fn($row) => global_currency_format($row->balance))
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">

                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if ($this->viewAffiliatesPermission == 'all') {
                    $action .= '<a href="' . route('affiliate.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }

                if ($this->deleteAffiliatesPermission == 'all') {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-toggle="tooltip"  data-affiliate-id="' . $row->id . '">
                            <i class="fa fa-trash mr-2"></i>
                            ' . trans('app.delete') . '
                        </a>';
                }

                $action .= '</div>
                </div>
            </div>';

                return $action;
            })
            ->addColumn('status', function ($row) {
                if ($this->manageAffiliateStatusPermission == 'all') {
                    $select = '<select class="form-control select-picker change-affiliate-status" data-affiliate-id="' . $row->id . '">';

                    foreach (Status::cases() as $status) {
                        $select .= '<option value="' . $status->value . '" data-content="' . $status->html() . '" ' . ($status == $row->status ? 'selected' : '') . '>' . $status->label() . '</option>';
                    }

                    $select .= '</select>';

                    return $select;
                }

                return $row->status;
            })
            ->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Affiliate $model)
    {
        $searchText = request('searchText');

        return $model->select(['affiliates.id', 'affiliates.status', 'affiliates.user_id', 'affiliates.balance'])
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'email', 'created_at');
            }])
            ->withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->whereHas('user', function ($query) use ($searchText) {
                $query->where('name', 'like', '%' . $searchText . '%')
                    ->orWhere('email', 'like', '%' . $searchText . '%');
            });

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->setBuilder('affiliate-table', 0)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["affiliate-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                    $(".select-picker").selectpicker();
                }',
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            '#' => ['data' => 'id', 'name' => 'id', 'visible' => false],
            __('app.name') => ['data' => 'user.name', 'name' => 'user.name', 'title' => __('app.name')],
            __('app.email') => ['data' => 'user.email', 'name' => 'user.email', 'title' => __('app.email')],
            __('affiliate::app.balance') => ['data' => 'balance', 'name' => 'balance', 'title' => __('affiliate::app.balance')],
            __('app.status') => ['data' => 'status', 'name' => 'affiliates.status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->width(150)
                ->addClass('text-right pr-20')
        ];
    }

}
