import { Component, OnInit } from '@angular/core';
import { ProfileService } from '../profile.service';
import { FormsModule, NgModel } from '@angular/forms';
@Component({
  selector: 'app-profile',
  imports: [FormsModule],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit{

  profile = { name: '', email: ''};
  message = '';

  constructor(private profileService: ProfileService){}
  ngOnInit(): void {
    this.profileService.getProfile().subscribe(data => {
      this.profile = data;
    });
  }

  save(){
    this.profileService.saveProfile(this.profile).subscribe({
      next: () => this.message = "Profile Saved",
      error: err => this.message = "Error saving profile: " + err.message
    });
  }

}
