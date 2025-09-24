<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Manager</title>
    <!-- Bootstrap 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- chart.js --> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .navbar {
            background: linear-gradient(90deg, #ee9bb7ff, #d8b9ff);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 0 0 10px 10px;
        }
        .navbar-brand {
            font-weight: 600;
            font-size: 1.8rem;
            color: #fff !important;
        }
        .navbar-nav .nav-link {
            color: #fff !important;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .navbar-nav .nav-link:hover {
            background-color: #fff3cd;
            color: #ff6f9c !important;
            transform: scale(1.1);
        }
        .navbar-toggler {
            border-color: rgba(255,255,255,0.8);
        }
        .navbar-toggler-icon {
            filter: invert(1);
        }
        @media (max-width: 991px) {
            .navbar-nav {
                background: linear-gradient(90deg, #f09ebaff, #d8b9ff);
                border-radius: 10px;
                padding: 1rem;
                margin-top: 0.5rem;
            }
        }
        .btn-primary {
            background-color: #a8d8ea !important;
            border-color: #a8d8ea !important;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #80c1e0 !important;
            transform: scale(1.05);
        }
        .btn-warning {
            background-color: #fff3cd !important;
            border-color: #fff3cd !important;
            color: #333 !important;
            transition: all 0.3s ease;
        }
        .btn-warning:hover {
            background-color: #ffe8a1 !important;
            transform: scale(1.05);
        }
        .btn-danger {
            background-color: #f7c5cc !important;
            border-color: #f7c5cc !important;
            color: #333 !important;
            transition: all 0.3s ease;
        }
        .btn-danger:hover {
            background-color: #f0a1b0 !important;
            transform: scale(1.05);
        }
        .btn-success {
            background-color: #b5ead7 !important;
            border-color: #b5ead7 !important;
            color: #333 !important;
            transition: all 0.3s ease;
        }
        .btn-success:hover {
            background-color: #90e0c5 !important;
            transform: scale(1.05);
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .modal-content {
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo base_url(); ?>">Financial Manager</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navmenu" aria-controls="navmenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navmenu">
                <ul class="navbar-nav ml-auto">
                    <?php if ($this->session->userdata('logged_in')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('dashboard'); ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('transactions'); ?>">Transactions</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('categories'); ?>">Categories</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('profile'); ?>">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('auth/logout'); ?>">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('auth/login'); ?>">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo base_url('auth/register'); ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid py-4">