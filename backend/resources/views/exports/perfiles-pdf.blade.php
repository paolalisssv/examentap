<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Listado de Perfiles</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
    </style>
</head>
<body>
    <h1>Listado de Perfiles</h1>
    <p class="meta">Fecha de generación: {{ $generatedAt->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Fecha de creación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($perfiles as $perfil)
                <tr>
                    <td>{{ $perfil->id }}</td>
                    <td>{{ $perfil->name }}</td>
                    <td>{{ $perfil->createdAt->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
