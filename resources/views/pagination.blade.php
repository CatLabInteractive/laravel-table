<ul class="pagination justify-content-center">
    @if($pagination->hasPrevious())
        <li class="page-item"><a class="page-link" href="{{ $pagination->getPreviousUrl()->getUrl() }}">{{ $pagination->getPreviousUrl()->getLabel() }}</a></li>
    @endif

    @foreach($pagination->getPages() as $page)
        <li class="page-item @if($page->isActive()) active @endif"><a class="page-link" href="{{ $page->getUrl() }}">{{ $page->getLabel() }}</a></li>
    @endforeach

    @if($pagination->hasNext())
        <li class="page-item"><a class="page-link" href="{{ $pagination->getNextUrl()->getUrl() }}">{{ $pagination->getNextUrl()->getLabel() }}</a></li>
    @endif
</ul>
