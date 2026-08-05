<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerfilExportService
{
    public function toPdf(array $perfiles): Response
    {
        return Pdf::loadView('exports.perfiles-pdf', [
            'perfiles' => $perfiles,
            'generatedAt' => Carbon::now(),
        ])->download('perfiles.pdf');
    }

    public function toExcel(array $perfiles): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Código', 'Nombre', 'Fecha de creación'], null, 'A1');

        $row = 2;

        foreach ($perfiles as $perfil) {
            $sheet->fromArray([
                $perfil->id,
                $perfil->name,
                $perfil->createdAt->format('d/m/Y H:i'),
            ], null, "A{$row}");

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="perfiles.xlsx"',
        ]);
    }
}
