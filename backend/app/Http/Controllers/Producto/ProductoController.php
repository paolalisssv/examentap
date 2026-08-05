<?php

namespace App\Http\Controllers\Producto;

use App\DTOs\AuthenticatedUserDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Requests\Producto\StoreProductoRequest;
use App\Requests\Producto\UpdateProductoRequest;
use App\Resources\Producto\ProductoResource;
use App\Services\Export\ProductoExportService;
use App\Services\Producto\ProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoService $productoService,
        private readonly ProductoExportService $exportService,
    ) {
    }

    #[OA\Get(
        path: '/productos',
        operationId: 'productosIndex',
        summary: 'Lista productos con paginación, búsqueda y ordenamiento',
        description: 'Devuelve un listado paginado de productos. Permite buscar por código o nombre. Disponible para cualquier usuario autenticado.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', default: 10)),
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_field', in: 'query', schema: new OA\Schema(type: 'string', default: 'created_at', enum: ['id', 'name', 'precio', 'created_at'])),
            new OA\Parameter(name: 'sort_direction', in: 'query', schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Listado de productos', content: new OA\JsonContent(ref: '#/components/schemas/ProductoListResponse')),
            new OA\Response(response: 401, description: 'No autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $page = max($request->integer('page', 1), 1);
        $perPage = min(max($request->integer('per_page', 10), 1), 100);

        $result = $this->productoService->paginate(
            $request->string('search')->toString() ?: null,
            $request->string('sort_field')->toString() ?: 'created_at',
            $request->string('sort_direction')->toString() ?: 'desc',
            $page,
            $perPage,
        );

        return ApiResponse::success([
            'items' => ProductoResource::collection($result['items']),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ],
        ], 'Productos obtenidos correctamente.');
    }

    #[OA\Get(
        path: '/productos/{producto}',
        operationId: 'productosShow',
        summary: 'Obtiene el detalle de un producto',
        description: 'Devuelve la información completa de un producto. Disponible para cualquier usuario autenticado.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'producto', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle de producto', content: new OA\JsonContent(ref: '#/components/schemas/ProductoResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function show(string $producto): JsonResponse
    {
        return ApiResponse::success(new ProductoResource($this->productoService->find($producto)), 'Detalle de producto obtenido correctamente.');
    }

    #[OA\Post(
        path: '/productos',
        operationId: 'productosStore',
        summary: 'Crea un nuevo producto',
        description: 'Crea un producto y registra el alta en la bitácora de auditoría. Requiere que el usuario autenticado tenga el permiso de crear en la sección productos.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreProductoRequest')
        ),
        responses: [
            new OA\Response(response: 201, description: 'Producto creado', content: new OA\JsonContent(ref: '#/components/schemas/ProductoResponse')),
            new OA\Response(response: 403, description: 'Sin permisos para crear productos', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function store(StoreProductoRequest $request): JsonResponse
    {
        $producto = $this->productoService->create($request->validated(), $this->actor($request));

        return ApiResponse::success(new ProductoResource($producto), 'Producto creado correctamente.', 201);
    }

    #[OA\Put(
        path: '/productos/{producto}',
        operationId: 'productosUpdate',
        summary: 'Actualiza un producto existente',
        description: 'Actualiza un producto capturando la información anterior antes de aplicar los cambios y registra la edición en la bitácora de auditoría. Requiere que el usuario autenticado tenga el permiso de editar en la sección productos.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'producto', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateProductoRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Producto actualizado', content: new OA\JsonContent(ref: '#/components/schemas/ProductoResponse')),
            new OA\Response(response: 403, description: 'Sin permisos para editar productos', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 422, description: 'Error de validación', content: new OA\JsonContent(ref: '#/components/schemas/ApiValidationErrorResponse')),
        ]
    )]
    public function update(UpdateProductoRequest $request, string $producto): JsonResponse
    {
        $updated = $this->productoService->update($producto, $request->validated(), $this->actor($request));

        return ApiResponse::success(new ProductoResource($updated), 'Producto actualizado correctamente.');
    }

    #[OA\Delete(
        path: '/productos/{producto}',
        operationId: 'productosDestroy',
        summary: 'Elimina un producto',
        description: 'Elimina físicamente el producto de Firebase Firestore. Requiere que el usuario autenticado tenga el permiso de eliminar en la sección productos.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'producto', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Producto eliminado', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 403, description: 'Sin permisos para eliminar productos', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
            new OA\Response(response: 404, description: 'Producto no encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiErrorResponse')),
        ]
    )]
    public function destroy(string $producto): JsonResponse
    {
        $this->productoService->delete($producto);

        return ApiResponse::success(null, 'Producto eliminado correctamente.');
    }

    #[OA\Get(
        path: '/productos/export/pdf',
        operationId: 'productosExportPdf',
        summary: 'Exporta el listado de productos a PDF',
        description: 'Genera un PDF con título, fecha de generación y una tabla con Código, Nombre, Precio y Fecha de creación.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo PDF', content: new OA\MediaType(mediaType: 'application/pdf')),
        ]
    )]
    public function exportPdf(Request $request): Response
    {
        // Se reutiliza paginate() con PHP_INT_MAX como per_page para obtener el
        // listado completo (sin paginar) que requiere la exportación.
        $result = $this->productoService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toPdf($result['items']);
    }

    #[OA\Get(
        path: '/productos/export/excel',
        operationId: 'productosExportExcel',
        summary: 'Exporta el listado de productos a Excel',
        description: 'Genera un archivo XLSX con las columnas Código, Nombre, Precio y Fecha de creación.',
        security: [['bearerAuth' => []]],
        tags: ['Productos'],
        parameters: [
            new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Archivo Excel', content: new OA\MediaType(mediaType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')),
        ]
    )]
    public function exportExcel(Request $request): Response
    {
        $result = $this->productoService->paginate(
            $request->string('search')->toString() ?: null,
            'created_at',
            'desc',
            1,
            PHP_INT_MAX
        );

        return $this->exportService->toExcel($result['items']);
    }

    private function actor(Request $request): AuthenticatedUserDTO
    {
        return $request->attributes->get('auth_user');
    }
}
