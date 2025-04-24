import { Component } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { AuthService } from '../auth.service';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Router } from '@angular/router';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-register',
  imports: [FormsModule, NgIf],
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})
export class RegisterComponent {
  user = {
    name: '',
    email: '',
    password: ''
  };
  
  isLoading = false;
  errorMessage = '';
  successMessage = '';
  debugResponse: any = null;

  constructor(
    private authService: AuthService, 
    private http: HttpClient,
    private router: Router
  ) {}

  register() {
    // Add detailed console logs to debug
    console.log('Register button clicked');
    console.log('User data being sent:', {
      name: this.user.name,
      email: this.user.email,
      password: this.user.password ? '[PASSWORD PROVIDED]' : '[NO PASSWORD]' // Don't log actual password
    });
    
    // Validate form fields
    if (!this.user.name || !this.user.email || !this.user.password) {
      this.errorMessage = 'All fields are required';
      console.error('Form validation failed - missing required fields');
      return;
    }
    
    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = '';
    this.debugResponse = null;

    // Make a direct HTTP request to see the raw response
    const apiUrl = 'http://localhost:8000/api/user/register';
    console.log(`Sending registration request to: ${apiUrl}`);
    
    const headers = new HttpHeaders({
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    });
    
    this.http.post(apiUrl, this.user, { headers, observe: 'response' }).subscribe({
      next: (response) => {
        console.log('Registration API full response:', response);
        this.isLoading = false;
        
        // For debugging purposes, show the response
        this.debugResponse = {
          status: response.status,
          statusText: response.statusText,
          body: response.body,
          headers: {
            contentType: response.headers.get('Content-Type')
          }
        };
        
        const responseBody = response.body;
        
        if (responseBody && responseBody['status'] === 'debug') {
          this.successMessage = 'Debug response received. Check console and below for details.';
        } else {
          this.successMessage = 'Registration successful! Redirecting to login...';
          
          // Clear the form
          this.user = {
            name: '',
            email: '',
            password: ''
          };
          
          // Redirect to login page after a short delay
          setTimeout(() => {
            this.router.navigate(['/login']);
          }, 2000);
        }
      },
      error: (error) => {
        console.error('Registration error details:', error);
        this.isLoading = false;
        
        // For debugging purposes, show the error response
        this.debugResponse = {
          status: error.status,
          statusText: error.statusText,
          error: error.error,
          message: error.message
        };
        
        if (error.error && error.error.error) {
          this.errorMessage = error.error.error;
        } else if (error.error && error.error.message) {
          this.errorMessage = error.error.message;
        } else if (error.message) {
          this.errorMessage = error.message;
        } else {
          this.errorMessage = 'Registration failed. Please try again.';
        }
      }
    });
  }
  
  gotoLogin() {
    this.router.navigate(['/login']);
  }
}
