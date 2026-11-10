@extends('layouts.app')
@section('title', 'Слишком много попыток')

@section('content')
{{-- страница для ошибки 429: сработало ограничение частоты запросов (throttle в routes/web.php) --}}
<div class="eyebrow">Ошибка 429</div>
<h1 class="big">Слишком много попыток</h1>
<div class="rule"></div>

<div class="card">
    <p>Вы отправляли форму слишком часто. Это защита от спама: подождите немного и попробуйте ещё раз.</p>
    <p><a class="btn btn-outline" href="{{ url()->previous() }}">← Вернуться назад</a></p>
</div>
@endsection
