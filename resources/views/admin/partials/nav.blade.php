<nav class="flex flex-wrap gap-2" aria-label="Navigasi administrasi">
    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-teal-800 text-white' : 'bg-white border border-gray-200 text-teal-800 hover:bg-teal-50' }}">Ringkasan</a>
    <a href="{{ route('admin.service-schedules.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs('admin.service-schedules.*') ? 'bg-teal-800 text-white' : 'bg-white border border-gray-200 text-teal-800 hover:bg-teal-50' }}">Jadwal Layanan BK</a>
    <a href="{{ route('admin.users.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs('admin.users.*') ? 'bg-teal-800 text-white' : 'bg-white border border-gray-200 text-teal-800 hover:bg-teal-50' }}">Pengguna</a>
    <a href="{{ route('admin.articles.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs('admin.articles.*') ? 'bg-teal-800 text-white' : 'bg-white border border-gray-200 text-teal-800 hover:bg-teal-50' }}">Artikel</a>
    <a href="{{ route('admin.categories.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs('admin.categories.*') ? 'bg-teal-800 text-white' : 'bg-white border border-gray-200 text-teal-800 hover:bg-teal-50' }}">Kategori</a>
</nav>
