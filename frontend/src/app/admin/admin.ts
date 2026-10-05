
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { AdminService } from '../services/admin.service';

@Component({
  selector: 'app-admin',
  standalone: true,
  imports: [CommonModule, FormsModule],
  templateUrl: './admin.html',
  styleUrl: './admin.css'
})
export class AdminComponent implements OnInit {
  email = '';
  password = '';

  loggedIn = false;
  loading = false;
  message = '';

  adminName = '';
  products: any[] = [];

  productForm = {
    id: 0,
    name: '',
    description: '',
    price: 0,
    category: '',
    image: '',
    stock: 0
  };

  editing = false;

  constructor(private adminService: AdminService) {}

  ngOnInit(): void {
    this.checkAdmin();
  }

  checkAdmin(): void {
    this.adminService.me().subscribe({
      next: (res) => {
        if (res.success) {
          this.loggedIn = true;
          this.adminName = res.data.name;
          this.loadProducts();
        }
      },
      error: () => {
        this.loggedIn = false;
      }
    });
  }

  login(): void {
    this.message = '';

    if (!this.email || !this.password) {
      this.message = 'Email and password required.';
      return;
    }

    this.loading = true;

    this.adminService.login(this.email, this.password).subscribe({
      next: (res) => {
        this.loading = false;

        if (res.success) {
          this.loggedIn = true;
          this.adminName = res.data.name;
          this.password = '';
          this.loadProducts();
        } else {
          this.message = res.message || 'Login failed.';
        }
      },
      error: (err) => {
        this.loading = false;
        this.message = err.error?.message || 'Login failed.';
      }
    });
  }

  loadProducts(): void {
    this.adminService.getProducts().subscribe({
      next: (res) => {
        if (res.success) {
          this.products = res.data || [];
        }
      },
      error: () => {
        this.message = 'Products could not be loaded.';
      }
    });
  }

  saveProduct(): void {
    this.message = '';

    if (!this.productForm.name.trim() || this.productForm.price < 0) {
      this.message = 'Enter a product name and valid price.';
      return;
    }

    const request = this.editing
      ? this.adminService.updateProduct(this.productForm)
      : this.adminService.addProduct(this.productForm);

    request.subscribe({
      next: (res) => {
        if (res.success) {
          this.message = res.message;
          this.resetForm();
          this.loadProducts();
        } else {
          this.message = res.message || 'Could not save product.';
        }
      },
      error: (err) => {
        this.message = err.error?.message || 'Could not save product.';
      }
    });
  }

  editProduct(product: any): void {
    this.productForm = {
      id: Number(product.id),
      name: product.name || '',
      description: product.description || '',
      price: Number(product.price),
      category: product.category || '',
      image: product.image || '',
      stock: Number(product.stock)
    };

    this.editing = true;
  }

  deleteProduct(id: number): void {
    if (!confirm('Are you sure you want to delete this product?')) {
      return;
    }

    this.adminService.deleteProduct(id).subscribe({
      next: (res) => {
        this.message = res.message;
        this.loadProducts();
      },
      error: (err) => {
        this.message = err.error?.message || 'Delete failed.';
      }
    });
  }

  resetForm(): void {
    this.editing = false;

    this.productForm = {
      id: 0,
      name: '',
      description: '',
      price: 0,
      category: '',
      image: '',
      stock: 0
    };
  }

  logout(): void {
    this.adminService.logout().subscribe({
      next: () => {
        this.loggedIn = false;
        this.products = [];
        this.resetForm();
      },
      error: () => {
        this.message = 'Logout failed.';
      }
    });
  }
}