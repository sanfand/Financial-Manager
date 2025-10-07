<template>
  <div class="container-fluid py-5">
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
              <input type="hidden" v-model="selectedCategory.id">
              <div class="form-group mb-3">
                <label for="editName">Name</label>
                <input type="text" v-model="selectedCategory.name" class="form-control" id="editName" required>
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
        <h6 class="m-0 fw-bold text-dark">Search Categories</h6>
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
        <h6 class="m-0 fw-bold text-dark">Categories</h6>
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
              <a class="page-link" href="#" @click.prevent="loadPage(currentPage - 1)">&lt;</a>
            </li>
            <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <a class="page-link" href="#" @click.prevent="loadPage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="loadPage(currentPage + 1)">&gt;</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import * as bootstrap from 'bootstrap';

export default {
  data() {
    return {
      title: 'Categories',
      categories: [],
      newCategory: { name: '', type: 'income' },
      selectedCategory: { id: '', name: '', type: 'income' },
      searchQuery: '',
      searchType: '',
      errorMessage: '',
      noCategoriesMessage: 'No categories found. Try adding a new category or adjusting your search.',
      currentPage: 1,
      totalPages: 1,
      perPage: 6
    };
  },
  async mounted() {
    await this.searchCategories(1);
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
        const modal = bootstrap.Modal.getInstance(this.$refs.addModal) || new bootstrap.Modal(this.$refs.addModal);
        modal.hide();
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
        const modal = bootstrap.Modal.getInstance(this.$refs.editModal) || new bootstrap.Modal(this.$refs.editModal);
        modal.hide();
        this.errorMessage = '';
      } catch (e) {
        console.error('Error closing edit modal:', e);
        this.errorMessage = 'Failed to close edit modal';
      }
    },
    async addCategory() {
    this.errorMessage = '';
    try {
        const response = await axios.post('/api/categories/create', {
            name: this.newCategory.name?.trim() || '',
            type: this.newCategory.type || 'income'
        }, {
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            withCredentials: true
        });
        console.log('Add category response:', response.data);
        if (response.data.status === 'success') {
            this.newCategory = { name: '', type: 'income' };
            this.closeAddModal();
            await this.searchCategories(1);
        } else {
            this.errorMessage = response.data.message || 'Failed to add category';
        }
    } catch (e) {
        console.error('Add category error:', e);
        this.errorMessage = e.response?.data?.message || 'Error adding category';
    }
    },


    async editCategory() {
    this.errorMessage = '';
    try {
        if (!this.selectedCategory.id) {
            this.errorMessage = 'No category selected';
            return;
        }
        const response = await axios.put(`/api/categories/edit/${this.selectedCategory.id}`, {
            name: this.selectedCategory.name?.trim() || '',
            type: this.selectedCategory.type || 'income'
        }, {
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            withCredentials: true
        });
        console.log('Edit category response:', response.data);
        if (response.data.status === 'success') {
            this.closeEditModal();
            await this.searchCategories(this.currentPage);
        } else {
            this.errorMessage = response.data.message || 'Failed to update category';
        }
    } catch (e) {
        console.error('Edit category error:', e);
        this.errorMessage = e.response?.data?.message || 'Error editing category';
    }
    },

    async deleteCategory(id) {
      this.errorMessage = '';
      try {
        const response = await axios.delete(`/api/categories/delete/${id}`, {
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          withCredentials: true
        });
        console.log('Delete category response:', response.data);
        if (response.data.status === 'success') {
          const newPage = this.currentPage > this.totalPages && this.totalPages > 1 ? this.currentPage - 1 : this.currentPage;
          await this.searchCategories(newPage || 1);
        } else {
          this.errorMessage = response.data.message || 'Failed to delete category';
        }
      } catch (e) {
        console.error('Delete category error:', e);
        this.errorMessage = e.response?.data?.message || 'Error deleting category';
      }
    },


    async searchCategories(page = 1) {
      this.errorMessage = '';
      try {
        const response = await axios.post('/api/categories/search', {
          search: this.searchQuery?.trim() || '',
          type: this.searchType || '',
          page,
          per_page: this.perPage
        }, {
          headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          withCredentials: true
        });
        console.log('Search response:', response.data);
        if (response.data.status === 'success') {
          this.categories = Array.isArray(response.data.categories) ? response.data.categories : [];
          this.currentPage = parseInt(response.data.current_page) || 1;
          this.totalPages = parseInt(response.data.total_pages) || 1;
          this.noCategoriesMessage = this.categories.length === 0
            ? 'No categories found. Try adding a new category or adjusting your search.'
            : '';
        } else {
          this.errorMessage = response.data.message || 'Search failed';
          this.categories = [];
          this.currentPage = 1;
          this.totalPages = 1;
          this.noCategoriesMessage = 'Search failed. Please try again.';
        }
      } catch (e) {
        console.error('Search categories error:', e);
        this.errorMessage = e.response?.data?.message || 'Error searching categories';
        this.categories = [];
        this.currentPage = 1;
        this.totalPages = 1;
        this.noCategoriesMessage = 'Error loading categories. Please check your connection.';
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
      await this.searchCategories(1);
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
.table {
  width: 100%;
  margin-bottom: 1rem;
}
.pagination .page-link {
  color: #007bff;
}
.pagination .page-item.active .page-link {
  background-color: #007bff;
  border-color: #007bff;
}
</style>