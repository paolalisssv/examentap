<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'ExamenTAP API',
    description: 'Documentación de la API REST del backend ExamenTAP.'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'API server'
)]
class OpenApiInfo
{
}
