<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';

// Handle Issue Book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['issue_book'])) {
    $user_id = intval($_POST['user_id']);
    $book_id = intval($_POST['book_id']);
    
    // Check if book has available copies
    $bookStmt = $pdo->prepare("SELECT available_copies FROM books WHERE book_id = ?");
    $bookStmt->execute([$book_id]);
    $book = $bookStmt->fetch();

    if ($book && $book['available_copies'] > 0) {
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime('+14 days')); // 14 days due period

        // Insert into issues table
        $stmt = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'Issued')");
        if ($stmt->execute([$book_id, $user_id, $issue_date, $due_date])) {
            // Decrease available copies
            $update = $pdo->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?");
            $update->execute([$book_id]);
            $message = "Book issued successfully. Due date: $due_date";
        }
    } else {
        $message = "This book is currently not available."; // Proposal message[cite: 5]
    }
}

// Handle Return Book
if (isset($_GET['return_id'])) {
    $issue_id = intval($_GET['return_id']);
    
    // Get issue details
    $issueStmt = $pdo->prepare("SELECT * FROM issues WHERE issue_id = ? AND status = 'Issued'");
    $issueStmt->execute([$issue_id]);
    $issue = $issueStmt->fetch();

    if ($issue) {
        $return_date = date('Y-m-d');
        $due_date = $issue['due_date'];
        
        // Calculate fine (Rs. 10 per day late)[cite: 5]
        $fine = 0;
        if (strtotime($return_date) > strtotime($due_date)) {
            $diff = abs(strtotime($return_date) - strtotime($due_date));
            $days_late = ceil($diff / (60 * 60 * 24));
            $fine = $days_late * 10; // Rs. 10 per day fine rule[cite: 5]
        }

        // Update issue record
        $updateIssue = $pdo->prepare("UPDATE issues SET return_date = ?, fine_amount = ?, status = 'Returned' WHERE issue_id = ?");
        $updateIssue->execute([$return_date, $fine, $issue_id]);

        // Increase available copies back
        $updateBook = $pdo->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
        $updateBook->execute([$issue['book_id']]);

        $message = "Book returned successfully. Fine calculated: Rs. $fine";
    }
}

// Fetch users and books for dropdowns
$users = $pdo->query("SELECT user_id, full_name, email FROM users ")->fetchAll();
$books = $pdo->query("SELECT book_id, title, available_copies FROM books WHERE available_copies > 0")->fetchAll();

// Fetch all active issues
$activeIssues = $pdo->query("SELECT issues.*, users.full_name, books.title FROM issues 
                             JOIN users ON issues.user_id = users.user_id 
                             JOIN books ON issues.book_id = books.book_id 
                             WHERE issues.status = 'Issued'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue & Return - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px 30px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .container { margin-top: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        select { padding: 8px; width: 250px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; background: #28a745; border: none; color: white; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .return-btn { background: #dc3545; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; }
        .return-btn:hover { background: #c82333; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Issue & Return Management</h2>
        <a href="dashboard.php" style="color: white; text-decoration: none;">← Back to Dashboard</a>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">&laquo; Return to Dashboard</a>
        
        <?php if(!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?= $message ?></p>
        <?php endif; ?>

        <h3>Issue a Book to Member</h3>
        <form method="POST" action="" style="margin-bottom: 30px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
            <select name="user_id" required>
                <option value="">Select Member</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= $u['email'] ?>)</option>
                <?php endforeach; ?>
            </select>

            <select name="book_id" required>
                <option value="">Select Book</option>
                <?php foreach ($books as $b): ?>
                    <option value="<?= $b['book_id'] ?>"><?= htmlspecialchars($b['title']) ?> (Available: <?= $b['available_copies'] ?>)</option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="issue_book">Issue Book</button>
        </form>

        <h3>Currently Issued Books</h3>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    <th>Member Name</th>
                    <th>Book Title</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($activeIssues) > 0): ?>
                    <?php foreach ($activeIssues as $issue): ?>
                        <tr>
                            <td><?= $issue['issue_id'] ?></td>
                            <td><?= htmlspecialchars($issue['full_name']) ?></td>
                            <td><?= htmlspecialchars($issue['title']) ?></td>
                            <td><?= $issue['issue_date'] ?></td>
                            <td><?= $issue['due_date'] ?></td>
                            <td>
                                <a href="issue.php?return_id=<?= $issue['issue_id'] ?>" class="return-btn" onclick="return confirm('Are you sure you want to return this book?')">Mark as Returned</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #777;">No active book issues right now.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>