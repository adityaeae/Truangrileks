<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil - RuangRileks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold">RuangRileks</h1>
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard') }}" class="text-sm text-teal-700 hover:text-teal-500">Dasbor</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-5xl mx-auto px-4 py-8">
                <h2 class="text-2xl font-bold mb-6">Profil Saya</h2>
                <div class="bg-white shadow rounded p-6 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama</p>
                        <p class="text-lg font-medium">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-lg font-medium">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Peran</p>
                        <p class="text-lg font-medium capitalize">{{ $user->role }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
