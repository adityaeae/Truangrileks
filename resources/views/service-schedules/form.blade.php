<form method="POST" action="{{ $formAction }}" class="mt-6 space-y-5">
    @csrf
    @if($formMethod !== 'POST') @method($formMethod) @endif

    @if($isAdmin)
        <div>
            <label for="bk_teacher_id" class="block text-sm font-semibold text-gray-700">Guru BK</label>
            <select id="bk_teacher_id" name="bk_teacher_id" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                <option value="">Pilih Guru BK</option>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" @selected((string)old('bk_teacher_id', $schedule?->bk_teacher_id) === (string)$teacher->id)>{{ $teacher->name }} — {{ $teacher->email }}</option>
                @endforeach
            </select>
            @error('bk_teacher_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            @if($teachers->isEmpty())<p class="text-sm text-amber-700 mt-1">Belum ada akun Guru BK aktif. Aktifkan atau buat akun Guru BK terlebih dahulu.</p>@endif
        </div>
    @endif

    <div>
        <label for="title" class="block text-sm font-semibold text-gray-700">Nama layanan</label>
        <input id="title" name="title" required maxlength="120" value="{{ old('title', $schedule?->title ?? 'Konsultasi Bimbingan dan Konseling') }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
        @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700">Deskripsi layanan (opsional)</label>
        <textarea id="description" name="description" rows="3" maxlength="1000" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600" placeholder="Contoh: Konsultasi belajar dan pengelolaan tekanan akademik.">{{ old('description', $schedule?->description) }}</textarea>
        @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="service_mode" class="block text-sm font-semibold text-gray-700">Moda layanan</label>
            <select id="service_mode" name="service_mode" required class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                <option value="tatap_muka" @selected(old('service_mode', $schedule?->service_mode ?? 'tatap_muka') === 'tatap_muka')>Tatap muka</option>
                <option value="daring" @selected(old('service_mode', $schedule?->service_mode) === 'daring')>Daring</option>
            </select>
            @error('service_mode')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="location" class="block text-sm font-semibold text-gray-700">Lokasi / media pertemuan</label>
            <input id="location" name="location" required maxlength="255" value="{{ old('location', $schedule?->location ?? 'Ruang BK') }}" placeholder="Ruang BK atau platform pertemuan" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
            @error('location')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="starts_at" class="block text-sm font-semibold text-gray-700">Mulai</label>
            <input id="starts_at" type="datetime-local" name="starts_at" required value="{{ old('starts_at', $schedule?->starts_at?->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
            @error('starts_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="ends_at" class="block text-sm font-semibold text-gray-700">Selesai</label>
            <input id="ends_at" type="datetime-local" name="ends_at" required value="{{ old('ends_at', $schedule?->ends_at?->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
            @error('ends_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="capacity" class="block text-sm font-semibold text-gray-700">Kapasitas siswa</label>
            <input id="capacity" type="number" min="1" max="20" name="capacity" required value="{{ old('capacity', $schedule?->capacity ?? 1) }}" class="mt-1 w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
            @error('capacity')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="rounded-lg bg-gray-50 p-4">
        <input type="hidden" name="is_published" value="0">
        <label class="flex items-start gap-3">
            <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $schedule?->is_published ?? true)) class="mt-1 rounded border-gray-300 text-teal-700 focus:ring-teal-600">
            <span><strong class="block text-sm text-gray-800">Publikasikan jadwal</strong><span class="block text-sm text-gray-600 mt-1">Jika aktif, siswa dapat melihat jadwal ini dan mengajukan janji temu.</span></span>
        </label>
        @error('is_published')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
        Jangan masukkan jawaban skrining atau informasi pribadi yang sensitif ke dalam deskripsi jadwal. Catatan janji temu siswa hanya akan terlihat oleh Guru BK yang dituju dan siswa pemiliknya.
    </div>

    <div class="flex flex-wrap gap-3 pt-2">
        <button type="submit" class="rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-3 text-sm font-semibold">Simpan Jadwal</button>
        <a href="{{ route($routePrefix.'.service-schedules.index') }}" class="rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-3 text-sm font-semibold">Batal</a>
    </div>
</form>
