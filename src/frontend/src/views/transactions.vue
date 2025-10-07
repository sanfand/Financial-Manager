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
                <input type="number" v-model.number="newTransaction.amount" step="0.01" class="form-control" id="addAmount" required>
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
                  <option value="">None</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="addDate">Date</label>
                <input type="date" v-model="newTransaction.occurred_at" class="form-control" id="addDate" required>
              </div>
              <div class="form-group mb-3">
                <label for="addNotes">Notes</label>
                <textarea v-model="newTransaction.notes" class="form-control" id="addNotes"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Add Transaction</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true" ref="editModal">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
            <h5 class="modal-title">Edit Transaction</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <form @submit.prevent="editTransaction">
              <input type="hidden" v-model="selectedTransaction.id">
              <div class="form-group mb-3">
                <label for="editTitle">Title</label>
                <input type="text" v-model="selectedTransaction.title" class="form-control" id="editTitle" required>
              </div>
              <div class="form-group mb-3">
                <label for="editAmount">Amount</label>
                <input type="number" v-model.number="selectedTransaction.amount" step="0.01" class="form-control" id="editAmount" required>
              </div>
              <div class="form-group mb-3">
                <label for="editType">Type</label>
                <select v-model="selectedTransaction.type" class="form-control" id="editType" required>
                  <option value="income">Income</option>
                  <option value="expense">Expense</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="editCategory">Category</label>
                <select v-model="selectedTransaction.category_id" class="form-control" id="editCategory">
                  <option value="">None</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
              </div>
              <div class="form-group mb-3">
                <label for="editDate">Date</label>
                <input type="date" v-model="selectedTransaction.occurred_at" class="form-control" id="editDate" required>
              </div>
              <div class="form-group mb-3">
                <label for="editNotes">Notes</label>
                <textarea v-model="selectedTransaction.notes" class="form-control" id="editNotes"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-sm">Update Transaction</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-dark">Search Transactions</h6>
      </div>
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-md-3">
            <input type="text" v-model="searchQuery" class="form-control" placeholder="Search title">
          </div>
          <div class="col-md-2">
            <select v-model="searchType" class="form-control">
              <option value="">All Types</option>
              <option value="income">Income</option>
              <option value="expense">Expense</option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="searchCategory" class="form-control">
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" v-model="startDate" class="form-control" placeholder="Start Date">
          </div>
          <div class="col-md-2">
            <input type="date" v-model="endDate" class="form-control" placeholder="End Date">
          </div>
        </div>
        <button class="btn btn-primary btn-sm me-2" @click="searchTransactions(1)">Search</button>
        <button class="btn btn-secondary btn-sm" @click="resetSearch">Reset</button>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 fw-bold text-dark">Transactions</h6>
        <button class="btn btn-primary btn-sm" @click="openAddModal">Add Transaction</button>
      </div>
      <div class="card-body">
        <div v-if="errorMessage" class="alert alert-danger mb-3">{{ errorMessage }}</div>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Title</th>
              <th>Amount</th>
              <th>Type</th>
              <th>Category</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transactions" :key="t.id">
              <td>{{ t.title || 'N/A' }}</td>
              <td>$ {{ formatAmount(t.amount) }}</td>
              <td>{{ capitalize(t.type) }}</td>
              <td>{{ t.category_name || 'Uncategorized' }}</td>
              <td>{{ formatDate(t.occurred_at) }}</td>
              <td>
                <button class="btn btn-warning btn-sm me-1" @click="openEditModal(t)">Edit</button>
                <button class="btn btn-danger btn-sm" @click="deleteTransaction(t.id)">Delete</button>
              </td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="6" class="text-center">{{ noTransactionsMessage }}</td>
            </tr>
          </tbody>
        </table>
        <nav v-if="totalPages > 1" class="pagination-container">
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
      title: 'Transactions',
      transactions: [],
      categories: [],
      newTransaction: { title: '', amount: 0, type: 'income', category_id: '', occurred_at: '', notes: '' },
      selectedTransaction: { id: '', title: '', amount: 0, type: 'income', category_id: '', occurred_at: '', notes: '' },
      searchQuery: '',
      searchType: '',
      searchCategory: '',
      startDate: '',
      endDate: '',
      errorMessage: '',
      noTransactionsMessage: 'No transactions found. Try adding a new transaction or adjusting your search.',
      currentPage: 1,
      totalPages: 1,
      perPage: 5
    };
  },
  async mounted() {
    await this.loadPage(1);
  },
  methods: {
    capitalize(str) {
      return str ? str.charAt(0).toUpperCase() + str.slice(1) : 'N/A';
    },
    formatDate(date) {
      return date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
    },
    formatAmount(amount) {
      const num = parseFloat(amount);
      return isNaN(num) ? '0.00' : num.toFixed(2);
    },
    openAddModal() {
      this.errorMessage = '';
      this.newTransaction = { title: '', amount: 0, type: 'income', category_id: '', occurred_at: '', notes: '' };
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
    openEditModal(t) {
      this.errorMessage = '';
      this.selectedTransaction = { ...t, category_id: t.category_id || '', amount: parseFloat(t.amount) || 0 };
      try {
        const modal = new bootstrap.Modal(this.$refs.editModal);
        modal.show();
      } catch (e) {
        console.error('Error opening edit modal:', e);
        this.errorMessage = 'Failed to open edit modal';
      }
    },
    closeModal() {
        const modal = this.$refs.addModal;
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            bootstrapModal.hide();
        }
    },
    async addTransaction() {
    this.errorMessage = '';
    try {
        const response = await axios.post('/api/transactions/create', {
            title: this.newTransaction.title,
            amount: this.newTransaction.amount,
            type: this.newTransaction.type,
            category_id: this.newTransaction.category_id || null,
            occurred_at: this.newTransaction.occurred_at,
            notes: this.newTransaction.notes || ''
        }, {
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            withCredentials: true
        });
        if (response.data.status === 'success') {
            this.closeModal();
            this.newTransaction = { title: '', amount: '', type: 'income', category_id: '', occurred_at: '', notes: '' };
            await this.loadPage(this.currentPage);
        } else {
            this.errorMessage = response.data.message || 'Failed to add transaction';
        }
    } catch (e) {
        console.error('Add transaction error:', e);
        this.errorMessage = e.response?.data?.message || 'Error adding transaction';
    }
    },
    async editTransaction() {
    this.errorMessage = '';
    try {
        const response = await axios.put(`/api/transactions/edit/${this.selectedTransaction.id}`, {
            title: this.selectedTransaction.title,
            amount: this.selectedTransaction.amount,
            type: this.selectedTransaction.type,
            category_id: this.selectedTransaction.category_id || null,
            occurred_at: this.selectedTransaction.occurred_at,
            notes: this.selectedTransaction.notes || ''
        }, {
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            withCredentials: true
        });
        if (response.data.status === 'success') {
            const modal = this.$refs.editModal;
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
            this.selectedTransaction = {};
            await this.loadPage(this.currentPage);
        } else {
            this.errorMessage = response.data.message || 'Failed to edit transaction';
        }
    } catch (e) {
        console.error('Edit transaction error:', e);
        this.errorMessage = e.response?.data?.message || 'Error editing transaction';
    }
    },
    async deleteTransaction(id) {
    try {
        const response = await axios.delete(`/api/transactions/delete/${id}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (response.data.status === 'success') {
            await this.loadPage(this.currentPage);
        } else {
            this.errorMessage = response.data.message || 'Failed to delete transaction';
        }
    } catch (e) {
        console.error('Delete transaction error:', e);
        this.errorMessage = e.response?.data?.message || 'Error deleting transaction';
    }
    },
    async searchTransactions(page = 1) {
        this.errorMessage = '';
        try {
            const response = await axios.post('/api/transactions/search', {
                search: this.searchQuery?.trim() || '',
                type: this.searchType || '',
                category_id: this.searchCategory || '',
                start_date: this.startDate || '',
                end_date: this.endDate || '',
                page,
                per_page: this.perPage
            }, {
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest' 
                },
                withCredentials: true
            });
            console.log('Search transactions response:', response.data);
            if (response.data.status === 'success') {
                this.transactions = Array.isArray(response.data.transactions) ? response.data.transactions : [];
                this.currentPage = parseInt(response.data.current_page) || 1;
                this.totalPages = parseInt(response.data.total_pages) || 1;
                this.noTransactionsMessage = this.transactions.length === 0
                    ? 'No transactions found. Try adjusting your search.'
                    : '';
            } else {
                this.errorMessage = response.data.message || 'Search failed';
                this.transactions = [];
                this.currentPage = 1;
                this.totalPages = 1;
                this.noTransactionsMessage = 'Search failed. Please try again.';
            }
        } catch (e) {
            console.error('Search transactions error:', e);
            this.errorMessage = e.response?.data?.message || 'Error searching transactions';
            this.transactions = [];
            this.currentPage = 1;
            this.totalPages = 1;
            this.noTransactionsMessage = 'Error loading transactions. Please check your connection.';
        }
    },
    async loadPage(page) {
      if (page < 1 || (this.totalPages > 1 && page > this.totalPages)) return;
      if (this.searchQuery || this.searchType || this.searchCategory || this.startDate || this.endDate) {
        await this.searchTransactions(page);
      } else {
        try {
          const response = await axios.get(`/api/transactions/index?page=${page}&per_page=${this.perPage}`);
          if (response.data.status === 'success') {
            this.transactions = Array.isArray(response.data.transactions) ? response.data.transactions.map(t => ({
              ...t,
              amount: parseFloat(t.amount) || 0
            })) : [];
            this.categories = Array.isArray(response.data.categories) ? response.data.categories : [];
            this.currentPage = parseInt(response.data.current_page) || 1;
            this.totalPages = parseInt(response.data.total_pages) || 1;
            this.noTransactionsMessage = this.transactions.length === 0
              ? 'No transactions found. Try adding a new transaction.'
              : '';
          } else {
            this.errorMessage = response.data.message || 'Failed to load transactions';
            this.transactions = [];
            this.categories = [];
            this.currentPage = 1;
            this.totalPages = 1;
          }
        } catch (e) {
          console.error('Load transactions error:', e);
          this.errorMessage = e.response?.data?.message || 'Error loading transactions';
          this.transactions = [];
          this.categories = [];
          this.currentPage = 1;
          this.totalPages = 1;
        }
      }
    },
    async resetSearch() {
      this.searchQuery = '';
      this.searchType = '';
      this.searchCategory = '';
      this.startDate = '';
      this.endDate = '';
      this.errorMessage = '';
      this.currentPage = 1;
      await this.loadPage(1);
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
.pagination-container {
  margin-top: 20px;
}
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0;
  list-style: none;
  border-radius: 25px;
  background: linear-gradient(135deg, #f0f8ff, #e6f3ff);
  box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
  overflow: hidden;
}
.pagination li {
  margin: 0;
}
.pagination a,
.pagination li.active a {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 15px;
  margin: 0 2px;
  text-decoration: none;
  border: none;
  border-radius: 50%;
  font-weight: 500;
  transition: all 0.3s ease;
  min-width: 40px;
  height: 40px;
  color: #007bff;
  background: white;
}
.pagination a:hover {
  background-color: #007bff !important;
  color: white !important;
  transform: scale(1.05);
  box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
}
.pagination li.active a {
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  color: white !important;
  box-shadow: 0 2px 8px rgba(0, 123, 255, 0.4);
}
.pagination .disabled a {
  background-color: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
  opacity: 0.5;
}
</style>