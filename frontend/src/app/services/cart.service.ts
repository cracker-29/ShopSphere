
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface CartItem {
  product_id: number;
  quantity: number;
}

export interface CartResponse {
  success: boolean;
  message: string;
  data: CartItem[] | null;
}

@Injectable({
  providedIn: 'root'
})
export class CartService {

  private apiUrl =
    'http://localhost/shopsphere-api/api/cart.php';

  constructor(private http: HttpClient) {}

  getCart(): Observable<CartResponse> {
    return this.http.get<CartResponse>(
      `${this.apiUrl}?action=list`,
      { withCredentials: true }
    );
  }

  addToCart(productId: number): Observable<CartResponse> {
    return this.http.post<CartResponse>(
      `${this.apiUrl}?action=add`,
      { product_id: productId },
      { withCredentials: true }
    );
  }

  updateQuantity(
    productId: number,
    quantity: number
  ): Observable<CartResponse> {
    return this.http.post<CartResponse>(
      `${this.apiUrl}?action=update`,
      {
        product_id: productId,
        quantity: quantity
      },
      { withCredentials: true }
    );
  }

  removeFromCart(
    productId: number
  ): Observable<CartResponse> {
    return this.http.post<CartResponse>(
      `${this.apiUrl}?action=remove`,
      { product_id: productId },
      { withCredentials: true }
    );
  }

  clearCart(): Observable<CartResponse> {
    return this.http.post<CartResponse>(
      `${this.apiUrl}?action=clear`,
      {},
      { withCredentials: true }
    );
  }
}