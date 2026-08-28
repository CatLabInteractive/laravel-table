@if($cell->isRelationship())
    @foreach($cell->getRelated() as $related)
        @if($related->hasUrl())
            <a href="{{ $related->getUrl() }}">{{ $related->getLabel() }}</a>
        @else
            {{ $related->getLabel() }}
        @endif
        @if(!$loop->last), @endif
    @endforeach
@elseif($cell->isObject())
    {{ json_encode($cell->getValue()) }}
@else
    {{ $cell->getValue() }}
@endif
