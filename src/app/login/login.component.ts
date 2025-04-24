import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../auth.service';
import { FormsModule } from '@angular/forms';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-login',
  imports: [FormsModule, NgIf],
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})

export class LoginComponent {
  email = '';
  password = '';
  errorMessage = '';
  isLoading = false;

  constructor(private authService: AuthService, private router: Router) {}

  login() {
    this.isLoading = true;
    this.errorMessage = '';
    
    this.authService.login(this.email, this.password).subscribe({
      next: (response) => {
        // Store the token and navigate to the home/profile page
        this.authService.setToken(response.token);
        this.isLoading = false;
        this.router.navigate(['/home']);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = err.error?.message || 'Login failed. Please check your credentials.';
        console.error('Login error:', err);
      }
    });
  }

  gotoRegister() {
    this.router.navigate(['/register']);
  }

  testConnections() {
    this.isLoading = true;
    this.errorMessage = '';
    
    this.authService.testAllConnections().subscribe({
      next: (response) => {
        this.isLoading = false;
        alert('Connection test successful! Check console for details.');
        console.log('Connection test results:', response);
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = 'Connection test failed. See console for details.';
        console.error('Connection test error:', err);
      }
    });
  }
}
