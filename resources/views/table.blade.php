@if(count($resources) > 0)

    @if($pagination)
        @include('table::pagination', [ 'pagination' => $pagination ])
    @endif

    <table class="table table-striped">

        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
                <th></th>
            </tr>
        </thead>

        <tbody>
            @foreach($resources as $resource)

                <tr>
                    @foreach($resource->toArray() as $col)
                        @if(is_array($col))
                            <td>relationship?</td>
                        @else
                            <td>{{ $col }}</td>
                        @endif

                    @endforeach

                    <td>
                        @foreach($modelActions as $action)
                            @if($action->shouldShow($resource))
                                <a href="{{ $action->getUrl($resource) }}">{{ $action->getLabel() }}</a>
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
