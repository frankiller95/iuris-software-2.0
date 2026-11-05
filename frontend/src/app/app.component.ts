import { Component } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { ToastModule } from 'primeng/toast';
import { ConfirmDialogModule } from 'primeng/confirmdialog';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, ToastModule, ConfirmDialogModule],
  template: `
    <p-toast position="top-right" />
    <p-confirmdialog [style]="{ width: '28rem' }" [breakpoints]="{ '640px': '95vw' }" />
    <router-outlet />
  `,
})
export class AppComponent {}
