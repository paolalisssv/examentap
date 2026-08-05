<?php

namespace App\Http\Controllers\Usuario;

use App\DTOs\AuthenticatedUserDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Requests\Usuario\StoreUsuarioRequest;
use App\Requests\Usuario\UpdateUsuarioRequest;
use App\Resources\Usuario\UsuarioDetailResource;
use App\Resources\Usuario\UsuarioResource;
use App\Services\Export\UsuarioExportService;
use App\Services\Usuario\UsuarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UsuarioService $usuarioService,
        private readonly UsuarioExportService $exportService,
    ) {
    }

    #[OA\Get(
        path: '/usuarios',
        operationId: 'usuariosIndex',
        summary: 'Lista usuarios con paginación, búsqueda y ordenamiento',
        description: 'Devuelve un listado paginado de usuarios. Permite buscar por código, nombre o correo electrónico y ordenar por cualquiera de las columnas visibles. Requiere el permiso "consultar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_field', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ['id', 'name', 'email', 'created_at'])),
            new OA\Parameter(name: 'sort_direction', in: 'query', schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado de usuarios', content: new OA\JsonContent(ref: '#/components/schemas/UsuarioListResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = max($request->integer('page', 1), 1);
        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        $result = $this->usuarioService->paginate(
            $request->string('search')->toString() ?: null,
            $request->string('sort_field')->toString() ?: 'created_at',
            $request->string('sort_direction')->toString() ?: 'desc',
            $page,
            $perPage,
        );

        return ApiResponse::success([
            'items' => UsuarioResource::collection($result['items']),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ],
        ], 'Usuarios obtenidos correctamente.');
    }

    #[OA\Get(
        path: '/usuarios/{usuario}',
        operationId: 'usuariosShow',
        summary: 'Obtiene el detalle de un usuario',
        description: 'Devuelve la información completa de un usuario, incluyendo los perfiles asignados y las secciones a las que tiene acceso (agregadas de todos sus perfiles). Requiere el permiso "consultar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de usuario', content: new OA\JsonContent(ref: '#/components/schemas/UsuarioDetailResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $usuario): JsonResponse
    {
        return ApiResponse::success(
            new UsuarioDetailResource($this->usuarioService->detail($usuario)),
            'Detalle de usuario obtenido correctamente.'
        );
    }

    #[OA\Post(
        path: '/usuarios',
        operationId: 'usuariosStore',
        summary: 'Crea un nuevo usuario',
        description: 'Crea un usuario con su fotografía de perfil (almacenada en Firebase Storage), valida que el correo sea único y registra el alta en la bitácora de auditoría. Si el sistema no tiene ningún usuario registrado, este endpoint es público y el usuario creado se registra automáticamente con el perfil Administrador (modo de inicialización del sistema). Una vez que existe al menos un usuario, requiere autenticación y el permiso "crear" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/StoreUsuarioRequest'))
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado', content: new OA\JsonContent(ref: '#/components/schemas/UsuarioResponse')),
            new OA\Response(response: 401, description: 'No autenticado (el sistema ya tiene usuarios registrados)', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 403, description: 'Sin permisos para crear usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function store(StoreUsuarioRequest $request): JsonResponse
    {
        $isBootstrap = (bool) $request->attributes->get('bootstrap', false);

        $usuario = $this->usuarioService->create(
            $request->validated(),
            $request->file('foto'),
            $this->actor($request),
            $isBootstrap
        );

        return ApiResponse::success(new UsuarioResource($usuario), 'Usuario creado correctamente.', 201);
    }

    #[OA\Put(
        path: '/usuarios/{usuario}',
        operationId: 'usuariosUpdate',
        summary: 'Actualiza un usuario existente',
        description: 'Actualiza los datos de un usuario. Captura la información anterior antes de aplicar los cambios y registra la edición en la bitácora de auditoría con el detalle antes/después. Al enviarse como multipart/form-data, debe incluir el campo _method=PUT. Requiere el permiso "editar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(ref: '#/components/schemas/UpdateUsuarioRequest'))
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado', content: new OA\JsonContent(ref: '#/components/schemas/UsuarioResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para editar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function update(UpdateUsuarioRequest $request, string $usuario): JsonResponse
    {
        $updated = $this->usuarioService->update(
            $usuario,
            $request->validated(),
            $request->file('foto'),
            $this->actor($request)
        );

        return ApiResponse::success(new UsuarioResource($updated), 'Usuario actualizado correctamente.');
    }

    #[OA\Delete(
        path: '/usuarios/{usuario}',
        operationId: 'usuariosDestroy',
        summary: 'Elimina un usuario',
        description: 'Elimina físicamente el usuario de Firebase Firestore. Requiere el permiso "eliminar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'usuario', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 403, description: 'Sin permiso para eliminar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Usuario no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $usuario): JsonResponse
    {
        $this->usuarioService->delete($usuario);

        return ApiResponse::success(null, 'Usuario eliminado correctamente.');
    }

    #[OA\Get(
        path: '/usuarios/export/pdf',
        operationId: 'usuariosExportPdf',
        summary: 'Exporta el listado de usuarios a PDF',
        description: 'Genera un PDF con título, fecha de generación y una tabla con Código, Usuario, Nombre y Fecha de creación. Requiere el permiso "consultar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo PDF', content: new OA\MediaType(mediaType: 'application/pdf')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function exportPdf(Request $request): Response
    {
        // Se reutiliza paginate() con PHP_INT_MAX como per_page para obtener el
        // listado completo (sin paginar) que requiere la exportación.
        $result = $this->usuarioService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toPdf($result['items']);
    }

    #[OA\Get(
        path: '/usuarios/export/excel',
        operationId: 'usuariosExportExcel',
        summary: 'Exporta el listado de usuarios a Excel',
        description: 'Genera un archivo XLSX con las columnas Código, Usuario, Nombre y Fecha de creación. Requiere el permiso "consultar" en la sección "usuarios".',
        security: [['bearerAuth' => []]],
        tags: ['Usuarios'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo Excel', content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')),
            new OA\Response(response: 403, description: 'Sin permiso para consultar usuarios', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function exportExcel(Request $request): Response
    {
        $result = $this->usuarioService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toExcel($result['items']);
    }

    private function actor(Request $request): ?AuthenticatedUserDTO
    {
        return $request->attributes->get('auth_user');
    }
}
