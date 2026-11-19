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
                @if($filter->hasOperators())
                    <select class="form-control table-filter-operator" name="{{ $filter->getOperatorInputName() }}" id="table-filter-{{ $filter->getName() }}-op" aria-label="{{ $filter->getLabel() }}: {{ __('table::table.operator') }}">
                        @foreach($filter->getOperators() as $operator => $operatorLabel)
                            <option value="{{ $operator }}" data-takes-value="{{ \CatLab\Laravel\Table\Models\Filter::operatorTakesValue($operator) ? '1' : '0' }}" @if($operator === $filter->getOperator()) selected @endif>{{ $operatorLabel }}</option>
                        @endforeach
                    </select>
                @endif
                @if($filter->hasOptions())
                    <select class="form-control table-filter-value" name="{{ $filter->getValueInputName() }}" id="table-filter-{{ $filter->getName() }}" @if(!$filter->takesValue()) hidden disabled @endif>
                        <option value="">{{ __('table::table.any') }}</option>
                        @foreach($filter->getOptions() as $option)
                            <option value="{{ $option }}" @if((string) $option === (string) $filter->getValue()) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control table-filter-value" name="{{ $filter->getValueInputName() }}" id="table-filter-{{ $filter->getName() }}" value="{{ $filter->getValue() }}" placeholder="{{ __('table::table.any') }}" @if(!$filter->takesValue()) hidden disabled @endif>
                @endif
            </div>
        </div>
    @endforeach

    <div class="form-group table-filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">
            <span class="glyphicon glyphicon-search" aria-hidden="true"></span> {{ __('table::table.filter') }}
        </button>
        @if($active)
            <a href="{{ $clearUrl }}" class="btn btn-default btn-sm">
                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span> {{ __('table::table.clear') }}
            </a>
        @endif
    </div>
</form>
<script>
    (function () {
        // "is empty" / "is not empty" compare with nothing: hide (and do not send) the value.
        var form = document.currentScript && document.currentScript.previousElementSibling;
        if (!form) {
            return;
        }
        form.addEventListener('change', function (event) {
            var select = event.target;
            if (!select.classList || !select.classList.contains('table-filter-operator')) {
                return;
            }
            var option = select.options[select.selectedIndex];
            var takesValue = !option || option.getAttribute('data-takes-value') !== '0';
            var value = select.parentNode.querySelector('.table-filter-value');
            if (value) {
                value.hidden = !takesValue;
                value.disabled = !takesValue;
            }
        });
    })();
</script>
