export interface ApiResponse<T> {
  success: boolean;
  status: 'success' | 'error';
  message: string;
  data: T | null;
  errors: unknown;
}
