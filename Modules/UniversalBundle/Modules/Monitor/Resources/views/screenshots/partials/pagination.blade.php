@if ($paginator->hasPages())
    <div class="mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between border-top-grey pt-3">
        <p class="mb-2 mb-sm-0 f-14 text-dark-grey">
            @lang('monitor::app.showingResults', [
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ])
        </p>
        {{ $paginator->links('monitor::pagination.bootstrap') }}
    </div>
@endif
