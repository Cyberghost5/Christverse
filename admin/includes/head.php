<?php
require_once __DIR__ . '/../auth.php';
require_admin_login();
require_once __DIR__ . '/../../includes/db.php';

// Active page helper
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Christverse Admin Dashboard</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Saira:wght@500;600;700&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #1A73E8;
            --primary-glow: rgba(26, 115, 232, 0.15);
            --accent: #FFC107;
            --sidebar-bg: #0F141F;
            --sidebar-active: rgba(255, 255, 255, 0.06);
            --content-bg: #F4F6FA;
            --card-border: rgba(0, 0, 0, 0.05);
            --text-dark: #2B303A;
            --text-muted: #6C757D;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--content-bg);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            height: 100vh;
            overflow-y: auto;
            width: 260px;
            background-color: var(--sidebar-bg);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: all 0.3s ease;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }

        .sidebar-brand {
            padding: 24px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background-color: rgba(0, 0, 0, 0.15);
        }

        .sidebar-brand img {
            max-height: 40px;
            width: auto;
        }

        .sidebar-heading {
            color: rgba(255,255,255,0.4);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 20px 24px 10px 24px;
            font-weight: 600;
        }

        .list-group-item-sidebar {
            background-color: transparent;
            color: rgba(255, 255, 255, 0.65);
            border: none;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            font-size: 0.92rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .list-group-item-sidebar i {
            font-size: 1.1rem;
            width: 25px;
            margin-right: 12px;
            transition: transform 0.3s ease;
        }

        .list-group-item-sidebar:hover {
            color: #fff;
            background-color: var(--sidebar-active);
        }

        .list-group-item-sidebar:hover i {
            transform: scale(1.1);
        }

        .list-group-item-sidebar.active {
            color: #fff;
            background-color: var(--sidebar-active);
            font-weight: 600;
        }

        .list-group-item-sidebar.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background-color: var(--primary);
        }

        /* Main Content Wrapper */
        #page-content-wrapper {
            margin-left: 260px;
            min-height: 100vh;
            transition: all 0.3s ease;
            padding-bottom: 40px;
            flex-grow: 1;
            width: calc(100% - 260px);
        }

        /* Top Navbar */
        .admin-navbar {
            background-color: #fff;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 30px;
        }

        .breadcrumb-item a {
            text-decoration: none;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: var(--primary);
        }

        /* Stats Cards */
        .stat-card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 15px;
            padding: 24px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            transform: scale(1.1);
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 5px;
            font-family: 'Saira', sans-serif;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.88rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Card components styling */
        .admin-card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.01);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .admin-card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 20px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-card-title {
            font-family: 'Saira', sans-serif;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
            font-size: 1.15rem;
        }

        .admin-card-body {
            padding: 25px;
        }

        /* Tables */
        .table-custom {
            margin: 0;
        }
        
        .table-custom th {
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            padding: 15px 20px;
            border-bottom-width: 1px;
            background-color: #F8F9FA;
        }

        .table-custom td {
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 0.9rem;
            color: var(--text-dark);
            border-bottom-color: rgba(0,0,0,0.03);
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            border: none;
        }

        /* Custom Badges */
        .badge-dept {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Responsive media queries */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                left: -260px;
            }
            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            #wrapper.toggled #sidebar-wrapper {
                left: 0;
            }
            /* Visual overlay to dim background on mobile sidebar active */
            #wrapper.toggled #page-content-wrapper::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(15, 20, 31, 0.5);
                z-index: 99;
                transition: all 0.3s ease;
            }
        }
    </style>
</head>
<body>

    <div class="d-flex" id="wrapper">
