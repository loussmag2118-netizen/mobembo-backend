{{-- Pagination de l'administration : « 6–10 sur 23 » + pages --}}
@if ($paginator->hasPages())
    @php
        $bouton = 'inline-flex items-center justify-center min-w-[2.25rem] h-9 px-3 rounded-xl text-sm font-bold transition';
        $inactif = 'text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800';
        $desactive = 'text-slate-300 dark:text-slate-600 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 cursor-not-allowed';
    @endphp
    <nav role="navigation" aria-label="Pagination" class="flex flex-col sm:flex-row items-center justify-between gap-3">
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
            <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            sur <span class="font-extrabold text-slate-800 dark:text-slate-100">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1.5 flex-wrap justify-center">
            @if ($paginator->onFirstPage())
                <span class="{{ $bouton }} {{ $desactive }}" aria-disabled="true"><i class="fa-solid fa-chevron-left text-xs"></i></span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $bouton }} {{ $inactif }}" aria-label="Page précédente"><i class="fa-solid fa-chevron-left text-xs"></i></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $bouton }} text-slate-400">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="{{ $bouton }} text-white shadow-sm" style="background: var(--agence-primaire, #16503c)">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $bouton }} {{ $inactif }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $bouton }} {{ $inactif }}" aria-label="Page suivante"><i class="fa-solid fa-chevron-right text-xs"></i></a>
            @else
                <span class="{{ $bouton }} {{ $desactive }}" aria-disabled="true"><i class="fa-solid fa-chevron-right text-xs"></i></span>
            @endif
        </div>
    </nav>
@endif
