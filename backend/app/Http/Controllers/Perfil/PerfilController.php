<?php

namespace App\Http\Controllers\Perfil;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Requests\Perfil\StorePerfilRequest;
use App\Requests\Perfil\UpdatePerfilRequest;
use App\Resources\Perfil\PerfilResource;
use App\Services\Export\PerfilExportService;
use App\Services\Perfil\PerfilService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class PerfilController extends Controller
{
    public function __construct(
        private readonly PerfilService $perfilService,
        private readonly PerfilExportService $exportService,
    ) {
    }

    #[OA\Get(
        path: '/perfiles',
        operationId: 'perfilesIndex',
        summary: 'Lista perfiles con paginación, búsqueda y ordenamiento',
        description: 'Devuelve un listado paginado de perfiles. Permite buscar por código o nombre. Requiere el permiso "consultar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_field', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ['id', 'name', 'created_at'])),
            new OA\Parameter(name: 'sort_direction', in: 'query', schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado de perfiles', content: new OA\JsonContent(ref: '#/components/schemas/PerfilListResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = max($request->integer('page', 1), 1);
        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        $result = $this->perfilService->paginate(
            $request->string('search')->toString() ?: null,
            $request->string('sort_field')->toString() ?: 'created_at',
            $request->string('sort_direction')->toString() ?: 'desc',
            $page,
            $perPage,
        );

        return ApiResponse::success([
            'items' => PerfilResource::collection($result['items']),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ],
        ], 'Perfiles obtenidos correctamente.');
    }

    #[OA\Get(
        path: '/perfiles/{perfil}',
        operationId: 'perfilesShow',
        summary: 'Obtiene el detalle de un perfil',
        description: 'Devuelve la información completa de un perfil, incluyendo la matriz de secciones y permisos asociada. Requiere el permiso "consultar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'perfil', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de perfil', content: new OA\JsonContent(ref: '#/components/schemas/PerfilResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Perfil no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $perfil): JsonResponse
    {
        return ApiResponse::success(
            new PerfilResource($this->perfilService->find($perfil)),
            'Detalle de perfil obtenido correctamente.'
        );
    }

    #[OA\Post(
        path: '/perfiles',
        operationId: 'perfilesStore',
        summary: 'Crea un nuevo perfil',
        description: 'Crea un perfil validando que el nombre sea único y define la matriz de permisos por sección. Requiere el permiso "crear" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StorePerfilRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Perfil creado', content: new OA\JsonContent(ref: '#/components/schemas/PerfilResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para crear perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function store(StorePerfilRequest $request): JsonResponse
    {
        $perfil = $this->perfilService->create($request->validated());

        return ApiResponse::success(new PerfilResource($perfil), 'Perfil creado correctamente.', 201);
    }

    #[OA\Put(
        path: '/perfiles/{perfil}',
        operationId: 'perfilesUpdate',
        summary: 'Actualiza un perfil existente',
        description: 'Actualiza el nombre y la matriz de permisos por sección de un perfil. Requiere el permiso "editar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'perfil', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdatePerfilRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Perfil actualizado', content: new OA\JsonContent(ref: '#/components/schemas/PerfilResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para editar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Perfil no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function update(UpdatePerfilRequest $request, string $perfil): JsonResponse
    {
        $updated = $this->perfilService->update($perfil, $request->validated());

        return ApiResponse::success(new PerfilResource($updated), 'Perfil actualizado correctamente.');
    }

    #[OA\Delete(
        path: '/perfiles/{perfil}',
        operationId: 'perfilesDestroy',
        summary: 'Elimina un perfil',
        description: 'Elimina físicamente el perfil de Firebase Firestore. Requiere el permiso "eliminar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'perfil', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Perfil eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para eliminar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Perfil no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $perfil): JsonResponse
    {
        $this->perfilService->delete($perfil);

        return ApiResponse::success(null, 'Perfil eliminado correctamente.');
    }

    #[OA\Get(
        path: '/perfiles/export/pdf',
        operationId: 'perfilesExportPdf',
        summary: 'Exporta el listado de perfiles a PDF',
        description: 'Genera un PDF con Código, Nombre y Fecha de creación. Requiere el permiso "consultar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo PDF', content: new OA\MediaType(mediaType: 'application/pdf')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function exportPdf(Request $request): Response
    {
        // Se reutiliza paginate() con PHP_INT_MAX como per_page para obtener el
        // listado completo (sin paginar) que requiere la exportación.
        $result = $this->perfilService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toPdf($result['items']);
    }

    #[OA\Get(
        path: '/perfiles/export/excel',
        operationId: 'perfilesExportExcel',
        summary: 'Exporta el listado de perfiles a Excel',
        description: 'Genera un archivo XLSX con Código, Nombre y Fecha de creación. Requiere el permiso "consultar" en la sección "perfiles".',
        security: [['bearerAuth' => []]],
        tags: ['Perfiles'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo Excel', content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar perfiles', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function exportExcel(Request $request): Response
    {
        $result = $this->perfilService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toExcel($result['items']);
    }
}
