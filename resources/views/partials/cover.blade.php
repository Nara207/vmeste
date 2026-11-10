{{--
    Обложка книги. Нужна переменная $book, необязательно $class (big-cover или mini-cover).
    Есть загруженная картинка — показываем её, иначе рисуем обложку по умолчанию (арка с названием).
--}}
@if ($book->cover_path)
    <div class="cover cover-img {{ $class ?? '' }}"><img src="{{ asset($book->cover_path) }}" alt="Обложка «{{ $book->title }}»" loading="lazy"></div>
@else
    <div class="cover cov-{{ $book->id % 6 }} {{ $class ?? '' }}"><span>{{ $book->title }}</span></div>
@endif
