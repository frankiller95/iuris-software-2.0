import { ApplicationConfig, provideZoneChangeDetection } from '@angular/core';
import { provideRouter, withComponentInputBinding } from '@angular/router';
import { provideHttpClient, withInterceptors } from '@angular/common/http';
import { provideAnimationsAsync } from '@angular/platform-browser/animations/async';
import { providePrimeNG } from 'primeng/config';
import { ConfirmationService, MessageService } from 'primeng/api';
import { routes } from './app.routes';
import { authInterceptor } from './core/interceptors/auth.interceptor';
import { IurisPreset } from './theme';

export const appConfig: ApplicationConfig = {
  providers: [
    provideZoneChangeDetection({ eventCoalescing: true }),
    provideRouter(routes, withComponentInputBinding()),
    provideHttpClient(withInterceptors([authInterceptor])),
    provideAnimationsAsync(),
    providePrimeNG({
      theme: { preset: IurisPreset, options: { darkModeSelector: '.app-dark' } },
      translation: {
        accept: 'Aceptar',
        reject: 'Cancelar',
        emptyMessage: 'Sin resultados',
        emptyFilterMessage: 'Sin resultados',
      },
    }),
    MessageService,
    ConfirmationService,
  ],
};
