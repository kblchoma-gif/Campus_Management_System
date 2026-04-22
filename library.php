<?php
/**
 * Library Management Module
 * Cyberpunk Theme - Handles book borrowing, returns, and fine management
 */

require_once 'config.php';
session_start();

// Check admin authentication
requireAdminLogin();

$message = '';
$message_type = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        $result = addLibraryUser($name, $email);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
    
    // Borrow book
    if (isset($_POST['borrow_book'])) {
        $user_id = intval($_POST['user_id']);
        $book_title = trim($_POST['book_title']);
        $category = $_POST['category'];
        $due_date = $_POST['due_date'];
        
        $result = borrowBook($user_id, $book_title, $category, $due_date);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    }
    
    // Return book
    if (isset($_POST['return_book'])) {
        $user_id = intval($_POST['user_id']);
        $book_uid = $_POST['book_uid'];
        
        $result = returnBook($user_id, $book_uid);
        if ($result['success']) {
            $message = $result['message'] . " Fine: R" . number_format($result['fine'], 2);
            $message_type = 'success';
        } else {
            $message = $result['message'];
            $message_type = 'error';
        }
    }
}

// Get all library users
$users = getAllLibraryUsers();

// For return form - get selected user's borrowed books
$selected_user_books = [];
if (isset($_POST['return_user_id'])) {
    $selected_user_books = getUserBorrowedBooks(intval($_POST['return_user_id']));
    $selected_user_books = array_filter($selected_user_books, function($book) {
        return $book['status'] == 'borrowed';
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📚 LIBRARY SYSTEM // CYBERPUNK CAMPUS</title>
    <style>
        /* Cyberpunk theme styles */
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap');
        
        :root {
            --neon-cyan: #0ff;
            --neon-pink: #f0f;
            --neon-purple: #b300ff;
            --neon-green: #0f0;
            --dark-bg: #0a0a0f;
            --darker-bg: #050507;
            --glow-cyan: 0 0 10px rgba(0, 255, 255, 0.5);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Share Tech Mono', monospace;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
            min-height: 100vh;
            color: var(--neon-cyan);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(10, 10, 15, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2em;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-pink));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .back-btn {
            background: rgba(0, 255, 255, 0.2);
            color: var(--neon-cyan);
            padding: 10px 25px;
            text-decoration: none;
            border-radius: 10px;
            border: 1px solid var(--neon-cyan);
            transition: all 0.3s;
            font-family: 'Orbitron', monospace;
        }
        
        .back-btn:hover {
            background: var(--neon-cyan);
            color: var(--dark-bg);
            box-shadow: var(--glow-cyan);
        }
        
        .content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }
        
        .card {
            background: rgba(10, 10, 15, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 25px;
        }
        
        .card h2 {
            font-family: 'Orbitron', monospace;
            color: var(--neon-cyan);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--neon-cyan);
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--neon-purple);
            font-weight: bold;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            border-radius: 10px;
            color: var(--neon-cyan);
            font-family: 'Share Tech Mono', monospace;
            font-size: 1em;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            box-shadow: var(--glow-cyan);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-purple));
            border: none;
            border-radius: 10px;
            color: var(--dark-bg);
            font-family: 'Orbitron', monospace;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: scale(1.02);
            box-shadow: var(--glow-cyan);
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .success {
            background: rgba(0, 255, 0, 0.2);
            border: 1px solid var(--neon-green);
            color: var(--neon-green);
        }
        
        .error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #f00;
            color: #f66;
        }
        
        .rate-info {
            background: rgba(0, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid rgba(0, 255, 255, 0.3);
        }
        
        .rate-info h4 {
            color: var(--neon-pink);
            margin-bottom: 10px;
        }
        
        .user-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .user-item {
            background: rgba(0, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 3px solid var(--neon-cyan);
        }
        
        .user-item:hover {
            transform: translateX(5px);
            background: rgba(0, 255, 255, 0.1);
        }
        
        .user-item h4 {
            color: var(--neon-pink);
            margin-bottom: 5px;
        }
        
        .user-item .fines {
            color: #ff66aa;
            font-weight: bold;
        }
        
        .book-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .book-item {
            background: rgba(0, 255, 255, 0.05);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        
        .status-borrowed {
            color: #ffaa44;
            font-weight: bold;
        }
        
        .status-returned {
            color: #44ff44;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 LIBRARY MANAGEMENT SYSTEM</h1>
            <a href="index.php" class="back-btn">⏎ BACK TO TERMINAL</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                > <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <!-- Add User Form -->
            <div class="card">
                <h2>> REGISTER NEW PATRON</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>FULL NAME</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>EMAIL ADDRESS</label>
                        <input type="email" name="email" placeholder="user@campus.edu" required>
                    </div>
                    <button type="submit" name="add_user" class="btn">REGISTER USER</button>
                </form>
                
                <div class="rate-info">
                    <h4>> FINE RATES</h4>
                    <p>📘 Textbooks: <span style="color: var(--neon-cyan);">R5/day</span></p>
                    <p>📓 Journals: <span style="color: var(--neon-cyan);">R3/day</span></p>
                    <p>📚 Reference: <span style="color: var(--neon-cyan);">R10/day</span></p>
                    <p style="margin-top: 10px; color: #ff66aa;">⚠️ MAX FINE LIMIT: R<?php echo MAX_FINE_LIMIT; ?></p>
                </div>
            </div>
            
            <!-- Borrow Book Form -->
            <div class="card">
                <h2>> BORROW BOOK</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>SELECT PATRON</label>
                        <select name="user_id" required>
                            <option value="">Choose user...</option>
                            <?php foreach ($users as $id => $user): ?>
                            <option value="<?php echo $id; ?>">
                                <?php echo htmlspecialchars($user['name']); ?> 
                                (Fines: R<?php echo number_format($user['total_fines'], 2); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>BOOK TITLE</label>
                        <input type="text" name="book_title" placeholder="Enter book title" required>
                    </div>
                    <div class="form-group">
                        <label>CATEGORY</label>
                        <select name="category" required>
                            <option value="textbook">Textbook (R5/day)</option>
                            <option value="journal">Journal (R3/day)</option>
                            <option value="reference">Reference Book (R10/day)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>DUE DATE</label>
                        <input type="date" name="due_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <button type="submit" name="borrow_book" class="btn">BORROW BOOK</button>
                </form>
            </div>
            
            <!-- Return Book Form -->
            <div class="card">
                <h2>> RETURN BOOK</h2>
                <form method="POST" id="returnForm">
                    <div class="form-group">
                        <label>SELECT PATRON</label>
                        <select name="return_user_id" id="return_user_id" onchange="this.form.submit()" required>
                            <option value="">Choose user...</option>
                            <?php foreach ($users as $id => $user): ?>
                            <option value="<?php echo $id; ?>" <?php echo (isset($_POST['return_user_id']) && $_POST['return_user_id'] == $id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if (isset($_POST['return_user_id']) && !empty($selected_user_books)): ?>
                    <div class="form-group">
                        <label>SELECT BOOK TO RETURN</label>
                        <select name="book_uid" required>
                            <option value="">Choose book...</option>
                            <?php foreach ($selected_user_books as $book): ?>
                            <option value="<?php echo $book['book_uid']; ?>">
                                <?php echo htmlspecialchars($book['title']); ?> 
                                (Due: <?php echo $book['due_date']; ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="return_book" class="btn">RETURN BOOK</button>
                    <?php elseif (isset($_POST['return_user_id'])): ?>
                    <p style="text-align: center; color: #ffaa44; padding: 20px;">
                        > NO BOOKS CURRENTLY BORROWED
                    </p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- User List Section -->
        <div class="card" style="margin-top: 25px;">
            <h2>> REGISTERED PATRONS</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
                <?php foreach ($users as $id => $user): 
                    $user_books = getUserBorrowedBooks($id);
                    $active_books = array_filter($user_books, function($b) { return $b['status'] == 'borrowed'; });
                ?>
                <div class="user-item" onclick="showUserBooks(<?php echo $id; ?>, this)">
                    <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p>📧 <?php echo htmlspecialchars($user['email']); ?></p>
                    <p>📅 Joined: <?php echo $user['join_date']; ?></p>
                    <p class="fines">💰 Fines: R<?php echo number_format($user['total_fines'], 2); ?></p>
                    <p>📚 Active Loans: <?php echo count($active_books); ?></p>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($users)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    > NO PATRONS REGISTERED YET
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Books Details Modal (simplified - shows in console) -->
        <div id="bookDetails" style="display: none;"></div>
    </div>
    
    <script>
    function showUserBooks(userId, element) {
        // Remove selected class from all
        document.querySelectorAll('.user-item').forEach(el => {
            el.style.background = 'rgba(0, 255, 255, 0.05)';
        });
        element.style.background = 'rgba(0, 255, 255, 0.2)';
        
        // Fetch user books via AJAX (simplified - show alert with info)
        alert('User books can be viewed in the database. Click "Return Book" to see borrowed books.');
    }
    </script>
</body>
</html>