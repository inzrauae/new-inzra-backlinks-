@props(['items' => []])
@if (count($items) > 1)
<nav aria-label="Breadcrumb" class="pdp__crumb reveal">
  <ol>
    @foreach ($items as $i => $item)
      <li>
        @if (! empty($item['item']) && $i < count($items) - 1)
          <a href="{{ $item['item'] }}">{{ $item['name'] }}</a>
        @else
          <span @if ($i === count($items) - 1) aria-current="page" @endif>{{ $item['name'] }}</span>
        @endif
      </li>
      @if ($i < count($items) - 1)
        <li aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></li>
      @endif
    @endforeach
  </ol>
</nav>
@endif
