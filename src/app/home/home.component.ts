import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../auth.service';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-home',
  imports: [NgIf],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit {
  welcomeMessage = 'Welcome to Your Dashboard';
  userProfile: any = null;
  isLoading = false;
  errorMessage = '';

  constructor(
    private authService: AuthService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadUserProfile();
  }

  loadUserProfile() {
    this.isLoading = true;
    this.errorMessage = '';
    
    this.authService.getProfile().subscribe({
      next: (response) => {
        this.userProfile = response;
        this.isLoading = false;
      },
      error: (err) => {
        this.isLoading = false;
        this.errorMessage = 'Failed to load profile. Please try again.';
        console.error('Profile loading error:', err);
      }
    });
  }

  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }

  testConnections() {
    this.isLoading = true;
    
    this.authService.testAllConnections().subscribe({
      next: (response) => {
        this.isLoading = false;
        alert('Connection test successful! Check console for details.');
        console.log('Connection test results:', response);
      },
      error: (err) => {
        this.isLoading = false;
        alert('Connection test failed. See console for details.');
        console.error('Connection test error:', err);
      }
    });
  }
}
