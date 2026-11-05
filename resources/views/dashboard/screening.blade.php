<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Skrining Stres Akademik - RuangRileks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <h1 class="text-xl font-semibold">RuangRileks - Skrining Stres Akademik</h1>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-red-600">Logout</button>
                </form>
            </div>
        </header>
        <main class="flex-1">
            <div class="max-w-5xl mx-auto px-4 py-8">
                <h2 class="text-2xl font-bold mb-4">Skrining Stres Akademik</h2>
                <div class="bg-white shadow rounded p-6">
                    <p class="text-gray-600">Halaman screening placeholder untuk siswa/guru BK.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
