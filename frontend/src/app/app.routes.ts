import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'admin',
    loadComponent: () =>
      import('./admin/admin').then((m) => m.AdminComponent)
  },
  {
    path: '',
    pathMatch: 'full',
    loadComponent: () =>
      import('./storefront').then((m) => m.StorefrontComponent)
  },
  {
    path: '**',
    redirectTo: ''
  }
];
