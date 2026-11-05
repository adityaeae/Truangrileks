$ErrorActionPreference = 'Stop'

$projectRoot = (Get-Location).Path
if (-not (Test-Path (Join-Path $projectRoot 'artisan'))) {
    Write-Host 'Jalankan script dari folder C:\xampp\htdocs\ruangrileks.' -ForegroundColor Red
    exit 1
}

$changes = @(
    [pscustomobject]@{
        Path = 'resources\views\admin\articles\index.blade.php'
        Old  = '{{ $article->updated_at->format(''d/m/Y'') }}'
        New  = '{{ $article->updated_at->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y'') }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\admin\users\index.blade.php'
        Old  = '{{ $managedUser->created_at?->format(''d/m/Y'') ?? ''-'' }}'
        New  = '{{ $managedUser->created_at?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y'') ?? ''-'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\bk\shares\index.blade.php'
        Old  = '{{ optional($share->screeningResult?->completed_at)->format(''d/m/Y H:i'') ?? ''Tidak tersedia'' }}'
        New  = '{{ $share->screeningResult?->completed_at?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') ?? ''Tidak tersedia'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\bk\shares\index.blade.php'
        Old  = '{{ optional($share->consented_at)->format(''d/m/Y H:i'') }}'
        New  = '{{ $share->consented_at?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') ?? ''-'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\bk\shares\show.blade.php'
        Old  = '{{ optional($share->screeningResult->completed_at ?? $share->screeningResult->created_at)->format(''d/m/Y H:i'') }}'
        New  = '{{ ($share->screeningResult->completed_at ?? $share->screeningResult->created_at)?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') ?? ''-'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\components\notification-bell.blade.php'
        Old  = '{{ $notification->created_at->format(''d/m/Y H:i'') }}'
        New  = '{{ $notification->created_at->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\screening\history\index.blade.php'
        Old  = '{{ optional($result->completed_at ?? $result->created_at)->format(''d/m/Y H:i'') }}'
        New  = '{{ ($result->completed_at ?? $result->created_at)?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') ?? ''-'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\screening\shares\create.blade.php'
        Old  = '{{ optional($result->completed_at ?? $result->created_at)->format(''d/m/Y H:i'') }}'
        New  = '{{ ($result->completed_at ?? $result->created_at)?->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') ?? ''-'' }}'
    },
    [pscustomobject]@{
        Path = 'resources\views\student\bk-appointments\index.blade.php'
        Old  = '{{ $appointment->requested_at->format(''d/m/Y H:i'') }}'
        New  = '{{ $appointment->requested_at->copy()->timezone(''Asia/Jakarta'')->format(''d/m/Y H:i'') }}'
    }
)

# Preflight: jangan ubah file apa pun jika ada pola yang tidak cocok.
$prepared = @()
$errors = @()
foreach ($change in $changes) {
    $fullPath = Join-Path $projectRoot $change.Path
    if (-not (Test-Path $fullPath)) {
        $errors += "File tidak ditemukan: $($change.Path)"
        continue
    }

    $content = [System.IO.File]::ReadAllText($fullPath)
    $first = $content.IndexOf($change.Old, [System.StringComparison]::Ordinal)
    if ($first -lt 0) {
        $errors += "Pola lama tidak ditemukan pada: $($change.Path)"
        continue
    }

    $second = $content.IndexOf($change.Old, $first + $change.Old.Length, [System.StringComparison]::Ordinal)
    if ($second -ge 0) {
        $errors += "Pola ditemukan lebih dari sekali pada: $($change.Path). Perlu pemeriksaan manual."
        continue
    }

    $prepared += [pscustomobject]@{
        FullPath = $fullPath
        RelativePath = $change.Path
        Original = $content
        Updated = $content.Replace($change.Old, $change.New)
    }
}

if ($errors.Count -gt 0) {
    Write-Host 'Patch dihentikan. Tidak ada file yang diubah.' -ForegroundColor Red
    $errors | ForEach-Object { Write-Host "- $_" -ForegroundColor Yellow }
    Write-Host 'Kirimkan pesan ini agar pola kode disesuaikan dengan versi proyekmu.'
    exit 1
}

$stamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backupRoot = Join-Path $projectRoot "storage\app\patch-backups\timezone-wib_$stamp"
New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null

$utf8NoBom = [System.Text.UTF8Encoding]::new($false)
foreach ($item in $prepared) {
    $backupPath = Join-Path $backupRoot $item.RelativePath
    $backupDir = Split-Path $backupPath -Parent
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
    [System.IO.File]::Copy($item.FullPath, $backupPath, $true)
    [System.IO.File]::WriteAllText($item.FullPath, $item.Updated, $utf8NoBom)
    Write-Host "Diperbarui: $($item.RelativePath)" -ForegroundColor Green
}

Write-Host ''
Write-Host 'Patch zona waktu WIB berhasil diterapkan.' -ForegroundColor Green
Write-Host "Backup file asli: $backupRoot" -ForegroundColor Cyan
Write-Host 'Zona waktu database tetap UTC; hanya tampilan timestamp yang dikonversi ke Asia/Jakarta.'
Write-Host 'Jadwal mulai/selesai BK tidak diubah karena nilai datetime-local disimpan sebagai waktu lokal yang ditampilkan dengan label WIB.'
