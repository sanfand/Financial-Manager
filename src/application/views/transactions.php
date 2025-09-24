<div class="container-fluid">
    <h1 class="h2 mb-4 text-gray-800"><?= $title; ?></h1>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #a8d8ea; color: #fff;">
                    <h5 class="modal-title" id="addTransactionModalLabel">Add Transaction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="csrf_name" value="<?= $this->security->get_csrf_token_name(); ?>">
                    <input type="hidden" id="csrf_hash" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" id="title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" id="amount" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select id="type" class="form-control">
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="category_id" class="form-control">
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c->id ?>"><?= $c->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="occurred_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="notes" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="addTransaction()">Add Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #a8d8ea; color: #fff;">
                    <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_transaction_id">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" id="edit_title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Amount</label>
                        <input type="number" step="0.01" id="edit_amount" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select id="edit_type" class="form-control">
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select id="edit_category_id" class="form-control">
                            <?php foreach($categories as $c): ?>
                                <option value="<?= $c->id ?>"><?= $c->name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" id="edit_occurred_at" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea id="edit_notes" class="form-control"></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="updateTransaction()">Update Transaction</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Form & Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Search Transactions</h6>
        </div>
        <div class="card-body">
            <div class="row g-2 mb-2">
                <div class="col-md-3"><input type="text" id="search" class="form-control" placeholder="Search title"></div>
                <div class="col-md-2">
                    <select id="search_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="search_category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c->id ?>"><?= $c->name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><input type="date" id="start_date" class="form-control"></div>
                <div class="col-md-2"><input type="date" id="end_date" class="form-control"></div>
            </div>
            <button class="btn btn-primary btn-sm mr-2" onclick="searchTransactions()">Search</button>
            <button class="btn btn-secondary btn-sm" onclick="resetSearch()">Reset</button>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-dark">Transactions</h6>
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addTransactionModal">Add Transaction</button>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped" id="transactions-table">
                <thead>
                    <tr>
                        <th>Title</th><th>Amount</th><th>Type</th><th>Category</th><th>Date</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $t): ?>
                        <tr data-id="<?= $t->id ?>">
                            <td><?= htmlspecialchars($t->title) ?></td>
                            <td>$<?= number_format($t->amount,2) ?></td>
                            <td><?= ucfirst($t->type) ?></td>
                            <td><?= htmlspecialchars($t->category_name ?: 'Uncategorized') ?></td>
                            <td><?= date('Y-m-d', strtotime($t->occurred_at)) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editTransaction(<?= $t->id ?>)">Edit</button>
                                <button class="btn btn-danger btn-sm" onclick="deleteTransaction(<?= $t->id ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>   
            </table>
            <!-- pagination--> 
            <div class="mt-3">
            <ul class="pagination justify-content-center">    
            <?= $links ?>
            </ul>
            </div>

        </div>
    </div>
</div>

<script>
function getCsrf() {
    return {
        name: $('#csrf_name').val(),
        hash: $('#csrf_hash').val()
    };
}

function refreshCsrf(token){
    $('#csrf_hash').val(token);
}

function addTransaction(){
    let csrf = getCsrf();
    $.post('<?= base_url("transactions/create") ?>', {
        [csrf.name]: csrf.hash,
        title: $('#title').val(),
        amount: $('#amount').val(),
        type: $('#type').val(),
        category_id: $('#category_id').val(),
        occurred_at: $('#occurred_at').val(),
        notes: $('#notes').val()
    }, function(res){
        if(res.status==='success'){
            alert(res.message);
            $('#addTransactionModal').modal('hide');
            location.reload();
        }else alert(res.message);
    }, 'json');
}

function editTransaction(id){
    $.get('<?= base_url("transactions/get_transaction/") ?>'+id, function(res){
        if(res.status==='error'){ alert(res.message); return; }
        $('#edit_transaction_id').val(res.id);
        $('#edit_title').val(res.title);
        $('#edit_amount').val(res.amount);
        $('#edit_type').val(res.type);
        $('#edit_category_id').val(res.category_id);
        $('#edit_occurred_at').val(res.occurred_at.split(' ')[0]);
        $('#edit_notes').val(res.notes);
        $('#editTransactionModal').modal('show');
    }, 'json');
}

function updateTransaction(){
    let id = $('#edit_transaction_id').val();
    let csrf = getCsrf();
    $.post('<?= base_url("transactions/edit/") ?>'+id, {
        [csrf.name]: csrf.hash,
        title: $('#edit_title').val(),
        amount: $('#edit_amount').val(),
        type: $('#edit_type').val(),
        category_id: $('#edit_category_id').val(),
        occurred_at: $('#edit_occurred_at').val(),
        notes: $('#edit_notes').val()
    }, function(res){
        if(res.status==='success'){
            alert(res.message);
            $('#editTransactionModal').modal('hide');
            location.reload();
        }else alert(res.message);
    }, 'json');
}

function deleteTransaction(id){
    if(!confirm('Are you sure?')) return;
    let csrf = getCsrf();
    $.post('<?= base_url("transactions/delete/") ?>'+id, {[csrf.name]: csrf.hash}, function(res){
        if(res.status==='success'){ alert(res.message); location.reload(); }
        else alert(res.message);
    }, 'json');
}

function searchTransactions(){
    $.get('<?= base_url("transactions/search") ?>', {
        search: $('#search').val(),
        type: $('#search_type').val(),
        category_id: $('#search_category').val(),
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val()
    }, function(res){
        if(res.status==='success'){
            let tbody = $('#transactions-table tbody'); tbody.empty();
            if(!res.data.length){ tbody.append('<tr><td colspan="6" class="text-center">No transactions found</td></tr>'); return; }
            res.data.forEach(t=>{
                let d = t.occurred_at? new Date(t.occurred_at).toISOString().split('T')[0]:'';
                tbody.append(`<tr data-id="${t.id}"><td>${t.title}</td><td>$${parseFloat(t.amount).toFixed(2)}</td><td>${t.type.charAt(0).toUpperCase()+t.type.slice(1)}</td><td>${t.category_name||'Uncategorized'}</td><td>${d}</td><td><button class="btn btn-sm btn-warning" onclick="editTransaction(${t.id})">Edit</button> <button class="btn btn-sm btn-danger" onclick="deleteTransaction(${t.id})">Delete</button></td></tr>`);
            });
        }else alert(res.message);
    }, 'json');
}

function resetSearch(){ $('#search').val(''); $('#search_type').val(''); $('#search_category').val(''); $('#start_date').val(''); $('#end_date').val(''); searchTransactions(); }
</script>