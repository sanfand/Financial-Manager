<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5" id="profileApp">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Profile Update Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true" ref="profileModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
                    <h5 class="modal-title">Update Profile</h5>
                    <button type="button" class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
                    <form @submit.prevent="updateProfile" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" v-model="user.name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" v-model="user.email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password <small class="text-muted">(Leave blank to keep current)</small></label>
                            <input type="password" v-model="password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Profile Picture</label>
                            <input type="file" @change="previewImage" class="form-control">
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
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Profile</h6>
            <button class="btn btn-primary btn-sm float-right" @click="openModal">Edit Profile</button>
        </div>
        <div class="card-body text-center">
            <img :src="user.profile_pic || '<?php echo base_url("assets/default-avatar.png"); ?>'" class="rounded-circle mb-3" style="width:120px;height:120px;object-fit:cover;">
            <h5>{{ user.name }}</h5>
            <p>{{ user.email }}</p>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data(){
        return {
            title: '<?php echo $title; ?>',
            user: <?php echo json_encode($user); ?>,
            password: '',
            preview: '',
            errorMessage: ''
        };
    },
    methods:{
        openModal(){
            this.preview = this.user.profile_pic;
            new bootstrap.Modal(this.$refs.profileModal).show();
        },
        closeModal(){
            new bootstrap.Modal(this.$refs.profileModal).hide();
            this.errorMessage = '';
            this.password = '';
            this.preview = '';
        },
        previewImage(e){
            const file = e.target.files[0];
            if(file) this.preview = URL.createObjectURL(file);
        },
        async updateProfile(){
            try{
                const formData = new FormData();
                formData.append('name', this.user.name);
                formData.append('email', this.user.email);
                formData.append('password', this.password);
                const fileInput = this.$refs.profileModal.querySelector('input[type=file]');
                if(fileInput.files[0]) formData.append('profile_pic', fileInput.files[0]);

                const res = await axios.post('<?php echo base_url("profile/update"); ?>', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                if(res.data.status==='success'){
                    this.user = res.data.user;
                    this.closeModal();
                } else {
                    this.errorMessage = res.data.message;
                }
            }catch(e){
                console.error(e);
                this.errorMessage = 'An error occurred. Please try again.';
            }
        }
    }
}).mount('#profileApp');
</script>

<?php $this->load->view('footer'); ?>
