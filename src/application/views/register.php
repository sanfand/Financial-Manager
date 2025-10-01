<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow" id="registerApp">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark text-center">Register</h6>
                </div>
                <div class="card-body">
                    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ errorMessage }}
                        <button type="button" class="btn-close" @click="errorMessage=''"></button>
                    </div>
                    <form @submit.prevent="register">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" v-model="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" v-model="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" v-model="password" id="password" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password_confirm" class="form-label">Confirm Password</label>
                            <input type="password" v-model="passwordConfirm" id="password_confirm" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                    <div class="mt-3 text-center">
                        <small>Already have an account? <a href="<?php echo base_url('auth/login'); ?>">Login here</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            name: '',
            email: '',
            password: '',
            passwordConfirm: '',
            errorMessage: ''
        }
    },
    methods: {
        async register() {
            this.errorMessage = ''; 
            try {
                const formData = new FormData();
                formData.append('name', this.name.trim());
                formData.append('email', this.email.trim());
                formData.append('password', this.password);
                formData.append('password_confirm', this.passwordConfirm);

                const response = await axios.post('<?php echo base_url("auth/register"); ?>', formData, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = response.data;

                if (data.status === 'success') {
                    window.location.href = '<?php echo base_url("auth/login"); ?>';
                } else {
                    this.errorMessage = data.message || 'Registration failed. Please try again.';
                }
            } catch (err) {
                console.error('Registration error:', err);
                this.errorMessage = err.response?.data?.message || 'Server error. Please try again later.';
            }
        }
    }
}).mount('#registerApp');
</script>

<?php $this->load->view('footer'); ?>