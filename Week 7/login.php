<?php
// Start the session at the very top for session management
session_start();

// Include your database connection file
require_once 'db_connect.php';

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Fetch the user by their email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify if user exists and check the hashed password
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables upon successful authentication
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            $message = "Login Successful! Welcome, " . htmlspecialchars($user['username']) . ".";
            $message_type = "success";
            
            // Optional: header("Location: index.php"); exit;
        } else {
            $message = "Invalid Email or Password!";
            $message_type = "error";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Chum-Lisper-Store</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        .logo-text {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 10px;
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .input-group label {
            display: block;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        .input-group input:focus {
            border-color: #0066cc;
            outline: none;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #0052a3;
        }
        .msg {
            margin-top: 15px;
            padding: 10px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
        .switch-link {
            margin-top: 15px;
            font-size: 0.85rem;
            color: #666;
        }
        .switch-link a {
            color: #0066cc;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-text">🏪 Chum-Lisper-Store</div>
    <h2>Login Form</h2>
    
    <form method="POST" action="">
        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Email" required>
        </div>
        
        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        
        <button type="submit" class="btn-submit">Login</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="msg <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="switch-link">
        Don't have an account? <a href="register.php">Register here.</a>
    </div>
</div>

</body>
</html>