<?php
require_once 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($full_name) && !empty($email) && !empty($password)) {
        // Check whether the email is already registered.
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "This email is already registered."; // Error message as per the proposal[cite: 5]
        } else {
            // Hashing passwords for security[cite: 2, 9]
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $insert = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, 'member')");
            if ($insert->execute([$full_name, $email, $password_hash])) {
                $message = "Account created successfully. Please log in."; // Proposal success message[cite: 5]
            } else {
                $message = "Something went wrong. Please try again.";
            }
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; border: none; color: white; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #0056b3; }
        p { text-align: center; color: #d9534f; font-size: 14px; }
        .login-link { text-align: center; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Register</h2>
        <?php if(!empty($message)): ?>
            <p style="color: <?= strpos($message, 'success') !== false ? 'green' : 'red' ?>;"><?= $message ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <label>Full Name</label>
            <input type="text" name="full_name" required>
            
            <label>Email</label>
            <input type="email" name="email" required>
            
            <label>Password</label>
            <input type="password" name="password" required>
            
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>