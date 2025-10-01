<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5" id="categoriesApp">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true" ref="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
                    <form @submit.prevent="addCategory">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                            :value="csrfToken">
                        <div class="form-group mb-3">
                            <label for="addName">Name</label>
                            <input type="text" v-model="newCategory.name" class="form-control" id="addName" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="addType">Type</label>
                            <select v-model="newCategory.type" class="form-control" id="addType" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true" ref="editModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
                    <form @submit.prevent="editCategory">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>"
                            :value="csrfToken">
                        <input type="hidden" v-model="selectedCategory.id">
                        <div class="form-group mb-3">
                            <label for="editName">Name</label>
                            <input type="text" v-model="selectedCategory.name" class="form-control" id="editName"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="editType">Type</label>
                            <select v-model="selectedCategory.type" class="form-control" id="editType" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update Category</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Search Categories</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" v-model="searchQuery" class="form-control" placeholder="Search name">
                </div>
                <div class="col-md-4">
                    <select v-model="searchType" class="form-control">
                        <option value="">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm me-2" @click="searchCategories(1)">Search</button>
                    <button class="btn btn-secondary btn-sm" @click="resetSearch">Reset</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Categories</h6>
            <button class="btn btn-primary btn-sm" @click="openAddModal">Add Category</button>
        </div>
        <div class="card-body">
            <div v-if="errorMessage" class="alert alert-danger mb-3">{{ errorMessage }}</div>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id">
                        <td>{{ category.name || 'N/A' }}</td>
                        <td>{{ capitalize(category.type) }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm me-1" @click="openEditModal(category)">Edit</button>
                            <button class="btn btn-danger btn-sm" @click="deleteCategory(category.id)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="3" class="text-center">{{ noCategoriesMessage }}</td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination -->
            <nav v-if="totalPages > 1">
                <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                        <a class="page-link" href="#" @click.prevent="loadPage(currentPage - 1)"><</a>
                    </li>
                    <li v-for="page in totalPages" :key="page" class="page-item"
                        :class="{ active: page === currentPage }">
                        <a class="page-link" href="#" @click.prevent="loadPage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                        <a class="page-link" href="#" @click.prevent="loadPage(currentPage + 1)">></a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    const { createApp } = Vue;
    // Configure Axios to include X-Requested-With header
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    createApp({
        data() {
            return {
                title: '<?php echo isset($title) ? addslashes($title) : 'Categories'; ?>',
                categories: [],
                newCategory: { name: '', type: 'income' },
                selectedCategory: { id: '', name: '', type: 'income' },
                searchQuery: '',
                searchType: '',
                errorMessage: '',
                noCategoriesMessage: 'No categories found. Try adding a new category or adjusting your search.',
                currentPage: 1,
                totalPages: 1,
                perPage: 6,
                csrfToken: '<?php echo $this->security->get_csrf_hash(); ?>'
            };
        },
        methods: {
            capitalize(str) {
                return str ? str.charAt(0).toUpperCase() + str.slice(1) : 'N/A';
            },
            openAddModal() {
                this.errorMessage = '';
                this.newCategory = { name: '', type: 'income' };
                try {
                    const modal = new bootstrap.Modal(this.$refs.addModal);
                    modal.show();
                } catch (e) {
                    console.error('Error opening add modal:', e);
                    this.errorMessage = 'Failed to open add modal';
                }
            },
            closeAddModal() {
                try {
                    const modal = bootstrap.Modal.getInstance(this.$refs.addModal);
                    if (modal) modal.hide();
                    this.errorMessage = '';
                } catch (e) {
                    console.error('Error closing add modal:', e);
                    this.errorMessage = 'Failed to close add modal';
                }
            },
            openEditModal(category) {
                this.errorMessage = '';
                this.selectedCategory = { ...category };
                try {
                    const modal = new bootstrap.Modal(this.$refs.editModal);
                    modal.show();
                } catch (e) {
                    console.error('Error opening edit modal:', e);
                    this.errorMessage = 'Failed to open edit modal';
                }
            },
            closeEditModal() {
                try {
                    const modal = bootstrap.Modal.getInstance(this.$refs.editModal);
                    if (modal) modal.hide();
                    this.errorMessage = '';
                } catch (e) {
                    console.error('Error closing edit modal:', e);
                    this.errorMessage = 'Failed to close edit modal';
                }
            },
            async addCategory() {
                try {
                    const formData = new FormData();
                    formData.append('name', this.newCategory.name?.trim() || '');
                    formData.append('type', this.newCategory.type || 'income');
                    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', this.csrfToken);
                    const res = await axios.post('<?php echo base_url("categories/create"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    console.log('Add category response:', res.data);
                    if (res.data.status === 'success') {
                        this.csrfToken = res.data.csrf_hash || this.csrfToken;
                        await this.loadPage(1); // Go to first page to show new category
                        this.closeAddModal();
                    } else {
                        this.errorMessage = res.data.message || 'Failed to add category';
                    }
                } catch (e) {
                    console.error('Add category error:', e);
                    this.errorMessage = e.response?.data?.message || 'Error adding category';
                }
            },
            async editCategory() {
                try {
                    if (!this.selectedCategory.id) {
                        this.errorMessage = 'No category selected';
                        return;
                    }
                    const formData = new FormData();
                    formData.append('id', this.selectedCategory.id);
                    formData.append('name', this.selectedCategory.name?.trim() || '');
                    formData.append('type', this.selectedCategory.type || 'income');
                    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', this.csrfToken);
                    const res = await axios.post('<?php echo base_url("categories/edit"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    console.log('Edit category response:', res.data);
                    if (res.data.status === 'success') {
                        this.csrfToken = res.data.csrf_hash || this.csrfToken;
                        await this.loadPage(this.currentPage);
                        this.closeEditModal();
                    } else {
                        this.errorMessage = res.data.message || 'Failed to update category';
                    }
                } catch (e) {
                    console.error('Edit category error:', e);
                    this.errorMessage = e.response?.data?.message || 'Error editing category';
                }
            },
            async deleteCategory(id) {
                if (!confirm('Are you sure you want to delete this category?')) return;
                try {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', this.csrfToken);
                    const res = await axios.post('<?php echo base_url("categories/delete"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    console.log('Delete category response:', res.data);
                    if (res.data.status === 'success') {
                        this.csrfToken = res.data.csrf_hash || this.csrfToken;
                        const newPage = this.currentPage > this.totalPages && this.totalPages > 1 ? this.currentPage - 1 : this.currentPage;
                        await this.loadPage(newPage || 1);
                    } else {
                        this.errorMessage = res.data.message || 'Failed to delete category';
                    }
                } catch (e) {
                    console.error('Delete category error:', e);
                    this.errorMessage = e.response?.data?.message || 'Error deleting category';
                }
            },
            async searchCategories(page = 1) {
                try {
                    const formData = new FormData();
                    formData.append('search', this.searchQuery?.trim() || '');
                    formData.append('type', this.searchType || '');
                    formData.append('page', page);
                    formData.append('per_page', this.perPage);
                    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', this.csrfToken);
                    const res = await axios.post('<?php echo base_url("categories/search"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    console.log('Search response:', res.data);
                    if (res.data.status === 'success') {
                        this.csrfToken = res.data.csrf_hash || this.csrfToken;
                        this.categories = Array.isArray(res.data.categories) ? res.data.categories : [];
                        this.currentPage = parseInt(res.data.current_page) || 1;
                        this.totalPages = parseInt(res.data.total_pages) || 1;
                        this.noCategoriesMessage = this.categories.length === 0
                            ? 'No categories found. Try adding a new category or adjusting your search.'
                            : '';
                        this.debugMessage = `Loaded ${this.categories.length} categories for user ID: ${res.data.user_id || 'unknown'}`;
                    } else {
                        this.errorMessage = res.data.message || 'Search failed';
                        this.categories = [];
                        this.currentPage = 1;
                        this.totalPages = 1;
                        this.noCategoriesMessage = 'Search failed. Please try again.';
                        this.debugMessage = 'Search failed: ' + (res.data.message || 'Unknown error');
                    }
                } catch (e) {
                    console.error('Search categories error:', e);
                    this.errorMessage = e.response?.data?.message || 'Error searching categories';
                    this.categories = [];
                    this.currentPage = 1;
                    this.totalPages = 1;
                    this.noCategoriesMessage = 'Error loading categories. Please check your connection.';
                    this.debugMessage = 'Search error: ' + (e.response?.data?.message || e.message);
                }
            },
            async loadPage(page) {
                if (page < 1 || (this.totalPages > 1 && page > this.totalPages)) return;
                await this.searchCategories(page);
            },
            async resetSearch() {
                this.searchQuery = '';
                this.searchType = '';
                this.currentPage = 1;
                this.errorMessage = '';
                this.debugMessage = '';
                await this.searchCategories(1);
            }
        },
        async mounted() {
            try {
                await this.searchCategories(1);
            } catch (e) {
                console.error('Initial load error:', e);
                this.errorMessage = 'Failed to load categories';
                this.noCategoriesMessage = 'Error loading categories. Please check your connection.';
                this.debugMessage = 'Initial load error: ' + (e.response?.data?.message || e.message);
            }
        }
    }).mount('#categoriesApp');
</script>

<?php $this->load->view('footer'); ?>