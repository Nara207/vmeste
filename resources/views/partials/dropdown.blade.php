{{--
    Выпадающий список из ссылок (работает без JavaScript, на <details>).
    Нужны: $label (подпись), $items (массив ['text' => ..., 'url' => ..., 'on' => true/false]).
--}}
@php $current = collect($items)->firstWhere('on', true)['text'] ?? $items[0]['text']; @endphp
<details class="dd">
    <summary>
        <span class="dd-label">{{ $label }}</span>
        <b>{{ $current }}</b>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </summary>
    <div class="dd-menu">
        @foreach ($items as $item)
            <a class="dd-item {{ $item['on'] ? 'on' : '' }}" href="{{ $item['url'] }}">{{ $item['text'] }}</a>
        @endforeach
    </div>
</details>
