
import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import {
  HttpClient,
  HttpErrorResponse
} from '@angular/common/http';
import { CartService } from './services/cart.service';

interface Product {
  id: number;
  name: string;
  category: string;
  price: number;
  oldPrice: number;
  rating: number;
  image: string;
  badge?: string;
}

interface ShopUser {
  id: number;
  name: string;
  email: string;
  created_at?: string;
}

interface AuthResponse {
  success: boolean;
  message: string;
  data: ShopUser | null;
}

@Component({
  selector: 'app-storefront',
  standalone: true,
  imports: [FormsModule],
  templateUrl: './storefront.html',
  styleUrl: './app.css'
})
export class StorefrontComponent implements OnInit {

  private readonly apiUrl =
    'http://localhost/shopsphere-api/api/auth.php';

  // Search and categories
  searchTerm = '';
  selectedCategory = 'All';

  // Cart
  cartOpen = false;
  cartItems: Product[] = [];
  cartLoading = false;

  // Messages
  toastMessage = '';

  // Login / Signup
  showAuth = false;
  authMode: 'login' | 'register' = 'login';

  isLoggedIn = false;
  userName = '';

  authName = '';
  authEmail = '';
  authPassword = '';
  showPassword = false;
  authMessage = '';
  authLoading = false;

  categories = [
    { name: 'All', icon: '✦' },
    { name: 'Fashion', icon: '👕' },
    { name: 'Electronics', icon: '🎧' },
    { name: 'Home', icon: '🏠' },
    { name: 'Accessories', icon: '⌚' }
  ];

  // Current ShopSphere products
  products: Product[] = [
    {
      id: 1,
      name: 'Classic White Sneakers',
      category: 'Fashion',
      price: 1499,
      oldPrice: 2199,
      rating: 4.8,
      image: 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=700&q=85',
      badge: 'Bestseller'
    },
    {
      id: 2,
      name: 'Premium Wireless Headphones',
      category: 'Electronics',
      price: 2499,
      oldPrice: 3499,
      rating: 4.7,
      image: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=700&q=85',
      badge: 'Popular'
    },
    {
      id: 3,
      name: 'Minimalist Wrist Watch',
      category: 'Accessories',
      price: 1899,
      oldPrice: 2799,
      rating: 4.6,
      image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=700&q=85'
    },
    {
      id: 4,
      name: 'Modern Table Lamp',
      category: 'Home',
      price: 999,
      oldPrice: 1499,
      rating: 4.5,
      image: 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=700&q=85',
      badge: 'New'
    },
    {
      id: 5,
      name: 'Everyday Casual Backpack',
      category: 'Accessories',
      price: 1299,
      oldPrice: 1899,
      rating: 4.4,
      image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=700&q=85'
    },
    {
      id: 6,
      name: 'Classic Cotton T-Shirt',
      category: 'Fashion',
      price: 599,
      oldPrice: 899,
      rating: 4.7,
      image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=700&q=85'
    },
    {
      id: 7,
      name: 'Premium Smart Watch',
      category: 'Electronics',
      price: 3299,
      oldPrice: 4499,
      rating: 4.8,
      image: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=700&q=85',
      badge: 'Trending'
    },
    {
      id: 8,
      name: 'Modern Home Decor',
      category: 'Home',
      price: 799,
      oldPrice: 1199,
      rating: 4.3,
      image: 'https://images.unsplash.com/photo-1490312278390-ab64016e0aa9?auto=format&fit=crop&w=700&q=85'
    }
  ];

  constructor(
    private http: HttpClient,
    private cartService: CartService
  ) {}

  ngOnInit(): void {
    this.checkSession();
  }

  // -----------------------------
  // Products and Search
  // -----------------------------

  get filteredProducts(): Product[] {
    return this.products.filter(product => {
      const matchesCategory =
        this.selectedCategory === 'All' ||
        product.category === this.selectedCategory;

      const matchesSearch =
        product.name.toLowerCase().includes(
          this.searchTerm.toLowerCase().trim()
        );

      return matchesCategory && matchesSearch;
    });
  }

  selectCategory(category: string): void {
    this.selectedCategory = category;
  }

  onSearch(value: string): void {
    this.searchTerm = value;
  }

  scrollToProducts(): void {
    document.getElementById('products')?.scrollIntoView({
      behavior: 'smooth'
    });
  }

  // -----------------------------
  // Cart
  // -----------------------------

  get cartCount(): number {
    return this.cartItems.length;
  }

  get cartTotal(): number {
    return this.cartItems.reduce(
      (total, item) => total + Number(item.price),
      0
    );
  }

  loadCart(): void {
    if (!this.isLoggedIn) {
      this.cartItems = [];
      return;
    }

    this.cartLoading = true;

    this.cartService.getCart().subscribe({
      next: response => {
        this.cartLoading = false;

        if (response.success && response.data) {
          const loadedItems: Product[] = [];

          response.data.forEach(cartItem => {
            const product = this.products.find(
              item => item.id === Number(cartItem.product_id)
            );

            if (product) {
              for (
                let i = 0;
                i < Number(cartItem.quantity);
                i++
              ) {
                loadedItems.push(product);
              }
            }
          });

          this.cartItems = loadedItems;
        } else {
          this.cartItems = [];
        }
      },
      error: () => {
        this.cartLoading = false;
        this.cartItems = [];
      }
    });
  }

  addToCart(product: Product): void {
    if (!this.isLoggedIn) {
      this.openAuth('login');
      this.authMessage = 'Please login to add products to cart.';
      return;
    }

    this.cartService.addToCart(product.id).subscribe({
      next: response => {
        if (response.success) {
          this.loadCart();
          this.showToast(product.name + ' added to cart!');
        } else {
          this.showToast(
            response.message || 'Could not add product to cart.'
          );
        }
      },
      error: (error: HttpErrorResponse) => {
        this.showToast(
          error.error?.message || 'Could not add product to cart.'
        );
      }
    });
  }

  removeFromCart(index: number): void {
    const product = this.cartItems[index];

    if (!product) {
      return;
    }

    this.cartService.removeFromCart(product.id).subscribe({
      next: response => {
        if (response.success) {
          this.loadCart();
          this.showToast('Product removed from cart.');
        } else {
          this.showToast(
            response.message || 'Could not remove product.'
          );
        }
      },
      error: (error: HttpErrorResponse) => {
        this.showToast(
          error.error?.message || 'Could not remove product.'
        );
      }
    });
  }

  toggleCart(): void {
    this.cartOpen = !this.cartOpen;

    if (this.cartOpen && this.isLoggedIn) {
      this.loadCart();
    }
  }

  // -----------------------------
  // Login / Signup
  // -----------------------------

  checkSession(): void {
    this.http.get<AuthResponse>(
      `${this.apiUrl}?action=me`,
      { withCredentials: true }
    ).subscribe({
      next: response => {
        if (response.success && response.data) {
          this.setLoggedInUser(response.data);
          this.loadCart();
        } else {
          this.resetUser();
        }
      },
      error: () => {
        this.isLoggedIn = false;
        this.userName = '';
      }
    });
  }

  openAuth(mode: 'login' | 'register'): void {
    this.authMode = mode;
    this.showAuth = true;
    this.authMessage = '';
    this.authLoading = false;
  }

  closeAuth(): void {
    if (this.authLoading) {
      return;
    }

    this.showAuth = false;
    this.authMessage = '';
  }

  switchAuthMode(): void {
    if (this.authLoading) {
      return;
    }

    this.authMode =
      this.authMode === 'login' ? 'register' : 'login';

    this.authMessage = '';
    this.authPassword = '';
  }

  submitAuth(): void {
    if (this.authLoading) {
      return;
    }

    const name = this.authName.trim();
    const email = this.authEmail.trim().toLowerCase();
    const password = this.authPassword;

    if (this.authMode === 'register' && !name) {
      this.authMessage = 'Please enter your full name.';
      return;
    }

    if (this.authMode === 'register' && name.length > 100) {
      this.authMessage = 'Name must be under 100 characters.';
      return;
    }

    if (
      !email ||
      !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    ) {
      this.authMessage = 'Please enter a valid email address.';
      return;
    }

    if (password.length < 8) {
      this.authMessage =
        'Password must contain at least 8 characters.';
      return;
    }

    if (this.authMode === 'register') {
      this.register(name, email, password);
    } else {
      this.login(email, password);
    }
  }

  private register(
    name: string,
    email: string,
    password: string
  ): void {
    this.authLoading = true;
    this.authMessage = '';

    this.http.post<AuthResponse>(
      `${this.apiUrl}?action=register`,
      {
        name,
        email,
        password
      },
      {
        withCredentials: true
      }
    ).subscribe({
      next: response => {
        this.authLoading = false;

        if (response.success && response.data) {
          this.setLoggedInUser(response.data);
          this.showAuth = false;
          this.clearAuthFields();
          this.loadCart();
          this.showToast('Account created successfully!');
        } else {
          this.authMessage =
            response.message || 'Registration failed.';
        }
      },
      error: (error: HttpErrorResponse) => {
        this.authLoading = false;
        this.authMessage = this.getErrorMessage(error);
      }
    });
  }

  private login(
    email: string,
    password: string
  ): void {
    this.authLoading = true;
    this.authMessage = '';

    this.http.post<AuthResponse>(
      `${this.apiUrl}?action=login`,
      {
        email,
        password
      },
      {
        withCredentials: true
      }
    ).subscribe({
      next: response => {
        this.authLoading = false;

        if (response.success && response.data) {
          this.setLoggedInUser(response.data);
          this.showAuth = false;
          this.clearAuthFields();
          this.loadCart();
          this.showToast('Welcome to ShopSphere!');
        } else {
          this.authMessage =
            response.message || 'Login failed.';
        }
      },
      error: (error: HttpErrorResponse) => {
        this.authLoading = false;
        this.authMessage = this.getErrorMessage(error);
      }
    });
  }

  logout(): void {
    this.http.post<AuthResponse>(
      `${this.apiUrl}?action=logout`,
      {},
      {
        withCredentials: true
      }
    ).subscribe({
      next: response => {
        if (response.success) {
          this.resetUser();
          this.cartItems = [];
          this.showToast('Logged out successfully.');
        }
      },
      error: () => {
        this.showToast(
          'Logout failed. Please check your connection.'
        );
      }
    });
  }

  private setLoggedInUser(user: ShopUser): void {
    this.isLoggedIn = true;
    this.userName = user.name;
  }

  private resetUser(): void {
    this.isLoggedIn = false;
    this.userName = '';
    this.cartItems = [];
    this.clearAuthFields();
  }

  private clearAuthFields(): void {
    this.authName = '';
    this.authEmail = '';
    this.authPassword = '';
    this.showPassword = false;
    this.authMessage = '';
  }

  private getErrorMessage(error: HttpErrorResponse): string {
    if (error.status === 0) {
      return 'Cannot connect to server. Check XAMPP and PHP API.';
    }

    if (
      error.error &&
      typeof error.error.message === 'string'
    ) {
      return error.error.message;
    }

    return 'Something went wrong. Please try again.';
  }

  private showToast(message: string): void {
    this.toastMessage = message;

    setTimeout(() => {
      this.toastMessage = '';
    }, 2500);
  }
}