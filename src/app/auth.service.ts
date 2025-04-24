import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class AuthService {

  private apiUrl = environment.apiUrl;

  constructor(private http: HttpClient) { }

  login(email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/user/login`, { email, password });
  }

  register(name: string, email: string, password: string): Observable<any> {
    return this.http.post<any>(`${this.apiUrl}/user/register`, { name, email, password });
  }

  setToken(token: string): void {
    localStorage.setItem('authToken', token);
  }

  getToken(): string | null {
    return localStorage.getItem('authToken');
  }

  logout(): void {
    localStorage.removeItem('authToken');
  }

  testConnection(): Observable<any> {
    return this.http.get(`${this.apiUrl}/test-connection`);
  }

  testCognitoConnection(): Observable<any> {
    return this.http.get(`${this.apiUrl}/test/cognito-connection`);
  }

  testDynamoDbConnection(): Observable<any> {
    return this.http.get(`${this.apiUrl}/test/dynamodb-connection`);
  }

  testAllConnections(): Observable<any> {
    return this.http.get(`${this.apiUrl}/test/all-connections`);
  }

  getProfile(): Observable<any> {
    const headers = new HttpHeaders({
      'Authorization': `Bearer ${this.getToken()}`
    });
    return this.http.get(`${this.apiUrl}/user/profile`, { headers });
  }
}
