import { Component, computed, inject, signal } from '@angular/core';
import { Router, RouterLink, RouterLinkActive, RouterOutlet, NavigationEnd } from '@angular/router';
import { toSignal } from '@angular/core/rxjs-interop';
import { filter, map } from 'rxjs';
import { ButtonModule } from 'primeng/button';
import { AuthService } from '../core/services/auth.service';

interface MenuItem {
  label: string;
  icon: string;
  route?: string;
  adminOnly?: boolean;
  soon?: boolean;
}

@Component({
  selector: 'app-main-layout',
  imports: [RouterOutlet, RouterLink, RouterLinkActive, ButtonModule],
  templateUrl: './main-layout.component.html',
  styleUrl: './main-layout.component.scss',
})
export class MainLayoutComponent {
  auth = inject(AuthService);
  private router = inject(Router);

  sidebarOpen = signal(false);

  private items: MenuItem[] = [
    { label: 'Inicio', icon: 'pi pi-home', route: '/dashboard' },
    { label: 'Clientes', icon: 'pi pi-id-card', route: '/clientes' },
    { label: 'Procesos', icon: 'pi pi-briefcase', soon: true },
    { label: 'Usuarios', icon: 'pi pi-users', route: '/usuarios', adminOnly: true },
  ];

  menu = computed(() => this.items.filter((i) => !i.adminOnly || this.auth.isAdmin()));

  pageTitle = toSignal(
    this.router.events.pipe(
      filter((e) => e instanceof NavigationEnd),
      map(() => this.currentTitle()),
    ),
    { initialValue: this.currentTitle() },
  );

  private currentTitle(): string {
    let route = this.router.routerState.snapshot.root;
    while (route.firstChild) route = route.firstChild;
    return route.title ?? '';
  }

  closeSidebar(): void {
    this.sidebarOpen.set(false);
  }
}
