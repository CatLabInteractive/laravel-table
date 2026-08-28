<form method="get" action="{{ $action }}" class="form-inline table-filters">
    @foreach($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    @foreach($filters as $filter)
        <div class="form-group">
            <label for="table-filter-{{ $filter->getName() }}">{{ $filter->getName() }}</label>
            @if($filter->hasOptions())
                <select class="form-control" name="{{ $filter->getName() }}" id="table-filter-{{ $filter->getName() }}">
                    <option value=""></option>
                    @foreach($filter->getOptions() as $option)
                        <option value="{{ $option }}" @if((string) $option === (string) $filter->getValue()) selected @endif>{{ $option }}</option>
                    @endforeach
                </select>
            @else
                <input type="text" class="form-control" name="{{ $filter->getName() }}" id="table-filter-{{ $filter->getName() }}" value="{{ $filter->getValue() }}">
            @endif
        </div>
    @endforeach

    <button type="submit" class="btn btn-default">Filter</button>
    <a href="{{ $action }}" class="btn btn-link">Reset</a>
</form>
