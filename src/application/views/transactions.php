<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>
<!-- for pagination-->
<style>
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-top: 20px;
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

<div class="container-fluid py-5" id="transactionsApp">
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
                            <input type="text" v-model="newTransaction.title" class="form-control" id="addTitle"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="addAmount">Amount</label>
                            <input type="number" v-model.number="newTransaction.amount" step="1" class="form-control"
                                id="addAmount" required>
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
                            <input type="date" v-model="newTransaction.occurred_at" class="form-control" id="addDate"
                                required>
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
                        <div class="form-group mb-3">
                            <label for="editTitle">Title</label>
                            <input type="text" v-model="selectedTransaction.title" class="form-control" id="editTitle"
                                required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="editAmount">Amount</label>
                            <input type="number" v-model.number="selectedTransaction.amount" step="0.01"
                                class="form-control" id="editAmount" required>
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
                            <input type="date" v-model="selectedTransaction.occurred_at" class="form-control"
                                id="editDate" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="editNotes">Note</label>
                            <textarea v-model="selectedTransaction.notes" class="form-control"
                                id="editNotes"></textarea>
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
            <h6 class="m-0 font-weight-bold text-dark">Search Transactions</h6>
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
            <button class="btn btn-primary btn-sm me-2" @click="searchTransactions">Search</button>
            <button class="btn btn-secondary btn-sm" @click="resetSearch">Reset</button>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-dark">Transactions</h6>
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
                        <td>{{ t.title }}</td>
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
                        <td colspan="6" class="text-center">No transactions found</td>
                    </tr>
                </tbody>
            </table>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo $links; ?>
                </div>



        </div>
    </div>
</div>

<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                title: '<?php echo $title; ?>',
                transactions: <?php echo json_encode($transactions); ?>,
                categories: <?php echo json_encode($categories); ?>,
                newTransaction: { title: '', amount: 0, type: 'income', category_id: '', occurred_at: '', notes: '' },
                selectedTransaction: {},
                searchQuery: '',
                searchType: '',
                searchCategory: '',
                startDate: '',
                endDate: '',
                errorMessage: ''
            };
        },
        methods: {
            capitalize(str) {
                return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
            },
            formatDate(date) {
                return date ? new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
            },
            formatAmount(amount) {
                const num = parseFloat(amount);
                return isNaN(num) ? '0.00' : num.toFixed(2);
            },
            openAddModal() {
                this.errorMessage = '';
                this.newTransaction = { title: '', amount: 0, type: 'income', category_id: '', occurred_at: '', notes: '' };
                const modal = new bootstrap.Modal(this.$refs.addModal);
                modal.show();
            },
            closeAddModal() {
                const modal = bootstrap.Modal.getInstance(this.$refs.addModal);
                if (modal) modal.hide();
                this.errorMessage = '';
            },
            openEditModal(t) {
                this.errorMessage = '';
                this.selectedTransaction = { ...t, category_id: t.category_id || '', amount: parseFloat(t.amount) || 0 };
                const modal = new bootstrap.Modal(this.$refs.editModal);
                modal.show();
            },
            closeEditModal() {
                const modal = bootstrap.Modal.getInstance(this.$refs.editModal);
                if (modal) modal.hide();
                this.errorMessage = '';
            },
            async addTransaction() {
                try {
                    const formData = new FormData();
                    for (let key in this.newTransaction) {
                        formData.append(key, this.newTransaction[key] || '');
                    }
                    const res = await axios.post(
                        "<?php echo base_url('transactions/create'); ?>",
                        formData
                    );

                    if (res.data.status === 'success') {
                        this.transactions.unshift({ ...res.data.transaction, amount: parseFloat(res.data.transaction.amount) });
                        this.closeAddModal();
                    } else {
                        this.errorMessage = res.data.message || 'Failed to add transaction';
                    }
                } catch (e) {
                    console.error('Add error:', e);
                    this.errorMessage = 'Error adding transaction';
                }
            },
            async editTransaction() {
                try {
                    const formData = new FormData();
                    for (let key in this.selectedTransaction) {
                        formData.append(key, this.selectedTransaction[key] || '');
                    }
                    const res = await axios.post('<?php echo base_url("transactions/edit"); ?>', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    });
                    if (res.data.status === 'success') {
                        const idx = this.transactions.findIndex(t => t.id === this.selectedTransaction.id);
                        this.transactions[idx] = { ...res.data.transaction, amount: parseFloat(res.data.transaction.amount) };
                        this.closeEditModal();
                    } else {
                        this.errorMessage = res.data.message || 'Failed to update transaction';
                    }
                } catch (e) {
                    console.error('Edit error:', e);
                    this.errorMessage = 'Error editing transaction';
                }
            },
            async deleteTransaction(id) {
                if (!confirm('Are you sure you want to delete this transaction?')) return;
                try {
                    const res = await axios.post('<?php echo base_url("transactions/delete"); ?>', { id }, {
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
                    });
                    if (res.data.status === 'success') {
                        this.transactions = this.transactions.filter(t => t.id !== id);
                    } else {
                        this.errorMessage = res.data.message || 'Failed to delete transaction';
                    }
                } catch (e) {
                    console.error('Delete error:', e);
                    this.errorMessage = 'Error deleting transaction';
                }
            },
            async searchTransactions() {
                try {
                    const filters = {
                        search: this.searchQuery,
                        type: this.searchType,
                        category_id: this.searchCategory,
                        start_date: this.startDate,
                        end_date: this.endDate
                    };
                    const res = await axios.post('<?php echo base_url("transactions/search"); ?>', filters, {
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.data.status === 'success') {
                        this.transactions = res.data.transactions.map(t => ({
                            ...t,
                            amount: parseFloat(t.amount)
                        }));
                        this.errorMessage = res.data.transactions.length === 0 ? 'No transactions found' : '';
                    } else {
                        this.errorMessage = res.data.message || 'Search failed';
                    }
                } catch (e) {
                    console.error('Search error:', e);
                    this.errorMessage = 'Error searching transactions';
                }
            },
            resetSearch() {
                this.searchQuery = '';
                this.searchType = '';
                this.searchCategory = '';
                this.startDate = '';
                this.endDate = '';
                this.errorMessage = '';
                this.searchTransactions();
            }
        },
        mounted() {
            if (!this.transactions || !this.categories) {
                this.errorMessage = 'Failed to load data';
            } else {
                // Ensure amounts are numbers on initial load
                this.transactions = this.transactions.map(t => ({
                    ...t,
                    amount: parseFloat(t.amount) || 0
                }));
            }
        }
    }).mount('#transactionsApp');
</script>

<?php $this->load->view('footer'); ?>