<?php
session_start();
require 'db_connect.php';

// If the user is already logged in, redirect them to the store
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$login_error = '';
$register_error = '';
$register_success = '';

// Determine which tab should be active on page load
$active_tab = 'login'; 

// ==========================================
// ENTERPRISE CONCEPT: SIMULATED LDAP LOOKUP
// ==========================================
// In a true enterprise application, this function would leverage php-ldap 
// functions like ldap_connect(), ldap_bind(), and ldap_search() to authenticate against Active Directory.
function authenticate_via_ldap($email, $password) {
    // Simulated Centralized Corporate Enterprise Directory Service
    $ldap_directory = [
        'admin@lisper.com' => [
            'id' => 1,
            'name' => 'Admin User', 
            'role' => 'admin', 
            'password_hash' => '$2y$10$YVnlcvQfCWc4TeFwBvCUF.7GTfuNeJeMXvbMPtgRZEyegIl/ja2Va'
        ],
        'john@example.com' => [
            'id' => 2,
            'name' => 'John Doe', 
            'role' => 'customer', 
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
        ],
        'wantam@example.com' => [
            'id' => 4,
            'name' => 'Kenga Amos', 
            'role' => 'customer', 
            'password_hash' => '$2y$10$YVnlcvQfCWc4TeFwBvCUF.7GTfuNeJeMXvbMPtgRZEyegIl/ja2Va'
        ]
    ];

    // Check if the identity exists inside the directory cluster
    if (array_key_exists($email, $ldap_directory)) {
        // Validate credentials against the corporate directory hash record
        if (password_verify($password, $ldap_directory[$email]['password_hash'])) {
            return $ldap_directory[$email]; // Return authenticated directory token data
        }
    }
    return false; // Identity validation failed
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // 1. UPDATED LOGIN LOGIC (LDAP SIMULATION)
    // ==========================================
    if (isset($_POST['action_login'])) {
        $active_tab = 'login'; 
        
        $email = $_POST['login_email'];
        $password = $_POST['login_password'];

        // Fulfill Week 13: Separate user authentication from the local application schema
        $ldap_user = authenticate_via_ldap($email, $password);

        if ($ldap_user) {
            // Establish tracking boundaries using directory server claims
            $_SESSION['user_id'] = $ldap_user['id'];
            $_SESSION['user_name'] = $ldap_user['name'];
            $_SESSION['user_role'] = $ldap_user['role'];

            if ($ldap_user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $login_error = "Invalid enterprise directory credentials.";
        }
    }

    // ==========================================
    // 2. REGISTRATION LOGIC
    // ==========================================
    if (isset($_POST['action_register'])) {
        $name = $_POST['reg_name'];
        $email = $_POST['reg_email'];
        $password = password_hash($_POST['reg_password'], PASSWORD_DEFAULT); 

        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        
        if ($check_stmt->rowCount() > 0) {
            $register_error = "An account with that email already exists.";
            $active_tab = 'register'; 
        } else {
            $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($insert_stmt->execute([$name, $email, $password])) {
                $register_success = "Registration successful! You can now log in.";
                $active_tab = 'login'; 
            } else {
                $register_error = "Something went wrong. Please try again.";
                $active_tab = 'register';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - Chum & Lisper Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-slate-100 flex flex-col items-center justify-center min-h-screen py-10 px-4" 
      style="background-image: radial-gradient(#e2e8f0 1px, transparent 1px); background-size: 20px 20px;">

    <a href="index.php" class="text-3xl font-extrabold text-indigo-600 mb-8 hover:text-indigo-700 transition drop-shadow-sm">
        Chum & Lisper
    </a>

    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 w-full max-w-md overflow-hidden relative z-10">
        
        <div class="flex border-b border-slate-200">
            <button onclick="switchTab('login')" id="btn-login" class="flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none <?= $active_tab === 'login' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-slate-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' ?>">
                Log In
            </button>
            <button onclick="switchTab('register')" id="btn-register" class="flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none <?= $active_tab === 'register' ? 'text-indigo-600 border-b-2 border-indigo-600 bg-slate-50/50' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' ?>">
                Sign Up
            </button>
        </div>

        <div class="p-8">
            
            <div id="tab-login" class="fade-in <?= $active_tab === 'login' ? 'block' : 'hidden' ?>">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Enterprise Log In</h2>
                    <p class="text-slate-500 text-sm mt-1">Access using your central directory account.</p>
                </div>
                
                <?php if ($login_error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded mb-6 text-sm font-medium"><?= $login_error ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-3 rounded mb-6 text-sm font-medium">✓ <?= $register_success ?></div>
                <?php endif; ?>

                <form action="auth.php" method="POST" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Corporate Email</label>
                        <input type="email" name="login_email" required class="w-full border border-slate-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-slate-50 focus:bg-white placeholder-slate-400" placeholder="admin@lisper.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input type="password" name="login_password" required class="w-full border border-slate-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-slate-50 focus:bg-white placeholder-slate-400" placeholder="••••••••">
                    </div>
                    <button type="submit" name="action_login" class="w-full bg-slate-900 text-white font-bold py-3 rounded-lg hover:bg-indigo-600 hover:shadow-lg transform transition-all duration-200 mt-2">
                        Directory Verification
                    </button>
                </form>
            </div>

            <div id="tab-register" class="fade-in <?= $active_tab === 'register' ? 'block' : 'hidden' ?>">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Create an Account</h2>
                    <p class="text-slate-500 text-sm mt-1">Join Chum & Lisper today.</p>
                </div>
                
                <?php if ($register_error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded mb-6 text-sm font-medium"><?= $register_error ?></div>
                <?php endif; ?>

                <form action="auth.php" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="reg_name" required class="w-full border border-slate-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-slate-50 focus:bg-white placeholder-slate-400" placeholder="William Ruto">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="reg_email" required class="w-full border border-slate-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-slate-50 focus:bg-white placeholder-slate-400" placeholder="wantam@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input type="password" name="reg_password" required minlength="6" class="w-full border border-slate-300 rounded-lg p-3 outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all bg-slate-50 focus:bg-white placeholder-slate-400" placeholder="At least 6 characters">
                    </div>
                    <button type="submit" name="action_register" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-lg hover:bg-indigo-700 hover:shadow-lg transform transition-all duration-200 mt-2">
                        Create Account
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="absolute top-0 left-0 w-full h-64 bg-indigo-600 shadow-lg -z-10" style="clip-path: polygon(0 0, 100% 0, 100% 40%, 0 100%);"></div>

    <script>
        function switchTab(tabName) {
            const loginTab = document.getElementById('tab-login');
            const registerTab = document.getElementById('tab-register');
            const btnLogin = document.getElementById('btn-login');
            const btnRegister = document.getElementById('btn-register');

            btnLogin.className = "flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none text-slate-500 hover:text-slate-700 hover:bg-slate-50";
            btnRegister.className = "flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none text-slate-500 hover:text-slate-700 hover:bg-slate-50";
            
            if (tabName === 'login') {
                loginTab.classList.remove('hidden');
                registerTab.classList.add('hidden');
                btnLogin.className = "flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none text-indigo-600 border-b-2 border-indigo-600 bg-slate-50/50";
            } else {
                registerTab.classList.remove('hidden');
                loginTab.classList.add('hidden');
                btnRegister.className = "flex-1 py-4 text-center font-semibold text-sm transition-colors outline-none text-indigo-600 border-b-2 border-indigo-600 bg-slate-50/50";
            }
        }
    </script>
</body>
</html>