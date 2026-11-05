<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - RuangRileks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Masuk</h1>

            @if(session('error'))
                <div class="mb-4 text-sm text-red-700 bg-red-100 border border-red-200 rounded p-3">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2">
                    @error('email')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password" required class="w-full border rounded px-3 py-2">
                    @error('password')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white rounded py-2">Login</button>
            </form>

            <p class="mt-4 text-sm text-center">Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600">Daftar</a></p>
        </div>
    </div>
</body>
</html>
