@if(count($filters) > 0)
    @include('table::filters', [
        'filters' => $filters,
        'action' => $filterAction,
        'clearUrl' => $filterClearUrl,
        'hidden' => $filterHiddenParameters
    ])
@endif

@if(count($rows) > 0)

    @if($pagination)
        @include('table::pagination', [ 'pagination' => $pagination ])
    @endif

    <table class="table table-striped">

        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>
                        @if($column->isSortable())
                            <a href="{{ $column->getSortUrl() }}">
                                {{ $column->getLabel() }}
                                @if($column->isSortedAscending())
                                    &#9650;
                                @elseif($column->isSortedDescending())
                                    &#9660;
                                @endif
                            </a>
                        @else
                            {{ $column->getLabel() }}
                        @endif
                    </th>
                @endforeach
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($columns as $column)
                        @php($cell = $row['cells'][$column->getKey()] ?? null)
                        <td>@if($cell)@include('table::cell', [ 'cell' => $cell ])@endif</td>
                    @endforeach

                    <td class="table-actions text-right text-end text-nowrap">
                        @foreach($row['iconActions'] as $action)
                            @include('table::action', [ 'action' => $action, 'resource' => $row['resource'], 'menu' => false ])
                        @endforeach

                        @if(count($row['menuActions']) > 0)
                            <div class="dropdown d-inline-block table-actions-menu">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary table-action table-action-more"
                                    data-toggle="dropdown"
                                    data-bs-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false"
                                    title="More actions"
                                    aria-label="More actions"
                                >@include('table::icons.more')</button>
                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-end">
                                    @foreach($row['menuActions'] as $action)
                                        @include('table::action', [ 'action' => $action, 'resource' => $row['resource'], 'menu' => true ])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($pagination)
        @include('table::pagination', [ 'pagination' => $pagination ])
    @endif
@else
    <p>No data set.</p>
@endif

@foreach($collectionActions as $action)
    <a class="btn btn-primary" href="{{ $action->getUrl() }}">
        {{ $action->getLabel() }}
    </a>
@endforeach
