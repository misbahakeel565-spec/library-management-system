<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION['full_name'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px 30px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .container { margin-top: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        .logout-btn { background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        .logout-btn:hover { background: #c82333; }
        .nav-buttons { margin-top: 20px; display: flex; gap: 15px; flex-wrap: wrap; }
        .btn { padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-books { background: #007bff; }
        .btn-issue { background: #28a745; }
        .btn-reports { background: #17a2b8; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Library Dashboard</h2>
        <div>
            <span>Welcome, <strong><?= htmlspecialchars($full_name) ?></strong> (<?= ucfirst($role) ?>)</span> | 
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="container">
        <h3>Dashboard Overview</h3>
        <?php if ($role == 'admin'): ?>
            <p>Welcome Admin! Here you can manage books, issue/return records, and view financial reports.</p>
            <div class="nav-buttons">
                <a href="books.php" class="btn btn-books">Manage Books & QR Codes</a>
                <a href="issue.php" class="btn btn-issue">Issue & Return Management</a>
                <a href="reports.php" class="btn btn-reports">View Tracking & Reports</a>
            </div>
        <?php else: ?>
            <p>Welcome Member! Here you can search books and view your issued books and fines.</p>
            <div class="nav-buttons">
                <a href="books.php" class="btn btn-books">Browse Book Catalogue</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>