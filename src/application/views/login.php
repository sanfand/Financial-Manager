<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow" id="loginApp">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark text-center">Login</h6>
                </div>
                <div class="card-body">
                    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ errorMessage }}
                        <button type="button" class="btn-close" @click="errorMessage = ''"></button>
                    </div>
                    <form @submit.prevent="login">
                        <div class="form-group mb-3">
                            <label for="username_email" class="form-label">Username or Email</label>
                            <input type="text" v-model="usernameEmail" id="username_email" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" v-model="password" id="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <small>Don't have an account? <a href="<?php echo base_url('auth/register'); ?>">Register here</a></small>
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
            usernameEmail: '',
            password: '',
            errorMessage: ''
        }
    },
    methods: {
        async login() {
            this.errorMessage = ''; 
            try {
                const formData = new FormData();
                formData.append('username_email', this.usernameEmail.trim());
                formData.append('password', this.password);

                const response = await axios.post('<?php echo base_url('auth/login'); ?>', formData, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = response.data;

                if (data.status === 'success') {
                    window.location.href = '<?php echo base_url('dashboard'); ?>';
                } else {
                    this.errorMessage = data.message || 'Login failed. Please try again.';
                }
            } catch (err) {
                console.error('Login error:', err);
                this.errorMessage = err.response?.data?.message || 'Server error. Please try again later.';
            }
        }
    }
}).mount('#loginApp');
</script>

<?php $this->load->view('footer'); ?>