<template>
  <div class="container-fluid py-5">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true" ref="profileModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
            <h5 class="modal-title">Edit Profile</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
            <form @submit.prevent="updateProfile" enctype="multipart/form-data">
              <div class="form-group mb-3">
                <label for="profileName">Name</label>
                <input type="text" v-model="user.name" class="form-control" id="profileName" required>
              </div>
              <div class="form-group mb-3">
                <label for="profileEmail">Email</label>
                <input type="email" v-model="user.email" class="form-control" id="profileEmail" required>
              </div>
              <div class="form-group mb-3">
                <label for="profilePassword">Password (leave blank to keep current)</label>
                <input type="password" v-model="user.password_hash" class="form-control" id="profilePassword">
              </div>
              <div class="form-group mb-3">
                <label for="profilePic">Profile Picture</label>
                <input type="file" class="form-control" id="profilePic" @change="handleFileChange" accept="image/*">
                <small class="form-text text-muted">Max size: 2MB. Allowed types: JPG, JPEG, PNG, GIF</small>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Update Profile</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-dark">Your Profile</h6>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="text-center">
          <img :src="user.profile_pic || '/default-avatar.png'" class="profile-pic rounded-circle" style="width:100px;height:100px;" @error="handleImageError">
          <h5 class="mt-3">{{ user.name || 'N/A' }}</h5>
          <p>{{ user.email || 'N/A' }}</p>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#profileModal">Edit Profile</button>
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
        console.log('Profile data:', response.data);
        if (response.data.status === 'success') {
          this.user = response.data.user || {};
        } else {
          this.errorMessage = response.data.message || 'Failed to load profile';
        }
      } catch (e) {
        console.error('Load profile error:', e.response || e.message);
        this.errorMessage = e.response?.data?.message || 'Error loading profile';
        if (e.response?.status === 401 || e.response?.status === 403) clearToken();
      }
    },
    handleFileChange(event) {
      this.profilePicFile = event.target.files[0] || null;
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
        
        console.log('Update profile response:', response.data);

        if (response.data.status === 'success') {
          this.user = response.data.user || {};
          // Add timestamp to force image reload
          if (this.user.profile_pic) {
            this.user.profile_pic = this.user.profile_pic + '?t=' + new Date().getTime();
          }
          this.profilePicFile = null;
          this.successMessage = response.data.message || 'Profile updated successfully';
          
          // Close modal after success
          const modal = bootstrap.Modal.getInstance(this.$refs.profileModal);
          if (modal) {
            modal.hide();
          }
          
          setTimeout(() => {
            this.successMessage = '';
          }, 3000);
        } else {
          this.errorMessage = response.data.message || 'Failed to update profile';
        }
      } catch (e) {
        console.error('Update profile error:', e.response || e.message);
        this.errorMessage = e.response?.data?.message || 'Error updating profile';
        if (e.response?.status === 401 || e.response?.status === 403) clearToken();
      }
    }
  }
};
</script>

<style scoped>
.card {
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.modal-header {
  background-color: #a8d8ea;
  color: #fff;
}
.profile-pic {
  object-fit: cover;
  border: 3px solid #a8d8ea;
}
</style>