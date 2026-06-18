<?php
require_once 'auth.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    header("Location: index.php");
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } elseif (attempt_admin_login($username, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Christverse Admin - Login</title>
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
            --accent: #FFC107;
            --dark-bg: #0B0E14;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text-light: #F8F9FA;
            --text-muted: #8E9AA8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(15, 20, 31) 0%, rgb(8, 10, 15) 90%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Abstract glowing blobs for premium aesthetic */
        .glow-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            z-index: 1;
            opacity: 0.15;
        }
        .blob-1 {
            width: 300px;
            height: 300px;
            background: var(--primary);
            top: 10%;
            left: 15%;
        }
        .blob-2 {
            width: 400px;
            height: 400px;
            background: #E040FB;
            bottom: 15%;
            right: 15%;
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(18, 24, 38, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 20px 40px rgba(26, 115, 232, 0.15);
        }

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-container img {
            max-width: 180px;
            filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
        }

        .card-title {
            color: var(--text-light);
            font-family: 'Saira', sans-serif;
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 8px;
            text-align: center;
        }

        .card-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid var(--glass-border) !important;
            color: var(--text-light) !important;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 10px rgba(26, 115, 232, 0.3) !important;
        }

        .form-floating label {
            color: var(--text-muted);
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--primary);
            transform: scale(0.85) translateY(-0.5rem) translateX(0.15rem);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, #0d55b0 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s ease;
            margin-top: 10px;
            font-family: 'Saira', sans-serif;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #1f7eff 0%, #1565c0 100%);
            box-shadow: 0 5px 15px rgba(26, 115, 232, 0.4);
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b76;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            padding: 12px;
        }

        .back-to-site {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s ease;
        }

        .back-to-site:hover {
            color: var(--text-light);
        }

        .back-to-site i {
            margin-right: 5px;
        }
    </style>
</head>
<body>

    <!-- Glowing Background Elements -->
    <div class="glow-blob blob-1"></div>
    <div class="glow-blob blob-2"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-container">
                <img src="../img/logos/Christverse%20Horizontal%20White.png" alt="Christverse Logo" onerror="this.src='../img/logos/Christverse.png'">
            </div>
            
            <h2 class="card-title">Portal Access</h2>
            <p class="card-subtitle">Sign in to manage the Christverse dashboard</p>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-custom d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div><?php echo htmlspecialchars($error_message); ?></div>
                </div>
            <?php endif; ?>

            <form action="login" method="POST" autocomplete="off">
                <div class="form-floating">
                    <input type="text" name="username" class="form-control" id="username" placeholder="Username" required autofocus>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="btn btn-login w-100">
                    Authenticate <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </form>

            <a href="../" class="back-to-site">
                <i class="fas fa-arrow-left"></i> Return to main site
            </a>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
