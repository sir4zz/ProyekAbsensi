<?php
require_once '../config.php';
requireLogin('admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-yellow: #FFD700;
            --dark-yellow: #FFC700;
            --primary-black: #1a1a1a;
            --secondary-black: #2d2d2d;
            --sidebar-width: 260px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            padding: 0;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: rgba(255, 215, 0, 0.1);
            border-bottom: 2px solid var(--primary-yellow);
            text-align: center;
        }
        
        .sidebar-logo {
            width: 60px;
            height: 60px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .sidebar-logo i {
            font-size: 30px;
            color: var(--primary-black);
        }
        
        .sidebar-title {
            color: var(--primary-yellow);
            font-size: 1.1rem;
            font-weight: bold;
            margin: 0;
        }
        
        .sidebar-subtitle {
            color: #bbb;
            font-size: 0.85rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .menu-item:hover {
            background: rgba(255, 215, 0, 0.1);
            color: var(--primary-yellow);
            border-left-color: var(--primary-yellow);
        }
        
        .menu-item.active {
            background: rgba(255, 215, 0, 0.15);
            color: var(--primary-yellow);
            border-left-color: var(--primary-yellow);
            font-weight: 600;
        }
        
        .menu-item i {
            width: 25px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .menu-label {
            color: var(--primary-yellow);
            padding: 20px 20px 10px 20px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Topbar */
        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .topbar-left h4 {
            margin: 0;
            color: var(--primary-black);
            font-weight: 600;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-yellow);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--primary-black);
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--primary-black);
            font-size: 0.9rem;
            display: block;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: #666;
        }
        
        .btn-logout {
            background: var(--primary-yellow);
            color: var(--primary-black);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-logout:hover {
            background: var(--dark-yellow);
            color: var(--primary-black);
            transform: translateY(-2px);
        }
        
        /* Content Area */
        .content-area {
            padding: 30px;
        }
        
        /* Cards */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-yellow);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-yellow);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--primary-black);
            margin-bottom: 15px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .stat-value {
            color: var(--primary-black);
            font-size: 2rem;
            font-weight: bold;
        }
        
        /* Tables */
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-top: 30px;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary-yellow);
        }
        
        .table-header h5 {
            margin: 0;
            color: var(--primary-black);
            font-weight: 600;
        }
        
        .btn-primary {
            background: var(--primary-yellow);
            border: none;
            color: var(--primary-black);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--dark-yellow);
            color: var(--primary-black);
            transform: translateY(-2px);
        }
        
        .btn-success { background: #28a745; border: none; }
        .btn-danger { background: #dc3545; border: none; }
        .btn-warning { background: var(--primary-yellow); color: var(--primary-black); border: none; }
        .btn-info { background: #17a2b8; border: none; }
        
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                padding: 15px;
            }
            
            .content-area {
                padding: 15px;
            }
        }
        
        /* DataTables Custom */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px 12px;
        }
        
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 6px 12px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-school"></i>
            </div>
            <h5 class="sidebar-title">Admin Panel</h5>
            <p class="sidebar-subtitle">Sistem Absensi XYZ</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="menu-label">Manajemen Data</div>
            
            <a href="data_siswa.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'data_siswa.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Data Siswa</span>
            </a>
            
            <a href="data_guru.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'data_guru.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i>
                <span>Data Guru</span>
            </a>
            
            <div class="menu-label">Absensi</div>
            
            <a href="data_absensi.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'data_absensi.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Data Absensi</span>
            </a>
            
            <a href="delete_all_absensi.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'delete_all_absensi.php' ? 'active' : ''; ?>">
                <i class="fas fa-trash-alt"></i>
                <span>Reset Absensi</span>
            </a>
            
            <div class="menu-label">Sistem</div>
            
            <a href="backup.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i>
                <span>Backup Database</span>
            </a>
            
            <a href="logout.php" class="menu-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <h4><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h4>
            </div>
            <div class="topbar-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_nama'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo $_SESSION['admin_nama']; ?></span>
                        <span class="user-role">Administrator</span>
                    </div>
                </div>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">