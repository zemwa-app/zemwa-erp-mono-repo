<?php

namespace App\DataTables;

use App\Helper\Common;
use App\Models\LeadForm;
use Yajra\DataTables\Html\Column;

class LeadFormsDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a href="' . route('lead-forms.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-cog mr-2"></i>' . __('modules.lead.configureFields') . '</a>';
            $action .= '<a class="dropdown-item openRightModal" href="' . route('lead-forms.edit', [$row->id]) . '"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            $action .= '<a class="dropdown-item copy-form-link" href="javascript:;" data-link="' . e($row->public_url) . '"><i class="fa fa-link mr-2"></i>' . __('modules.lead.copyFormLink') . '</a>';

            if (!$row->is_default) {
                $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . __('app.delete') . '</a>';
            }

            $action .= '</div></div></div>';

            return $action;
        });

        $datatables->editColumn('name', function ($row) {
            $defaultBadge = $row->is_default ? ' <span class="badge badge-secondary">' . __('app.default') . '</span>' : '';

            return '<a href="' . route('lead-forms.show', [$row->id]) . '">' . e($row->name) . '</a>' . $defaultBadge;
        });

        $datatables->editColumn('status', function ($row) {
            return $row->status == 'active'
                ? '<i class="fa fa-circle mr-1 text-dark-green"></i>' . __('app.active')
                : '<i class="fa fa-circle mr-1 text-red"></i>' . __('app.inactive');
        });

        $datatables->editColumn('pipeline', function ($row) {
            return $row->pipeline?->name ?? '--';
        });

        $datatables->addColumn('submissions', function ($row) {
            return $row->deals_count ?? 0;
        });

        $datatables->editColumn('slug', fn($row) => e($row->slug));

        $datatables->smart(false);
        $datatables->setRowId(fn($row) => 'row-' . $row->id);
        $datatables->rawColumns(['action', 'name', 'status']);

        return $datatables;
    }

    public function query(LeadForm $model)
    {
        $query = $model->newQuery()
            ->with(['pipeline', 'company'])
            ->withCount('deals');

        if ($this->request()->searchText != '') {
            $safeTerm = Common::safeString(request('searchText'));
            $query->where(function ($q) use ($safeTerm) {
                $q->where('name', 'like', '%' . $safeTerm . '%')
                    ->orWhere('slug', 'like', '%' . $safeTerm . '%');
            });
        }

        return $query;
    }

    public function html()
    {
        return $this->setBuilder('lead-forms-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["lead-forms-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    });
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('modules.lead.formSlug') => ['data' => 'slug', 'name' => 'slug', 'title' => __('modules.lead.formSlug')],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            __('modules.deal.pipeline') => ['data' => 'pipeline', 'name' => 'pipeline.name', 'title' => __('modules.deal.pipeline')],
            __('modules.lead.submissions') => ['data' => 'submissions', 'name' => 'submissions', 'title' => __('modules.lead.submissions'), 'orderable' => false, 'searchable' => false],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
