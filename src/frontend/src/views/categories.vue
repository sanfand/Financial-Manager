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

    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark">Categories</h6>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="mb-3">
          <form @submit.prevent="searchCategories">
            <div class="row">
              <div class="col-md-4">
                <input type="text" v-model="searchQuery" class="form-control" placeholder="Search by name">
              </div>
              <div class="col-md-4">
                <select v-model="searchType" class="form-control">
                  <option value="">All Types</option>
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                <button type="button" class="btn btn-secondary btn-sm w-100 mt-1" @click="resetSearch">Reset</button>
              </div>
            </div>
          </form>
        </div>

        <div v-if="loading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>

        <div v-else>
          <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="category in categories" :key="category.id">
                  <td>{{ category.name }}</td>
                  <td>
                    <span :class="['badge', category.type === 'income' ? 'bg-success' : 'bg-danger']">
                      {{ category.type }}
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-warning btn-sm me-2" @click="openEditModal(category)" data-bs-toggle="modal" data-bs-target="#editCategoryModal">Edit</button>
                    <button class="btn btn-danger btn-sm" @click="deleteCategory(category.id)">Delete</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
              Showing page {{ currentPage }} of {{ totalPages }}
            </div>
            <nav>
              <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                  <button class="page-link" @click="changePage(currentPage - 1)">Previous</button>
                </li>
                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                  <button class="page-link" @click="changePage(page)">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                  <button class="page-link" @click="changePage(currentPage + 1)">Next</button>
                </li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '../utils/auth';
import { Modal } from 'bootstrap'

export default {
  name: 'Categories',
  data() {
    return {
      title: 'Categories',
      categories: [],
      loading: false,
      errorMessage: '',
      newCategory: {
        name: '',
        type: 'income'
      },
      selectedCategory: {
        id: '',
        name: '',
        type: 'income'
      },
      searchQuery: '',
      searchType: '',
      currentPage: 1,
      totalPages: 1,
      perPage: 6
    }
  },
  mounted() {
    this.loadCategories()
  },
  methods: {
    async loadCategories() {
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.get(`/categories?page=${this.currentPage}&per_page=${this.perPage}`)
        
        if (response.data.status === 'success') {
          this.categories = response.data.categories
          this.currentPage = response.data.current_page
          this.totalPages = response.data.total_pages
        } else {
          this.errorMessage = response.data.message || 'Failed to load categories'
        }
      } catch (error) {
        console.error('Error loading categories:', error)
        if (error.response?.status === 401) {
          this.$router.push('/login')
          return
        }
        this.errorMessage = 'Error loading categories'
      } finally {
        this.loading = false
      }
    },
    async addCategory() {
      this.errorMessage = ''
      
      try {
        const response = await axios.post('/categories/create', this.newCategory)
        
        if (response.data.status === 'success') {
          this.newCategory = { name: '', type: 'income' }
          const modal = Modal.getInstance(this.$refs.addModal)
          modal.hide()
          this.loadCategories()
        } else {
          this.errorMessage = response.data.message || 'Failed to add category'
        }
      } catch (error) {
        console.error('Error adding category:', error)
        this.errorMessage = 'Error adding category'
      }
    },
    openEditModal(category) {
      this.selectedCategory = { ...category }
    },
    async editCategory() {
      this.errorMessage = ''
      
      try {
        const response = await axios.post(`/categories/edit/${this.selectedCategory.id}`, this.selectedCategory)
        
        if (response.data.status === 'success') {
          const modal = Modal.getInstance(this.$refs.editModal)
          modal.hide()
          this.loadCategories()
        } else {
          this.errorMessage = response.data.message || 'Failed to update category'
        }
      } catch (error) {
        console.error('Error updating category:', error)
        this.errorMessage = 'Error updating category'
      }
    },
    async deleteCategory(id) {
      if (!confirm('Are you sure you want to delete this category?')) {
        return
      }

      try {
        const response = await axios.delete(`/categories/delete/${id}`)
        
        if (response.data.status === 'success') {
          this.loadCategories()
        } else {
          alert(response.data.message || 'Failed to delete category')
        }
      } catch (error) {
        console.error('Error deleting category:', error)
        alert('Error deleting category')
      }
    },
    async searchCategories() {
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.post('/categories/search', {
          search: this.searchQuery,
          type: this.searchType,
          page: this.currentPage,
          per_page: this.perPage
        })

        if (response.data.status === 'success') {
          this.categories = response.data.categories
          this.currentPage = response.data.current_page
          this.totalPages = response.data.total_pages
        } else {
          this.errorMessage = response.data.message || 'Search failed'
        }
      } catch (error) {
        console.error('Error searching categories:', error)
        this.errorMessage = 'Error searching categories'
      } finally {
        this.loading = false
      }
    },
    resetSearch() {
      this.searchQuery = ''
      this.searchType = ''
      this.currentPage = 1
      this.loadCategories()
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
        if (this.searchQuery || this.searchType) {
          this.searchCategories()
        } else {
          this.loadCategories()
        }
      }
    }
  }
}
</script>
<style scoped>


.btn {
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
}

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
  background: linear-gradient(135deg, #d36b6b, #f89595);
}

.btn-warning {
  background: linear-gradient(135deg, #f5b7fc, #cc9199);
}


.badge {
  border-radius: 8px;
  padding: 6px 12px;
  font-weight: 600;
  font-size: 0.75rem;
}

.bg-success {
  background: linear-gradient(135deg, #5e8f48, #4a7e6b) !important;
}

.bg-danger {
  background: linear-gradient(135deg, #642020, #693333) !important;
}



.pagination .page-link {
  border-radius: 8px;
  margin: 0 3px;
  border: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.pagination .page-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}

.page-item.active .page-link {
  background: linear-gradient(135deg, #667eea, #764ba2);
  border: none;
}
</style>