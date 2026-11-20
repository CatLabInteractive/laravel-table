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
                    {{-- The condition picks the name of the value input: name, name[neq], ... (no name of its own). --}}
                    <select class="form-control table-filter-operator" id="table-filter-{{ $filter->getName() }}-op" aria-label="{{ $filter->getLabel() }}: {{ __('table::table.operator') }}">
                        @foreach($filter->getOperators() as $operator => $operatorLabel)
                            <option value="{{ $operator }}" data-input-name="{{ $filter->getInputNameFor($operator) }}" data-takes-value="{{ \CatLab\Laravel\Table\Models\Filter::operatorTakesValue($operator) ? '1' : '0' }}" @if($operator === $filter->getOperator()) selected @endif>{{ $operatorLabel }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" class="table-filter-flag" value="1" @if(!$filter->takesValue()) name="{{ $filter->getValueInputName() }}" @else disabled @endif>
                @endif
                @if($filter->hasOptions())
                    <select class="form-control table-filter-value" @if($filter->takesValue()) name="{{ $filter->getValueInputName() }}" @else hidden disabled @endif id="table-filter-{{ $filter->getName() }}">
                        <option value="">{{ __('table::table.any') }}</option>
                        @foreach($filter->getOptions() as $option)
                            <option value="{{ $option }}" @if((string) $option === (string) $filter->getValue()) selected @endif>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control table-filter-value" @if($filter->takesValue()) name="{{ $filter->getValueInputName() }}" @else hidden disabled @endif id="table-filter-{{ $filter->getName() }}" value="{{ $filter->getValue() }}" placeholder="{{ __('table::table.any') }}">
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
        // The condition renames the value input (name, name[neq], ...); "is
        // empty" / "is not empty" send a flag (name[empty]=1) instead of the value.
        var forms = document.querySelectorAll('form.table-filters:not([data-filters-ready])');
        Array.prototype.forEach.call(forms, function (form) {
            form.setAttribute('data-filters-ready', '1');
            form.addEventListener('change', function (event) {
                var select = event.target;
                if (!select.classList || !select.classList.contains('table-filter-operator')) {
                    return;
                }
                var option = select.options[select.selectedIndex];
                if (!option) {
                    return;
                }
                var name = option.getAttribute('data-input-name');
                var takesValue = option.getAttribute('data-takes-value') !== '0';
                var group = select.parentNode;
                var value = group.querySelector('.table-filter-value');
                var flag = group.querySelector('.table-filter-flag');
                if (value) {
                    value.hidden = !takesValue;
                    value.disabled = !takesValue;
                    if (takesValue) {
                        value.name = name;
                    } else {
                        value.removeAttribute('name');
                    }
                }
                if (flag) {
                    flag.disabled = takesValue;
                    if (takesValue) {
                        flag.removeAttribute('name');
                    } else {
                        flag.name = name;
                    }
                }
            });
        });
    })();
</script>
