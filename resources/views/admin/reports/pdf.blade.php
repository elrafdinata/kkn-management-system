<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #212529;
        }

        h1 {
            font-size: 14px;
            margin: 0 0 4px 0;
        }

        p {
            margin: 0 0 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #adb5bd;
            padding: 4px 6px;
        }

        th {
            background: #e9ecef;
            text-align: left;
        }

        .r {
            text-align: right;
        }

        tfoot td {
            font-weight: bold;
            background: #f8f9fa;
        }

        .note {
            margin-top: 8px;
            font-size: 9px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <h1>Laporan Absensi KKN Kelompok 30 UNTARA</h1>
    <p>
        Periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}
        &middot; Kategori: {{ $categoryLabel }}{{ $name !== '' ? ' · Nama: ' . $name : '' }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Kategori</th>
                <th class="r">Hari wajib</th>
                <th class="r">Hadir</th>
                <th class="r">Izin</th>
                <th class="r">Libur</th>
                <th class="r">Tidak Hadir</th>
                <th class="r">Menunggu</th>
                <th class="r">Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
            <tr>
                <td>{{ $r['name'] }}</td>
                <td>{{ $r['category'] }}</td>
                <td class="r">{{ $r['required'] }}</td>
                <td class="r">{{ $r['hadir'] }}</td>
                <td class="r">{{ $r['izin'] }}</td>
                <td class="r">{{ $r['libur'] }}</td>
                <td class="r">{{ $r['tidak_hadir'] }}</td>
                <td class="r">{{ $r['menunggu'] }}</td>
                <td class="r">{{ $r['percent'] !== null ? $r['percent'] . '%' : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="r">{{ $totals['required'] }}</td>
                <td class="r">{{ $totals['hadir'] }}</td>
                <td class="r">{{ $totals['izin'] }}</td>
                <td class="r">{{ $totals['libur'] }}</td>
                <td class="r">{{ $totals['tidak_hadir'] }}</td>
                <td class="r">{{ $totals['menunggu'] }}</td>
                <td class="r">{{ $totals['percent'] !== null ? $totals['percent'] . '%' : '-' }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="note">
        Kehadiran = Hadir / (Hadir + Tidak Hadir). Izin, Libur, dan Menunggu tidak masuk persentase.
        Hari Libur dikeluarkan dari hari wajib.
        Hari wajib yang sudah lewat tanpa absensi dihitung Tidak Hadir.
        Dicetak {{ now(config('app.timezone'))->format('d/m/Y H:i') }} WIB.
    </p>
</body>

</html>