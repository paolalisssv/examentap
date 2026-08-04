import { environment } from '../../../environments/environment';

export function isProductionEnvironment(): boolean {
  return environment.production;
}
