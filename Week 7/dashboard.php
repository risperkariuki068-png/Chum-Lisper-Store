<?php
// Start the session to read session variables
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    // Kick them back to the login page
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Protected Dashboard</title>
</head>
<body>
    <h2>Welcome to the Protected Shop Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>Only logged-in users can see this screen.</p>
    <a href="logout.php">Logout</a>
</body>
</html>