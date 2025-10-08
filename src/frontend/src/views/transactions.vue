<template>
  <div class="container-fluid py-5">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true" ref="addModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
            <h5 class="modal-title">Add Transaction</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <form @submit.prevent="addTransaction">
              <div class="form-group mb-3">
                <label for="addTitle">Title</label>
                <input type="text" v-model="newTransaction.title" class="form-control" id="addTitle" required>
              </div>
              <div class="form-group mb-3">
                <label for="addAmount">Amount</label>
                <input type="number" step="0.01" v-model="newTransaction.amount" class="form-control" id="addAmount" required>
              </div>
              <div class="form-group mb-3">
                <label for="addType">Type</label>
                <select v-model="newTransaction.type" class="form-control" id="addType" required>
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="addCategory">Category</label>
                <select v-model="newTransaction.category_id" class="form-control" id="addCategory">
                  <option value="">No Category</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }} ({{ category.type }})
                  </option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="addDate">Date</label>
                <input type="datetime-local" v-model="newTransaction.occurred_at" class="form-control" id="addDate">
              </div>
              <div class="form-group mb-3">
                <label for="addNotes">Notes</label>
                <textarea v-model="newTransaction.notes" class="form-control" id="addNotes" rows="3"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Add Transaction</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark">Transactions</h6>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">Add Transaction</button>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="mb-3">
          <form @submit.prevent="searchTransactions">
            <div class="row">
              <div class="col-md-3">
                <input type="text" v-model="searchQuery" class="form-control" placeholder="Search by title">
              </div>
              <div class="col-md-2">
                <select v-model="searchType" class="form-control">
                  <option value="">All Types</option>
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
              <div class="col-md-2">
                <select v-model="searchCategory" class="form-control">
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category.id" :value="category.id">
                    {{ category.name }}
                  </option>
                </select>
              </div>
              <div class="col-md-2">
                <input type="date" v-model="startDate" class="form-control" placeholder="Start Date">
              </div>
              <div class="col-md-2">
                <input type="date" v-model="endDate" class="form-control" placeholder="End Date">
              </div>
              <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-md-12">
                <button type="button" class="btn btn-secondary btn-sm" @click="resetSearch">Reset</button>
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
                  <th>Title</th>
                  <th>Amount</th>
                  <th>Type</th>
                  <th>Category</th>
                  <th>Date</th>
                  <th>Notes</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="transaction in transactions" :key="transaction.id">
                  <td>{{ transaction.title }}</td>
                  <td :class="transaction.type === 'income' ? 'text-success' : 'text-danger'">
                    {{ transaction.type === 'income' ? '+' : '-' }}${{ transaction.amount }}
                  </td>
                  <td>
                    <span :class="['badge', transaction.type === 'income' ? 'bg-success' : 'bg-danger']">
                      {{ transaction.type }}
                    </span>
                  </td>
                  <td>{{ transaction.category_name || 'No Category' }}</td>
                  <td>{{ formatDate(transaction.occurred_at) }}</td>
                  <td>{{ transaction.notes || '-' }}</td>
                  <td>
                    <button class="btn btn-danger btn-sm" @click="deleteTransaction(transaction.id)">Delete</button>
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
  name: 'Transactions',
  data() {
    return {
      title: 'Transactions',
      transactions: [],
      categories: [],
      loading: false,
      errorMessage: '',
      newTransaction: {
        title: '',
        amount: 0,
        type: 'income',
        category_id: '',
        occurred_at: '',
        notes: ''
      },
      searchQuery: '',
      searchType: '',
      searchCategory: '',
      startDate: '',
      endDate: '',
      currentPage: 1,
      totalPages: 1,
      perPage: 10
    }
  },
  mounted() {
    this.loadTransactions()
    this.loadCategories()
  },
  methods: {
    async loadTransactions() {
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.get(`/transactions?page=${this.currentPage}&per_page=${this.perPage}`,{
          headers: {
            Authorization: `Bearer 5282f7ba9dd2bcbea9071bc0f7ac3b6e`
          }
        })

        if (response.data.status === 'success') {
          this.transactions = response.data.transactions
          this.categories = response.data.categories || []
          this.currentPage = response.data.current_page
          this.totalPages = response.data.total_pages
        } else {
          this.errorMessage = response.data.message || 'Failed to load transactions'
        }
      } catch (error) {
        console.error('Error loading transactions:', error)
        if (error.response?.status === 401) {
          this.$router.push('/login')
          return
        }
        this.errorMessage = 'Error loading transactions'
      } finally {
        this.loading = false
      }
    },
    async loadCategories() {
      try {
        const response = await axios.get('/categories/get_categories_list')
        if (response.data.status === 'success') {
          this.categories = response.data.categories
        }
      } catch (error) {
        console.error('Error loading categories:', error)
      }
    },
    async addTransaction() {
      this.errorMessage = ''
      
      try {
        const transactionData = {
          ...this.newTransaction,
          occurred_at: this.newTransaction.occurred_at || new Date().toISOString().slice(0, 16)
        }

        const response = await axios.post('/transactions/create', transactionData)
        
        if (response.data.status === 'success') {
          this.newTransaction = {
            title: '',
            amount: 0,
            type: 'income',
            category_id: '',
            occurred_at: '',
            notes: ''
          }
          const modal = Modal.getInstance(this.$refs.addModal)
          modal.hide()
          this.loadTransactions()
        } else {
          this.errorMessage = response.data.message || 'Failed to add transaction'
        }
      } catch (error) {
        console.error('Error adding transaction:', error)
        this.errorMessage = 'Error adding transaction'
      }
    },
    async deleteTransaction(id) {
      if (!confirm('Are you sure you want to delete this transaction?')) {
        return
      }

      try {
        const response = await axios.delete(`/transactions/delete/${id}`)
        
        if (response.data.status === 'success') {
          this.loadTransactions()
        } else {
          alert(response.data.message || 'Failed to delete transaction')
        }
      } catch (error) {
        console.error('Error deleting transaction:', error)
        alert('Error deleting transaction')
      }
    },
    async searchTransactions() {
      this.loading = true
      this.errorMessage = ''
      
      try {
        const response = await axios.post('/transactions/search', {
          search: this.searchQuery,
          type: this.searchType,
          category_id: this.searchCategory,
          start_date: this.startDate,
          end_date: this.endDate,
          page: this.currentPage,
          per_page: this.perPage
        })
        
        if (response.data.status === 'success') {
          this.transactions = response.data.transactions
          this.currentPage = response.data.current_page
          this.totalPages = response.data.total_pages
        } else {
          this.errorMessage = response.data.message || 'Search failed'
        }
      } catch (error) {
        console.error('Error searching transactions:', error)
        this.errorMessage = 'Error searching transactions'
      } finally {
        this.loading = false
      }
    },
    resetSearch() {
      this.searchQuery = ''
      this.searchType = ''
      this.searchCategory = ''
      this.startDate = ''
      this.endDate = ''
      this.currentPage = 1
      this.loadTransactions()
    },
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
        if (this.searchQuery || this.searchType || this.searchCategory || this.startDate || this.endDate) {
          this.searchTransactions()
        } else {
          this.loadTransactions()
        }
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString()
    }
  }
}
</script>