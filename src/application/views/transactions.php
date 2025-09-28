<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php $this->load->view('header'); ?>

<div class="container-fluid py-5" id="transactionsApp">
    <h1 class="h2 mb-4 text-gray-800">{{ title }}</h1>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true" ref="addModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:#a8d8ea;color:#fff;">
                    <h5 class="modal-title">Add Transaction</h5>
                    <button type="button" class="btn-close" @click="closeAddModal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="addTransaction">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" v-model="newTransaction.title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" v-model.number="newTransaction.amount" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select v-model="newTransaction.type" class="form-control" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select v-model="newTransaction.category_id" class="form-control">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" v-model="newTransaction.occurred_at" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea v-model="newTransaction.notes" class="form-control"></textarea>
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
                    <button type="button" class="btn-close" @click="closeEditModal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="editTransaction">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" v-model="selectedTransaction.title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" v-model.number="selectedTransaction.amount" step="0.01" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select v-model="selectedTransaction.type" class="form-control" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select v-model="selectedTransaction.category_id" class="form-control">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" v-model="selectedTransaction.occurred_at" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea v-model="selectedTransaction.notes" class="form-control"></textarea>
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
            <div class="row g-2 mb-2">
                <div class="col-md-3"><input type="text" v-model="searchQuery" class="form-control" placeholder="Search title"></div>
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
                <div class="col-md-2"><input type="date" v-model="startDate" class="form-control"></div>
                <div class="col-md-2"><input type="date" v-model="endDate" class="form-control"></div>
            </div>
            <button class="btn btn-primary btn-sm mr-2" @click="searchTransactions">Search</button>
            <button class="btn btn-secondary btn-sm" @click="resetSearch">Reset</button>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Transactions</h6>
            <button class="btn btn-primary btn-sm float-right" @click="openAddModal">Add Transaction</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title</th><th>Amount</th><th>Type</th><th>Category</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in filteredTransactions" :key="t.id">
                        <td>{{ t.title }}</td>
                        <td>$ {{ t.amount.toFixed(2) }}</td>
                        <td>{{ capitalize(t.type) }}</td>
                        <td>{{ t.category_name || 'Uncategorized' }}</td>
                        <td>{{ t.occurred_at }}</td>
                        <td>
                            <button class="btn btn-warning btn-sm" @click="openEditModal(t)">Edit</button>
                            <button class="btn btn-danger btn-sm" @click="deleteTransaction(t.id)">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="filteredTransactions.length===0">
                        <td colspan="6" class="text-center">No transactions found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const { createApp, ref } = Vue;

createApp({
    data() {
        return {
            title: '<?php echo $title; ?>',
            transactions: <?php echo json_encode($transactions); ?>,
            categories: <?php echo json_encode($categories); ?>,
            newTransaction: { title:'', amount:0, type:'income', category_id:'', occurred_at:'', notes:'' },
            selectedTransaction: {},
            searchQuery: '',
            searchType: '',
            searchCategory: '',
            startDate: '',
            endDate: ''
        }
    },
    computed: {
        filteredTransactions() {
            return this.transactions.filter(t => {
                const titleMatch = t.title.toLowerCase().includes(this.searchQuery.toLowerCase());
                const typeMatch = !this.searchType || t.type===this.searchType;
                const categoryMatch = !this.searchCategory || t.category_id==this.searchCategory;
                const dateMatch = (!this.startDate || t.occurred_at>=this.startDate) && (!this.endDate || t.occurred_at<=this.endDate);
                return titleMatch && typeMatch && categoryMatch && dateMatch;
            });
        }
    },
    methods: {
        capitalize(str){ return str.charAt(0).toUpperCase() + str.slice(1); },
        openAddModal(){ new bootstrap.Modal(this.$refs.addModal).show(); },
        closeAddModal(){ new bootstrap.Modal(this.$refs.addModal).hide(); },
        openEditModal(t){ this.selectedTransaction = {...t}; new bootstrap.Modal(this.$refs.editModal).show(); },
        closeEditModal(){ new bootstrap.Modal(this.$refs.editModal).hide(); },
        async addTransaction(){
            try {
                const formData = new FormData();
                for (let key in this.newTransaction) formData.append(key, this.newTransaction[key]);
                const res = await axios.post('<?php echo base_url("transactions/add"); ?>', formData);
                if(res.data.status==='success'){
                    this.transactions.push(res.data.transaction);
                    this.newTransaction={ title:'', amount:0, type:'income', category_id:'', occurred_at:'', notes:'' };
                    this.closeAddModal();
                } else { alert(res.data.message); }
            } catch(e){ console.error(e); alert('Error adding transaction'); }
        },
        async editTransaction(){
            try {
                const formData = new FormData();
                for (let key in this.selectedTransaction) formData.append(key, this.selectedTransaction[key]);
                const res = await axios.post('<?php echo base_url("transactions/edit"); ?>', formData);
                if(res.data.status==='success'){
                    const idx = this.transactions.findIndex(t=>t.id===this.selectedTransaction.id);
                    this.transactions[idx] = res.data.transaction;
                    this.closeEditModal();
                } else { alert(res.data.message); }
            } catch(e){ console.error(e); alert('Error editing transaction'); }
        },
        async deleteTransaction(id){
            if(!confirm('Are you sure?')) return;
            try{
                const res = await axios.post('<?php echo base_url("transactions/delete"); ?>',{id});
                if(res.data.status==='success') this.transactions = this.transactions.filter(t=>t.id!==id);
            }catch(e){ console.error(e); alert('Error deleting transaction'); }
        },
        searchTransactions(){ /* computed handles filtering */ },
        resetSearch(){ this.searchQuery=''; this.searchType=''; this.searchCategory=''; this.startDate=''; this.endDate=''; }
    }
}).mount('#transactionsApp');
</script>

<?php $this->load->view('footer'); ?>
