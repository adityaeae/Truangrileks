@extends('layouts.app')

@section('content')
<div class="bg-teal-700 text-white py-12 sm:py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-teal-100 text-sm font-semibold mb-2">Dashboard Siswa</p>
        <h1 class="text-3xl sm:text-4xl font-bold mb-3">Berbagi Hasil kepada Guru BK</h1>
        <p class="text-teal-100 max-w-3xl">Pilih guru BK yang boleh melihat satu hasil skrining ini. Persetujuan berbagi terpisah dari persetujuan penyimpanan.</p>
    </div>
</div>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    @if(session('status'))
        <div class="rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-800" role="status">{{ session('status') }}</div>
    @endif

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-2">
        <div class="flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">Hasil skrining</span>
            <span class="text-sm text-gray-500">Hasil tersimpan</span>
        </div>
        <h2 class="text-xl font-bold text-teal-900">Skrining tekanan akademik</h2>
        <p class="text-sm text-gray-600">Tanggal: {{ ($result->completed_at ?? $result->created_at)?->copy()->timezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}</p>
        <p class="text-sm text-gray-700">Guru BK yang dipilih hanya akan dapat melihat ringkasan dan 16 jawaban dari hasil ini, bukan seluruh riwayat skrining Anda.</p>
    </section>

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <h2 class="text-xl font-bold text-teal-900 mb-2">Bagikan kepada guru BK</h2>
        <p class="text-sm text-gray-600 mb-5">Pilih akun guru BK yang tersedia. Baca pernyataan persetujuan sebelum mengirim.</p>

        @if($teachers->isEmpty())
            <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                Belum ada akun guru BK yang tersedia. Hasil ini belum dibagikan kepada siapa pun.
            </div>
        @else
            @php
                $activeTeacherIds = $shares->whereNull('revoked_at')->pluck('bk_teacher_id')->all();
            @endphp
            <form method="POST" action="{{ route('student.screening-shares.store', $result->id) }}" class="space-y-5">
                @csrf
                <div>
                    <label for="teacher_id" class="block text-sm font-semibold text-gray-700 mb-2">Guru BK tujuan</label>
                    <select id="teacher_id" name="teacher_id" required class="w-full rounded-lg border-gray-300 focus:border-teal-600 focus:ring-teal-600">
                        <option value="">Pilih guru BK</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected((string) old('teacher_id') === (string) $teacher->id) @disabled(in_array($teacher->id, $activeTeacherIds, true))>
                                {{ $teacher->name }} — {{ $teacher->email }}{{ in_array($teacher->id, $activeTeacherIds, true) ? ' (sudah memiliki akses)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')<p class="text-sm text-red-600 mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                    <label class="flex items-start gap-3 text-sm text-teal-950">
                        <input type="checkbox" name="consent_share" value="1" required class="mt-1 rounded border-teal-400 text-teal-700 focus:ring-teal-600" @checked(old('consent_share'))>
                        <span>Saya menyetujui pembagian hasil skrining yang saya pilih ini kepada guru BK yang tercantum. Saya memahami guru BK tersebut dapat melihat identitas dasar, ringkasan, dan jawaban pada hasil ini. Saya dapat mencabut akses kapan saja melalui halaman ini.</span>
                    </label>
                    @error('consent_share')<p class="text-sm text-red-700 mt-2">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="inline-flex justify-center rounded-lg bg-teal-700 hover:bg-teal-600 text-white px-5 py-3 font-semibold transition">Setujui dan Bagikan</button>
                    <a href="{{ route('screening-history.index') }}" class="inline-flex justify-center rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-3 font-semibold transition">Kembali ke Riwayat</a>
                </div>
            </form>
        @endif
    </section>

    <section class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sm:p-6">
        <h2 class="text-xl font-bold text-teal-900 mb-4">Status akses guru BK</h2>
        @if($shares->isEmpty())
            <p class="text-sm text-gray-600">Belum ada guru BK yang diberikan akses ke hasil ini.</p>
        @else
            <div class="space-y-3">
                @foreach($shares as $share)
                    <article class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border border-gray-100 rounded-lg p-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $share->teacher?->name ?? 'Akun guru BK tidak tersedia' }}</p>
                            @if($share->teacher?->email)<p class="text-sm text-gray-500">{{ $share->teacher->email }}</p>@endif
                            <p class="text-xs text-gray-500 mt-1">Disetujui: {{ optional($share->consented_at)->format('d/m/Y H:i') }}</p>
                            @if($share->revoked_at)
                                <p class="text-xs text-gray-500">Akses dicabut: {{ $share->revoked_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            @if($share->revoked_at)
                                <span class="rounded-full bg-gray-100 text-gray-700 px-3 py-1 text-xs font-semibold">Akses dicabut</span>
                            @else
                                <span class="rounded-full bg-teal-50 text-teal-800 px-3 py-1 text-xs font-semibold">Aktif</span>
                                <form method="POST" action="{{ route('student.screening-shares.revoke', [$result->id, $share->id]) }}" onsubmit="return confirm('Cabut akses guru BK ini terhadap hasil skrining yang dipilih?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-800">Cabut akses</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <p class="text-xs text-gray-500 text-center max-w-3xl mx-auto">Hasil yang dibagikan adalah ringkasan respons deskriptif, bukan diagnosis atau penetapan tingkat tekanan akademik. Persetujuan berbagi hanya berlaku untuk hasil yang tercantum pada halaman ini.</p>
</div>
@endsection
