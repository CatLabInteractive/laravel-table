@if(count($filters) > 0)
    @include('table::filters', [
        'filters' => $filters,
        'action' => $filterAction,
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

                    <td>
                        @foreach($modelActions as $action)
                            @if($action->shouldShow($row['resource']))
                                <a href="{{ $action->getUrl($row['resource']) }}">{{ $action->getLabel() }}</a>
                            @endif
                        @endforeach
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
