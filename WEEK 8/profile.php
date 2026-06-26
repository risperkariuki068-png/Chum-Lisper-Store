<?php
session_start();
// The ?? null coalescing operator checks if a session username exists; if not, it assigns a default guest value.
$current_user = $_SESSION['username'] ?? 'Guest Customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Chum-Lisper-Store</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        /* Flexbox Layout Requirement */
        .profile-container {
            display: flex;
            flex-direction: row;
            background: #ffffff;
            max-width: 800px;
            margin: 40px auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .profile-image-section {
            background-color: #0066cc;
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        /* Responsive Profile Image Requirement */
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            max-width: 100%;
        }

        .profile-details-section {
            flex: 2;
            padding: 40px;
        }

        .store-logo {
            font-size: 1rem;
            color: #0066cc;
            font-weight: bold;
            margin-bottom: 10px;
        }

        h1 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .about-section {
            margin-top: 20px;
            line-height: 1.6;
            color: #555;
        }

        .contact-info {
            margin-top: 25px;
            border-top: 1px solid #eaeaea;
            padding-top: 15px;
            font-size: 0.95rem;
            color: #444;
        }

        /* Media Queries Requirement for Mobile Stacked View */
        @media (max-width: 600px) {
            .profile-container {
                flex-direction: column;
                margin: 10px auto;
            }
            .profile-details-section {
                padding: 20px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="profile-image-section">
        <svg class="profile-img" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="background:#fff;">
            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12Z" fill="#0066cc"/>
            <path d="M12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="#0066cc"/>
        </svg>
    </div>

    <div class="profile-details-section">
        <div class="store-logo">🏪 Chum-Lisper-Store Customer Profile</div>
        <h1><?php echo htmlspecialchars($current_user); ?></h1>
        
        <div class="about-section">
            <h3>About Me</h3>
            <p>Welcome to my store personal profile page. I am exploring full-stack user environments, responsive styles, and secure application pipelines.</p>
        </div>

        <div class="contact-info">
            <h3>Contact Information</h3>
            <p><strong>Email:</strong> user@example.com</p>
            <p><strong>Account Status:</strong> Active Platform Member</p>
        </div>
    </div>
</div>

</body>
</html>