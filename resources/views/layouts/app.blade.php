<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-cream-50 text-teal-900 font-sans antialiased">
    <nav class="bg-teal-700 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('beranda') }}" class="text-xl font-bold tracking-tight">RuangRileks</a>
                <div class="hidden md:flex space-x-6 text-sm font-medium">
                    <a href="{{ route('beranda') }}" class="hover:text-teal-200 {{ request()->routeIs('beranda') ? 'text-teal-200' : '' }}">Beranda</a>
                    <a href="{{ route('tentang') }}" class="hover:text-teal-200 {{ request()->routeIs('tentang') ? 'text-teal-200' : '' }}">Tentang</a>
                    <a href="{{ route('edukasi') }}" class="hover:text-teal-200 {{ request()->routeIs('edukasi') ? 'text-teal-200' : '' }}">Edukasi</a>
                    <a href="{{ route('informasi-bk') }}" class="hover:text-teal-200 {{ request()->routeIs('informasi-bk') ? 'text-teal-200' : '' }}">Informasi BK</a>
                </div>
                <div class="hidden md:flex items-center space-x-4 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-teal-600 hover:bg-teal-500 px-4 py-2 rounded-lg">Dasbor</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-white/10 hover:bg-white/20 border border-white/30 px-4 py-2 rounded-lg transition">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-teal-200">Masuk</a>
                        <a href="{{ route('register') }}" class="bg-teal-600 hover:bg-teal-500 px-4 py-2 rounded-lg">Daftar</a>
                    @endauth
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        @include('components.notification-bell')
                    @endauth
                    <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md hover:bg-teal-600" aria-label="Buka menu navigasi">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-teal-800 px-4 pt-2 pb-4 space-y-2 text-sm">
            <a href="{{ route('beranda') }}" class="block py-2 hover:text-teal-200">Beranda</a>
            <a href="{{ route('tentang') }}" class="block py-2 hover:text-teal-200">Tentang</a>
            <a href="{{ route('edukasi') }}" class="block py-2 hover:text-teal-200">Edukasi</a>
            <a href="{{ route('informasi-bk') }}" class="block py-2 hover:text-teal-200">Informasi BK</a>
            @auth
                <a href="{{ route('dashboard') }}" class="block py-2 hover:text-teal-200">Dasbor</a>
                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full text-left rounded-lg border border-white/30 bg-white/10 hover:bg-white/20 px-3 py-2">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block py-2 hover:text-teal-200">Masuk</a>
                <a href="{{ route('register') }}" class="block py-2 hover:text-teal-200">Daftar</a>
            @endauth
        </div>
    </nav>

    <main class="min-h-[calc(100vh-8rem)]">
        @yield('content')
    </main>

    <footer class="bg-teal-900 text-teal-100 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm">
            <p>&copy; {{ date('Y') }} RuangRileks. Seluruh hak cipta dilindungi.</p>
        </div>
    </footer>

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => { menu.classList.toggle('hidden'); });
    </script>
</body>
</html>
