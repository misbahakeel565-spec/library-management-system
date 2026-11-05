<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$message = '';

// Handle Add Book (Admin Only)
if ($role == 'admin' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $quantity = intval($_POST['quantity']);
    $shelf = trim($_POST['shelf_location']);

    if (!empty($title) && !empty($author) && !empty($isbn) && $quantity > 0) {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, quantity, available_copies, shelf_location) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $author, $isbn, $quantity, $quantity, $shelf])) {
            $message = "Book added successfully!";
        } else {
            $message = "Failed to add book.";
        }
    } else {
        $message = "Please fill in all required fields correctly.";
    }
}

// Fetch Books with Search
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
} else {
    $stmt = $pdo->query("SELECT * FROM books");
}
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Catalogue - Library Management System</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; margin: 0; padding: 20px; }
        .header { background: #007bff; color: white; padding: 15px 30px; border-radius: 5px; display: flex; justify-content: space-between; align-items: center; }
        .container { margin-top: 30px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        input[type="text"], input[type="number"] { padding: 8px; width: 200px; margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; background: #28a745; border: none; color: white; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        .search-bar { margin-bottom: 20px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Book Catalogue</h2>
        <a href="dashboard.php" style="color: white; text-decoration: none;">← Back to Dashboard</a>
    </div>

    <div class="container">
        <a href="dashboard.php" class="back-link">&laquo; Return to Dashboard</a>
        
        <?php if(!empty($message)): ?>
            <p style="color: green; font-weight: bold;"><?= $message ?></p>
        <?php endif; ?>

        <!-- Se arch Form -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" placeholder="Search by title, author..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Search</button>
                <?php if(!empty($search)): ?>
                    <a href="books.php" style="margin-left: 10px; color: #dc3545; text-decoration: none;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($role == 'admin'): ?>
            <h3>Add New Book</h3>
            <form method="POST" action="" style="margin-bottom: 30px; background: #f9f9f9; padding: 15px; border-radius: 5px;">
                <input type="text" name="title" placeholder="Book Title" required>
                <input type="text" name="author" placeholder="Author Name" required>
                <input type="text" name="isbn" placeholder="ISBN" required>
                <input type="number" name="quantity" placeholder="Quantity" min="1" required>
                <input type="text" name="shelf_location" placeholder="Shelf Location">
                <button type="submit" name="add_book">Add Book</button>
            </form>
        <?php endif; ?>

        <h3>Available Books</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>ISBN</th>
                    <th>Available Copies</th>
                    <th>Shelf</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($books) > 0): ?>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td><?= $book['book_id'] ?></td>
                            <td><?= htmlspecialchars($book['title']) ?></td>
                            <td><?= htmlspecialchars($book['author']) ?></td>
                            <td><?= htmlspecialchars($book['isbn']) ?></td>
                            <td><?= $book['available_copies'] ?> / <?= $book['quantity'] ?></td>
                            <td><?= htmlspecialchars($book['shelf_location']) ?></td>
                            <td>
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=ISBN:<?= urlencode($book['isbn']) ?>" alt="QR Code">
</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #777;">No books found matching your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>