export interface Producto {
  id: string;
  name: string;
  precio: number;
  createdAt: string;
}

export interface ProductoDto {
  id: string;
  name: string;
  precio: number;
  created_at: string;
}

export function mapProducto(dto: ProductoDto): Producto {
  return {
    id: dto.id,
    name: dto.name,
    precio: dto.precio,
    createdAt: dto.created_at
  };
}
