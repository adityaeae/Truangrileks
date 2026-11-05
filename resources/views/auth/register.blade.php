<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - RuangRileks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md bg-white shadow rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-4">Daftar Akun</h1>

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border rounded px-3 py-2">
                    @error('name')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
                </div>
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
                <div>
                    <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border rounded px-3 py-2">
                </div>
                <div class="rounded-lg bg-teal-50 border border-teal-100 p-3 text-sm text-teal-800">
                    Pendaftaran publik hanya tersedia untuk siswa. Untuk membuat akun Guru BK atau admin, silakan hubungi administrator sekolah.
                </div>
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white rounded-lg py-3 font-semibold transition">Daftar</button>
            </form>

            <p class="mt-4 text-sm text-center">Sudah punya akun? <a href="{{ route('login') }}" class="text-teal-700">Masuk</a></p>
        </div>
    </div>
</body>
</html>
