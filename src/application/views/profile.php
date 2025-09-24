<head>
    <style>
        body { background: linear-gradient(135deg,#f8f9fa,#e0f7fa); }
        .card {
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }
        .btn-cute-blue { background: #5dade2; color: #fff; border-radius: 50px; }
        .btn-cute-pink { background: #f78fb3; color: #fff; border-radius: 50px; }
        .btn-cute-green { background: #7bed9f; color: #fff; border-radius: 50px; }
        .btn-cute-blue:hover { background: #3498db; }
        .btn-cute-pink:hover { background: #ff6b81; }
        .btn-cute-green:hover { background: #2ecc71; }
        #profilePicPreview { box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1,h6 { font-family: 'Poppins', sans-serif; }
        p { font-size: 0.95rem; line-height: 1.5; }
        label { font-weight: 500; color: #555; }
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
        }
        .modal-header { border-bottom: none; }
        .modal-footer { border-top: none; justify-content: center; }
        .card-body { padding: 1.8rem; }
        .row > .col-md-4, .row > .col-md-8 { margin-bottom: 1.5rem; }
        .card:hover { transform: translateY(-5px); transition: 0.3s; }
        .d-grid.gap-2 {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
        }

        body, h1, h2, h3, h4, h5, h6, p, label, span {
            font-weight: 600 !important;
        }

        .btn-cute-blue, .btn-cute-pink, .btn-cute-green {
            padding: 10px 20px !important;
            font-weight: 600;
        }
    </style>
</head>
<div class="container py-5">
    <h1 class="h2 mb-4 text-gray-800"><?= $title; ?></h1>

    <div class="row">
        <div class="col-md-4 text-center">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <img src="<?= $user->profile_pic ? base_url('uploads/profile_pics/' . $user->profile_pic) : base_url('assets/default_profile.jpg'); ?>" 
                         id="profilePicPreview" class="img-fluid rounded-circle mb-3 border" style="max-width:180px;">
                    <div class="d-grid gap-2">
                        <button class="btn btn-cute-blue btn-sm" data-toggle="modal" data-target="#updateInfoModal">Update Info</button>
                        <button class="btn btn-cute-pink btn-sm" data-toggle="modal" data-target="#updatePasswordModal">Change Password</button>
                        <button class="btn btn-cute-green btn-sm" data-toggle="modal" data-target="#uploadPicModal">Upload Picture</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Current Info</h6>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> <span id="currentName"><?= $user->name; ?></span></p>
                    <p><strong>Email:</strong> <span id="currentEmail"><?= $user->email; ?></span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Info Modal -->
<div class="modal fade" id="updateInfoModal" tabindex="-1" role="dialog" aria-labelledby="updateInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #a8d8ea; color: #fff;">
                <h5 class="modal-title" id="updateInfoModalLabel">Update Info</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" id="nameInput" class="form-control" value="<?= $user->name; ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="emailInput" class="form-control" value="<?= $user->email; ?>">
                </div>
                <div id="infoAlert"></div>
            </div>
            <div class="modal-footer">
                <button id="updateInfoBtn" class="btn btn-cute-blue btn-sm">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="updatePasswordModal" tabindex="-1" role="dialog" aria-labelledby="updatePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #fff3cd; color: #333;">
                <h5 class="modal-title" id="updatePasswordModalLabel">Change Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" id="currentPasswordInput" class="form-control">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="newPasswordInput" class="form-control">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirmPasswordInput" class="form-control">
                </div>
                <div id="passAlert"></div>
            </div>
            <div class="modal-footer">
                <button id="updatePasswordBtn" class="btn btn-cute-pink btn-sm">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Picture Modal -->
<div class="modal fade" id="uploadPicModal" tabindex="-1" role="dialog" aria-labelledby="uploadPicModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #b5ead7; color: #333;">
                <h5 class="modal-title" id="uploadPicModalLabel">Upload Profile Picture</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <input type="file" id="profilePicInput" class="form-control mb-3">
                <div id="picAlert"></div>
            </div>
            <div class="modal-footer">
                <button id="uploadPicBtn" class="btn btn-cute-green btn-sm">Upload</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    function showAlert(container, message, type='success') {
        $(container).html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
    }

    $('#updateInfoBtn').click(function() {
        $.post('<?= base_url("profile/update"); ?>', {
            name: $('#nameInput').val(),
            email: $('#emailInput').val()
        }, function(response){
            let res = JSON.parse(response);
            showAlert('#infoAlert', res.message, res.status === 'success' ? 'success' : 'danger');
            if(res.status === 'success'){
                $('#currentName').text($('#nameInput').val());
                $('#currentEmail').text($('#emailInput').val());
                $('#updateInfoModal').modal('hide');
            }
        });
    });

    $('#updatePasswordBtn').click(function() {
        $.post('<?= base_url("profile/update_password"); ?>', {
            current_password: $('#currentPasswordInput').val(),
            new_password: $('#newPasswordInput').val(),
            confirm_password: $('#confirmPasswordInput').val()
        }, function(response){
            let res = JSON.parse(response);
            showAlert('#passAlert', res.message, res.status === 'success' ? 'success' : 'danger');
            if(res.status === 'success'){
                $('#currentPasswordInput,#newPasswordInput,#confirmPasswordInput').val('');
                $('#updatePasswordModal').modal('hide');
            }
        });
    });

    $('#uploadPicBtn').click(function() {
        let file = $('#profilePicInput')[0].files[0];
        if(!file){
            showAlert('#picAlert','Please select a picture','danger');
            return;
        }
        let formData = new FormData();
        formData.append('profile_pic',file);
        $.ajax({
            url: '<?= base_url("profile/upload_profile_pic"); ?>',
            type:'POST',
            data:formData,
            contentType:false,
            processData:false,
            cache:false,
            success:function(response){
                let res = JSON.parse(response);
                showAlert('#picAlert',res.message,res.status==='success'?'success':'danger');
                if(res.status==='success'){
                    $('#profilePicPreview').attr('src','<?= base_url("uploads/profile_pics/"); ?>'+res.filename);
                    $('#profilePicInput').val('');
                    $('#uploadPicModal').modal('hide');
                }
            }
        });
    });
});
</script>


