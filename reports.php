<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch Total Statistics
$totalBooks = $pdo->query("SELECT SUM(quantity) as total FROM books")->fetch()['total'] ?? 0;
$totalIssued = $pdo->query("SELECT COUNT(*) as total FROM issues WHERE status = 'Issued'")->fetch()['total'] ?? 0;
$totalFines = $pdo->query("SELECT SUM(fine_amount) as total FROM issues")->fetch()['total'] ?? 0;

// Fetch Complete Issue/Return History with Fines
$history = $pdo->query("SELECT issues.*, users.full_name, books.title FROM issues 
                        JOIN users ON issues.user_id = users.user_id 
                        JOIN books ON issues.book_id = books.book_id 
                        ORDER BY issues.issue_id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tracking Reports - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px 30px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .container { margin-top: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        .stats-grid { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #e9ecef; padding: 20px; border-radius: 6px; flex: 1; text-align: center; }
        .stat-card h4 { margin: 0; color: #555; }
        .stat-card p { font-size: 24px; font-weight: bold; color: #007bff; margin: 10px 0 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .badge-returned { color: green; font-weight: bold; }
        .badge-issued { color: orange; font-weight: bold; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Library Tracking & Fine Reports</h2>
        <a href="dashboard.php" style="color: white; text-decoration: none;">← Back to Dashboard</a>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">&laquo; Return to Dashboard</a>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Books in Library</h4>
                <p><?= $totalBooks ?></p>
            </div>
            <div class="stat-card">
                <h4>Currently Issued Books</h4>
                <p><?= $totalIssued ?></p>
            </div>
            <div class="stat-card">
                <h4>Total Fines Collected</h4>
                <p>Rs. <?= $totalFines ?></p>
            </div>
        </div>

        <h3>Complete Transaction & Fine History</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Member</th>
                    <th>Book Title</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Return Date</th>
                    <th>Fine (Rs.)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($history) > 0): ?>
                    <?php foreach ($history as $row): ?>
                        <tr>
                            <td><?= $row['issue_id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= $row['issue_date'] ?></td>
                            <td><?= $row['due_date'] ?></td>
                            <td><?= $row['return_date'] ?? 'Not Returned Yet' ?></td>
                            <td>Rs. <?= $row['fine_amount'] ?></td>
                            <td>
                                <span class="<?= $row['status'] == 'Returned' ? 'badge-returned' : 'badge-issued' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #777;">No history records found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>