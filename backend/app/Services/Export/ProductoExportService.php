<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductoExportService
{
    public function toPdf(array $productos): Response
    {
        return Pdf::loadView('exports.productos-pdf', [
            'productos' => $productos,
            'generatedAt' => Carbon::now(),
        ])->download('productos.pdf');
    }

    public function toExcel(array $productos): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Código', 'Nombre', 'Precio', 'Fecha de creación'], null, 'A1');

        $row = 2;

        foreach ($productos as $producto) {
            $sheet->fromArray([
                $producto->id,
                $producto->name,
                $producto->precio,
                $producto->createdAt->format('d/m/Y H:i'),
            ], null, "A{$row}");

            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="productos.xlsx"',
        ]);
    }
}
