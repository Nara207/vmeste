<div class="tabs">
    <a class="tab {{ request()->routeIs('admin.books*', 'admin.chapters*') ? 'on' : '' }}" href="{{ route('admin.books') }}">Книги</a>
    @php $newRequests = \App\Models\BookRequest::where('status', 'new')->count(); @endphp
    <a class="tab {{ request()->routeIs('admin.requests*') ? 'on' : '' }}" href="{{ route('admin.requests') }}">Заявки@if ($newRequests) <span class="tab-count">{{ $newRequests }}</span>@endif</a>
    <a class="tab {{ request()->routeIs('admin.users*') ? 'on' : '' }}" href="{{ route('admin.users') }}">Пользователи</a>
    <a class="tab {{ request()->routeIs('admin.rooms*') ? 'on' : '' }}" href="{{ route('admin.rooms') }}">Комнаты</a>
    <a class="tab {{ request()->routeIs('admin.log') ? 'on' : '' }}" href="{{ route('admin.log') }}">Журнал</a>
    <a class="tab {{ request()->routeIs('admin.announcements*') ? 'on' : '' }}" href="{{ route('admin.announcements') }}">Объявления</a>
</div>
