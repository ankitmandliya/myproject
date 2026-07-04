@if (! empty($breadcrumbs))
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            @foreach ($breadcrumbs as $breadcrumb)
                <li class="nav-home">
                    @if (! empty($breadcrumb['url']))
                        <a href="{{ $breadcrumb['url'] }}">
                            @if ($loop->first)
                                <i class="fas fa-home"></i>
                            @else
                                {{ $breadcrumb['label'] }}
                            @endif
                        </a>
                    @else
                        <span>{{ $breadcrumb['label'] }}</span>
                    @endif
                </li>
                @unless ($loop->last)
                    <li class="separator">
                        <i class="fas fa-angle-right"></i>
                    </li>
                @endunless
            @endforeach
        </ul>
    </div>
@endif
