<?php

namespace App\Enums;

enum SeccionSistema: string
{
    case Usuarios = 'usuarios';
    case Perfiles = 'perfiles';
    case Productos = 'productos';

    public static function values(): array
    {
        return array_map(fn (self $seccion) => $seccion->value, self::cases());
    }
}
