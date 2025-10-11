<template>
  <div class="container-fluid py-4">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card profile-card shadow">
          <div class="card-header gradient-bg text-white">
            <div class="d-flex align-items-center">
              <img :src="user.profile_pic || '/default-avatar.png'" class="profile-pic me-3" @error="handleImageError">
              <div>
                <h4 class="mb-1">{{ user.name || 'User' }}</h4>
                <p class="mb-0 opacity-75">{{ user.email || 'No email' }}</p>
              </div>
            </div>
          </div>
          
          <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="mb-0">Profile Information</h5>
              <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profileModal">
                <i class="fas fa-edit me-2"></i>Edit Profile
              </button>
            </div>

            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>

            <div class="row">
              <div class="col-md-6">
                <div class="info-item">
                  <label>Full Name</label>
                  <p>{{ user.name || 'Not set' }}</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-item">
                  <label>Email Address</label>
                  <p>{{ user.email || 'Not set' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true" ref="profileModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header gradient-bg text-white">
            <h5 class="modal-title">Edit Profile</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="updateProfile" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-4 text-center mb-4">
                  <img :src="user.profile_pic || '/default-avatar.png'" class="profile-pic-large mb-3" @error="handleImageError">
                  <div class="form-group">
                    <label for="profilePic" class="btn btn-outline-primary btn-sm w-100">
                      <i class="fas fa-camera me-2"></i>Change Photo
                    </label>
                    <input type="file" class="d-none" id="profilePic" @change="handleFileChange" accept="image/*">
                    <small class="form-text text-muted d-block mt-2">Max 2MB • JPG, PNG, GIF</small>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" v-model="user.name" class="form-control" required>
                  </div>
                  <div class="form-group mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" v-model="user.email" class="form-control" required>
                  </div>
                  <div class="form-group mb-4">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" v-model="user.password_hash" class="form-control" placeholder="Enter new password">
                  </div>
                </div>
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Profile</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../utils/auth';
import { clearToken } from '../utils/auth';
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.min.js';

export default {
  data() {
    return {
      title: 'Profile',
      user: {},
      profilePicFile: null,
      errorMessage: '',
      successMessage: ''
    };
  },
  mounted() {
    this.loadProfile();
  },
  methods: {
    async loadProfile() {
      this.errorMessage = '';
      try {
        const response = await axios.get('/profile');
        if (response.data.status === 'success') {
          this.user = response.data.user || {};
        } else {
          this.errorMessage = response.data.message || 'Failed to load profile';
        }
      } catch (e) {
        this.errorMessage = e.response?.data?.message || 'Error loading profile';
        if (e.response?.status === 401 || e.response?.status === 403) clearToken();
      }
    },
    handleFileChange(event) {
      this.profilePicFile = event.target.files[0] || null;
      if (this.profilePicFile) {
        const reader = new FileReader();
        reader.onload = (e) => {
          document.querySelector('.profile-pic-large').src = e.target.result;
        };
        reader.readAsDataURL(this.profilePicFile);
      }
    },
    handleImageError(event) {
      event.target.src = '/default-avatar.png';
    },
    async updateProfile() {
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const formData = new FormData();
        formData.append('name', this.user.name?.trim() || '');
        formData.append('email', this.user.email?.trim() || '');
        if (this.user.password_hash) {
          formData.append('password_hash', this.user.password_hash);
        }
        if (this.profilePicFile) {
          formData.append('profile_pic', this.profilePicFile);
        }

        const response = await axios.post('/profile/update_profile', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.status === 'success') {
          this.user = response.data.user || {};
          const currentUser = JSON.parse(localStorage.getItem('user') || '{}');
          const updatedUser = { ...currentUser, ...response.data.user };
          localStorage.setItem('user', JSON.stringify(updatedUser));
          
          if (this.user.profile_pic) {
            this.user.profile_pic = this.user.profile_pic + '?t=' + new Date().getTime();
          }
          this.profilePicFile = null;
          this.user.password_hash = '';
          this.successMessage = response.data.message || 'Profile updated successfully';
          
          const modal = bootstrap.Modal.getInstance(this.$refs.profileModal);
          if (modal) modal.hide();
          
          setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
          this.errorMessage = response.data.message || 'Failed to update profile';
        }
      } catch (e) {
        this.errorMessage = e.response?.data?.message || 'Error updating profile';
        if (e.response?.status === 401 || e.response?.status === 403) clearToken();
      }
    }
  }
};
</script>

<style>
.profile-card {
  border-radius: 15px;
  border: none;
}

.gradient-bg {
  background: linear-gradient(135deg, #16576e, #caeee1);
  border-radius: 15px 15px 0 0 !important;
  padding: 2rem;
}

.profile-pic {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(255,255,255,0.3);
}

.profile-pic-large {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #a8d8ea;
}

.info-item {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 10px;
  margin-bottom: 1rem;
}

.info-item label {
  font-weight: 600;
  color: #6c757d;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.info-item p {
  margin: 0;
  font-size: 1.1rem;
  color: #2c3e50;
}

.form-control {
  border-radius: 8px;
  border: 2px solid #e9ecef;
  padding: 0.75rem;
}

.form-control:focus {
  border-color: #a8d8ea;
  box-shadow: 0 0 0 0.2rem rgba(168, 216, 234, 0.25);
}

.btn {
  border-radius: 8px;
  padding: 0.5rem 1.5rem;
}

.alert {
  border-radius: 10px;
  border: none;
}





.btn {
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
}
</style>
<style >
.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-primary {
  background: linear-gradient(135deg, #5563a0, #7c59a0);
}

.btn-success {
  background: linear-gradient(135deg, #56ab2f, #a8e6cf);
}

.btn-danger {
  background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
}

.btn-warning {
  background: linear-gradient(135deg, #f5b7fc, #cc9199);
}



</style>