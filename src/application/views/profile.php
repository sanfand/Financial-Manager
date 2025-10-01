<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<style>
    #profileApp {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    #profileApp::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 1px, transparent 1px);
        background-size: 50px 50px;
        opacity: 0.5;
        animation: float 20s infinite linear;
    }

    @keyframes float {
        0% {
            transform: translate(0, 0) rotate(0deg);
        }

        100% {
            transform: translate(50px, 50px) rotate(360deg);
        }
    }

    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background: rgba(255, 255, 255, 0.9);
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background: linear-gradient(45deg, #a8d8ea, #74c0fc);
        color: white;
        border-radius: 15px 15px 0 0 !important;
    }

    .profile-pic {
        border: 4px solid #a8d8ea;
        box-shadow: 0 4px 15px rgba(168, 216, 234, 0.5);
    }
</style>

<div class="container-fluid py-5" id="profileApp">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Profile Update Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true" ref="profileModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
                    <h5 class="modal-title">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
                    <form @submit.prevent="updateProfile" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" v-model="user.name" class="form-control" id="name" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" v-model="user.email" class="form-control" id="email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password <small class="text-muted">(Leave blank to keep
                                    current)</small></label>
                            <input type="password" v-model="password" class="form-control" id="password">
                        </div>
                        <div class="form-group mb-3">
                            <label for="profilePic">Profile Picture</label>
                            <input type="file" ref="fileInput" @change="previewImage" class="form-control"
                                id="profilePic" accept="image/jpeg,image/png">
                            <img v-if="preview" :src="preview" class="img-fluid mt-2" style="max-height:150px;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Display Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark" style="color: white;">Profile</h6>
            <button class="btn btn-primary btn-sm" @click="openModal">Edit Profile</button>
        </div>
        <div class="card-body text-center">
            <img :src="user.profile_pic || '<?php echo base_url('assets/default-avatar.png'); ?>'"
                class="rounded-circle mb-3 profile-pic" style="width:120px;height:120px;object-fit:cover;"
                alt="Profile Picture">
            <h5>{{ user.name }}</h5>
            <p>{{ user.email }}</p>
        </div>
    </div>
</div>

<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                title: '<?php echo $title; ?>',
                user: <?php echo json_encode($user); ?>,
                password: '',
                preview: '',
                errorMessage: '',
                file: null
            };
        },
        methods: {
            openModal() {
                this.preview = this.user.profile_pic || '<?php echo base_url("assets/default-avatar.png"); ?>';
                this.errorMessage = '';
                this.password = '';
                this.file = null;
                const modal = new bootstrap.Modal(this.$refs.profileModal);
                modal.show();
            },
            closeModal() {
                const modal = bootstrap.Modal.getInstance(this.$refs.profileModal);
                if (modal) {
                    modal.hide();
                }
                this.errorMessage = '';
                this.password = '';
                this.file = null;
                if (this.preview) {
                    URL.revokeObjectURL(this.preview);
                    this.preview = '';
                }
            },
            previewImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.file = file;
                    if (this.preview) {
                        URL.revokeObjectURL(this.preview);
                    }
                    this.preview = URL.createObjectURL(file);
                } else {
                    this.file = null;
                    this.preview = this.user.profile_pic || '<?php echo base_url("assets/default-avatar.png"); ?>';
                }
            },
            async updateProfile() {
                try {
                    const formData = new FormData();
                    formData.append('name', this.user.name);
                    formData.append('email', this.user.email);
                    if (this.password) {
                        formData.append('password_hash', this.password);
                    }
                    if (this.file) {
                        formData.append('profile_pic', this.file);
                    }

                    const response = await axios.post('<?php echo base_url("profile/update"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });

                    if (response.data.status === 'success') {
                        this.user = response.data.user;
                        this.closeModal();
                    } else {
                        this.errorMessage = response.data.message || 'Profile update failed';
                    }
                } catch (error) {
                    console.error('Update error:', error);
                    this.errorMessage = 'An error occurred. Please try again.';
                }
            }
        },
        mounted() {
            if (!this.user || !this.user.name || !this.user.email) {
                this.errorMessage = 'Failed to load user data';
            }
        }
    }).mount('#profileApp');
</script>

<?php $this->load->view('footer'); ?>
