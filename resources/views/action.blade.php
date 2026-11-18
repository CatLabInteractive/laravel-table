{{--
    One row action: $action (ModelAction) for $resource, as a button next to
    the row or ($menu) as an entry of the row's dropdown menu. A link, or a
    form for an action with a method or a select.
--}}
@php
    $url = $action->getUrl($resource);
    $label = $action->getLabel();
    $confirm = $action->getConfirm();
    $iconOnly = !$menu && !$action->hasLabelButton();
    $class = $menu
        ? 'dropdown-item'
        : 'btn btn-sm btn-' . $action->getStyle() . ' table-action' . ($action->getIcon() !== null ? ' table-action-' . $action->getIcon() : '');
    $options = $action->isForm() && $action->getOptionsName() !== null ? $action->getOptions($resource) : [];
    $selected = count($options) > 0 ? $action->getSelectedOption($resource) : null;
@endphp
@if($action->isForm())
    <form method="post" action="{{ $url }}" class="{{ $menu ? 'table-action-form' : 'd-inline-block table-action-form' }}"
          @if($confirm !== null) onsubmit="return confirm({{ json_encode($confirm) }});" @endif>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        @if($action->getMethod() !== 'post')
            <input type="hidden" name="_method" value="{{ strtoupper($action->getMethod()) }}">
        @endif
        @if(count($options) > 0)
            <select name="{{ $action->getOptionsName() }}" class="form-control form-control-sm d-inline-block w-auto table-action-select" aria-label="{{ $label }}">
                @foreach($options as $value => $display)
                    <option value="{{ $value }}" @if((string) $value === $selected) selected @endif>{{ $display }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" class="{{ $class }}" @if($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif>@if($iconOnly)@include('table::icons.' . $action->getIcon())@else{{ $label }}@endif</button>
    </form>
@else
    <a href="{{ $url }}" class="{{ $class }}"
       @if($iconOnly) title="{{ $label }}" aria-label="{{ $label }}" @endif
       @if($confirm !== null) onclick="return confirm({{ json_encode($confirm) }});" @endif
    >@if($iconOnly)@include('table::icons.' . $action->getIcon())@else{{ $label }}@endif</a>
@endif
