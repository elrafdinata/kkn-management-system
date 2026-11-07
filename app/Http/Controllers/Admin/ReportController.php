<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\ScheduleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const MAX_DAYS = 366;

    public function index(Request $request, ReportService $report): View|RedirectResponse
    {
        $f = $this->filters($request);

        if ($f instanceof RedirectResponse) {
            return $f;
        }

        $result = $report->build($f['from'], $f['to'], $f['category'], $f['name']);

        $today = CarbonImmutable::now(config('app.timezone'))->startOfDay();

        $presets = [
            'Hari ini' => [$today->toDateString(), $today->toDateString()],
            '7 hari terakhir' => [$today->subDays(6)->toDateString(), $today->toDateString()],
            'Bulan ini' => [$today->startOfMonth()->toDateString(), $today->toDateString()],
            'Bulan lalu' => [
                $today->subMonthNoOverflow()->startOfMonth()->toDateString(),
                $today->subMonthNoOverflow()->endOfMonth()->toDateString(),
            ],
        ];

        return view('admin.reports.index', [
            'from' => $f['from'],
            'to' => $f['to'],
            'category' => $f['category'],
            'name' => $f['name'],
            'categories' => ScheduleService::CATEGORIES,
            'rows' => $result['rows'],
            'totals' => $result['totals'],
            'presets' => $presets,
        ]);
    }

    public function pdf(Request $request, ReportService $report)
    {
        $f = $this->filters($request);

        if ($f instanceof RedirectResponse) {
            return $f;
        }

        $result = $report->build($f['from'], $f['to'], $f['category'], $f['name']);

        return Pdf::loadView('admin.reports.pdf', [
            'from' => $f['from'],
            'to' => $f['to'],
            'name' => $f['name'],
            'categoryLabel' => $f['category'] ? (ScheduleService::CATEGORIES[$f['category']] ?? '-') : 'Semua',
            'rows' => $result['rows'],
            'totals' => $result['totals'],
        ])->setPaper('a4', 'landscape')->download($this->fileName($f, 'pdf'));
    }

    public function excel(Request $request, ReportService $report): StreamedResponse|RedirectResponse
    {
        $f = $this->filters($request);

        if ($f instanceof RedirectResponse) {
            return $f;
        }

        $result = $report->build($f['from'], $f['to'], $f['category'], $f['name']);
        $percent = fn($p) => $p !== null ? $p . '%' : '-';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        $sheet->setCellValue('A1', 'Laporan Absensi KKN Kelompok 30 UNTARA');
        $sheet->setCellValue('A2', 'Periode ' . CarbonImmutable::parse($f['from'])->format('d/m/Y')
            . ' sampai ' . CarbonImmutable::parse($f['to'])->format('d/m/Y'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->fromArray(
            ['Nama', 'Kategori', 'Hari wajib', 'Hadir', 'Izin', 'Libur', 'Tidak Hadir', 'Menunggu', 'Kehadiran'],
            null,
            'A4'
        );
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);

        $line = 5;
        foreach ($result['rows'] as $r) {
            $sheet->fromArray([
                $r['name'],
                $r['category'],
                $r['required'],
                $r['hadir'],
                $r['izin'],
                $r['libur'],
                $r['tidak_hadir'],
                $r['menunggu'],
                $percent($r['percent']),
            ], null, 'A' . $line);
            $line++;
        }

        $t = $result['totals'];
        $sheet->fromArray([
            'Total',
            '',
            $t['required'],
            $t['hadir'],
            $t['izin'],
            $t['libur'],
            $t['tidak_hadir'],
            $t['menunggu'],
            $percent($t['percent']),
        ], null, 'A' . $line);
        $sheet->getStyle('A' . $line . ':I' . $line)->getFont()->setBold(true);

        $sheet->setCellValue('A' . ($line + 2), 'Kehadiran = Hadir / (Hadir + Tidak Hadir). Izin, Libur, dan Menunggu tidak masuk persentase. Hari Libur dikeluarkan dari hari wajib.');

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = $this->fileName($f, 'xlsx');

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @return array{from: string, to: string, category: ?string, name: string}|RedirectResponse
     */
    private function filters(Request $request): array|RedirectResponse
    {
        $today = CarbonImmutable::now(config('app.timezone'))->startOfDay();

        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'category' => ['nullable', Rule::in(array_keys(ScheduleService::CATEGORIES))],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $from = $filters['from'] ?? $today->startOfMonth()->toDateString();
        $to = $filters['to'] ?? $today->toDateString();

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        if (CarbonImmutable::parse($from)->diffInDays(CarbonImmutable::parse($to)) > self::MAX_DAYS) {
            return redirect()
                ->route('admin.reports.index')
                ->withErrors(['from' => 'Rentang laporan maksimal ' . self::MAX_DAYS . ' hari.']);
        }

        return [
            'from' => $from,
            'to' => $to,
            'category' => $filters['category'] ?? null,
            'name' => trim($filters['name'] ?? ''),
        ];
    }

    private function fileName(array $f, string $ext): string
    {
        return 'laporan-absensi-' . $f['from'] . '-sd-' . $f['to'] . '.' . $ext;
    }
}
