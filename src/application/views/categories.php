<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $title; ?></h1>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="csrf_name" value="<?= $this->security->get_csrf_token_name(); ?>">
                    <input type="hidden" id="csrf_hash" value="<?= $this->security->get_csrf_hash(); ?>">
                    <div class="form-group">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name">
                    </div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="addCategory()">Add Category</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_category_id">
                    <div class="form-group">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name">
                    </div>
                    <button class="btn btn-primary btn-sm mt-2" onclick="updateCategory()">Update Category</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Categories</h6>
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addCategoryModal">Add Category</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="categories-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category->name); ?></td>
                            <td>
                                <?php if ($category->user_id == $this->session->userdata('user_id')): ?>
                                    <button class="btn btn-sm btn-warning" onclick="editCategory(<?= $category->id ?>)">Edit</button>
                                    <?php if (!$category->used): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category->id ?>)">Delete</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav>
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($pagination['current'] <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?= $pagination['current'] - 1 ?>)">&laquo;</a>
                    </li>
                    <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                        <li class="page-item <?= ($i === $pagination['current']) ? 'active' : '' ?>">
                            <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?= $i ?>)"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($pagination['current'] >= $pagination['pages']) ? 'disabled' : '' ?>">
                        <a class="page-link" href="javascript:void(0);" onclick="loadPage(<?= $pagination['current'] + 1 ?>)">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
// CSRF helper
function getCsrf() {
    return {
        name: document.getElementById('csrf_name').value,
        hash: document.getElementById('csrf_hash').value
    };
}

// Add Category
function addCategory() {
    const name = document.getElementById('name').value.trim();
    if (!name) return alert('Enter category name');

    const csrf = getCsrf();
    fetch('<?= base_url("categories/create") ?>', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `${encodeURIComponent(csrf.name)}=${encodeURIComponent(csrf.hash)}&name=${encodeURIComponent(name)}`
    })
    .then(r=>r.json())
    .then(res=>{
        alert(res.message);
        if(res.status==='success') location.reload();
    });
}

// Edit Category
function editCategory(id) {
    fetch('<?= base_url("categories/get_category/") ?>'+id)
    .then(r=>r.json())
    .then(res=>{
        if(res.status==='error'){ alert(res.message); return; }
        document.getElementById('edit_category_id').value = res.id;
        document.getElementById('edit_name').value = res.name;
        // Show Bootstrap 4 modal
        $('#editCategoryModal').modal('show');
    });
}

// Update Category
function updateCategory() {
    const id = document.getElementById('edit_category_id').value;
    const name = document.getElementById('edit_name').value.trim();
    if (!name) return alert('Enter category name');

    const csrf = getCsrf();
    fetch('<?= base_url("categories/edit/") ?>'+id, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `${encodeURIComponent(csrf.name)}=${encodeURIComponent(csrf.hash)}&name=${encodeURIComponent(name)}`
    })
    .then(r=>r.json())
    .then(res=>{
        alert(res.message);
        if(res.status==='success') location.reload();
    });
}

// Delete Category
function deleteCategory(id) {
    if(!confirm('Are you sure?')) return;
    const csrf = getCsrf();
    fetch('<?= base_url("categories/delete/") ?>'+id, {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`${encodeURIComponent(csrf.name)}=${encodeURIComponent(csrf.hash)}`
    })
    .then(r=>r.json())
    .then(res=>{
        alert(res.message);
        if(res.status==='success') location.reload();
    });
}

// Pagination
function loadPage(page) {
    window.location.href = '<?= base_url("categories") ?>?page='+page;
}
</script>
