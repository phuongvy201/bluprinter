@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="catalog-paginator">
        <ul class="catalog-paginator__list">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="catalog-paginator__item catalog-paginator__item--dots" aria-disabled="true">
                        <span>{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="catalog-paginator__item catalog-paginator__item--active" aria-current="page">
                                <span>{{ $page }}</span>
                            </li>
                        @else
                            <li class="catalog-paginator__item">
                                <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </ul>
    </nav>
@endif
