<table class="table">
    <tr>
        @foreach($columns as $column)
            <th>{{ $column }}</th>
        @endforeach
        <th></th>
    </tr>

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

                <?php
                    $id = ($resource->getIdentifiers()->getValues())[0]->getValue();
                ?>
                @foreach($modelActions as $action)
                    <?php
                        $routeParameters = [
                            'id' => $id
                        ];

                        if ($action['routeParameters'] !== null) {
                            $routeParameters = array_merge($action['routeParameters'], [ 'id' => $id ]);
                        }

                        $routeParameters = array_merge($routeParameters, $action['queryParameters']);
                    ?>
                    <a href="{{ action($action['action'], $routeParameters) }}">{{ $action['label'] }}</a>
                @endforeach
            </td>
        </tr>
    @endforeach
</table>

@foreach($collectionActions as $action)
    <a class="btn btn-primary" href="{{ action($action['action'], array_merge($action['routeParameters'], $action['queryParameters'])) }}">
        {{ $action['label'] }}
    </a>
@endforeach