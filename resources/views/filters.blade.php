@php($active = count(array_filter($filters, function ($filter) { return $filter->isActive(); })) > 0)
<form method="get" action="{{ $action }}" class="table-filters form-inline" role="search">
    @foreach($hidden as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach

    @foreach($filters as $filter)
        <div class="form-group table-filter">
            <label class="sr-only" for="table-filter-{{ $filter->getName() }}">{{ $filter->getLabel() }}</label>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">{{ $filter->getLabel() }}</span>
                @if($filter->hasOptions())
                    <select class="form-control" name="{{ $filter->getName() }}" id="table-filter-{{ $filter->getName() }}">
                        <option value="">Any</option>
                        @foreach($filter->getOptions() as $option)
                            <option value="{{ $option }}" @if((string) $option === (string) $filter->getValue()) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" name="{{ $filter->getName() }}" id="table-filter-{{ $filter->getName() }}" value="{{ $filter->getValue() }}" placeholder="Any">
                @endif
            </div>
        </div>
    @endforeach

    <div class="form-group table-filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span> Filter
        </button>
        @if($active)
            <a href="{{ $clearUrl }}" class="btn btn-default btn-sm">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Clear
            </a>
        @endif
    </div>
</form>
