import { HttpInterceptorFn } from '@angular/common/http';

import { JSON_CONTENT_TYPE } from '../constants/api.constants';

export const apiInterceptor: HttpInterceptorFn = (req, next) => {
  const cloned = req.clone({
    setHeaders: {
      Accept: JSON_CONTENT_TYPE
    }
  });

  return next(cloned);
};
