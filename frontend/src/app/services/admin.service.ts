
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Injectable({
  providedIn: 'root'
})
export class AdminService {
  private apiUrl = 'http://localhost/shopsphere-api/api/admin';

  constructor(private http: HttpClient) {}

  login(email: string, password: string) {
    return this.http.post<any>(
      `${this.apiUrl}/auth.php?action=login`,
      { email, password },
      { withCredentials: true }
    );
  }

  me() {
    return this.http.get<any>(
      `${this.apiUrl}/auth.php?action=me`,
      { withCredentials: true }
    );
  }

  logout() {
    return this.http.post<any>(
      `${this.apiUrl}/auth.php?action=logout`,
      {},
      { withCredentials: true }
    );
  }

  getProducts() {
    return this.http.get<any>(
      `${this.apiUrl}/products.php?action=list`,
      { withCredentials: true }
    );
  }

  addProduct(product: any) {
    return this.http.post<any>(
      `${this.apiUrl}/products.php?action=add`,
      product,
      { withCredentials: true }
    );
  }

  updateProduct(product: any) {
    return this.http.post<any>(
      `${this.apiUrl}/products.php?action=update`,
      product,
      { withCredentials: true }
    );
  }

  deleteProduct(id: number) {
    return this.http.post<any>(
      `${this.apiUrl}/products.php?action=delete`,
      { id },
      { withCredentials: true }
    );
  }
}