<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UsuarioExportService
{
    public function toPdf(array $usuarios): Response
    {
        return Pdf::loadView('exports.usuarios-pdf', [
            'usuarios' => $usuarios,
            'generatedAt' => Carbon::now(),
        ])->download('usuarios.pdf');
    }

    public function toExcel(array $usuarios): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Código', 'Usuario', 'Nombre', 'Fecha de creación'], null, 'A1');

        $row = 2;

        foreach ($usuarios as $usuario) {
            $sheet->fromArray([
                $usuario->id,
                $usuario->email,
                $usuario->name,
                $usuario->createdAt->format('d/m/Y H:i'),
            ], null, "A{$row}");

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        // Se escribe directo al stream de la respuesta en lugar de generar el archivo
        // completo en memoria antes de enviarlo.
        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="usuarios.xlsx"',
        ]);
    }
}
