<?php
/**
 * Campus Management System - Database Configuration & Core Functions
 * Cyberpunk Theme Edition
 * @author Campus Management System
 * @version 2.0
 */

// ==================== DATABASE CONFIGURATION ====================
// Define database connection parameters
define('DB_HOST', 'localhost');      // Database server hostname
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password (empty for local development)
define('DB_NAME', 'campus_management'); // Database name

// ==================== SYSTEM CONSTANTS ====================
define('PARKING_CAPACITY', 500);           // Maximum parking spots available
define('PARKING_PRICE_STUDENT', 450);      // Monthly student parking fee in Rands
define('PARKING_PRICE_STAFF', 750);        // Monthly staff parking fee in Rands
define('PARKING_PRICE_VISITOR', 100);      // Daily visitor parking fee in Rands
define('MAX_FINE_LIMIT', 200);             // Maximum fine limit before borrowing restriction
define('ADMIN_USERNAME', 'admin');         // Default admin username
define('ADMIN_PASSWORD', 'campus2024');    // Default admin password (should be changed in production)

// ==================== DATABASE CONNECTION FUNCTION ====================
/**
 * Establish database connection using MySQLi
 * @return mysqli Database connection object
 */
function getDBConnection() {
    // Create new MySQLi connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check if connection was successful
    if ($conn->connect_error) {
        // If connection fails, terminate script with error message
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Return the connection object
    return $conn;
}

// ==================== DATABASE INITIALIZATION ====================
/**
 * Create all required database tables if they don't exist
 * This function sets up the complete database schema
 */
function initializeDatabase() {
    // Get database connection
    $conn = getDBConnection();
    
    // SQL query to create parking_permits table
    $sql_parking = "CREATE TABLE IF NOT EXISTS parking_permits (
        id INT AUTO_INCREMENT PRIMARY KEY,              // Unique permit ID
        permit_number VARCHAR(50) UNIQUE NOT NULL,      // Unique permit identifier
        name VARCHAR(100) NOT NULL,                     // Permit holder's name
        age INT NOT NULL,                               // Permit holder's age
        type ENUM('student', 'staff', 'visitor') NOT NULL, // Permit type
        price DECIMAL(10,2) NOT NULL,                   // Permit price
        issue_date DATETIME NOT NULL,                   // Date permit was issued
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  // Record creation timestamp
    )";
    
    // SQL query to create library_users table
    $sql_users = "CREATE TABLE IF NOT EXISTS library_users (
        id INT AUTO_INCREMENT PRIMARY KEY,              // Unique user ID
        user_uid VARCHAR(50) UNIQUE NOT NULL,           // Unique user identifier
        name VARCHAR(100) NOT NULL,                     // User's full name
        email VARCHAR(100) NOT NULL,                    // User's email address
        join_date DATE NOT NULL,                        // Date user joined
        total_fines DECIMAL(10,2) DEFAULT 0,            // Accumulated fines
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  // Record creation timestamp
    )";
    
    // SQL query to create borrowed_books table
    $sql_books = "CREATE TABLE IF NOT EXISTS borrowed_books (
        id INT AUTO_INCREMENT PRIMARY KEY,              // Unique borrowing record ID
        user_id INT NOT NULL,                           // Reference to library user
        book_uid VARCHAR(50) UNIQUE NOT NULL,           // Unique book identifier
        title VARCHAR(200) NOT NULL,                    // Book title
        category ENUM('textbook', 'journal', 'reference') NOT NULL, // Book category
        borrow_date DATE NOT NULL,                      // Date book was borrowed
        due_date DATE NOT NULL,                         // Date book is due
        return_date DATE NULL,                          // Date book was returned (NULL if not returned)
        status ENUM('borrowed', 'returned') DEFAULT 'borrowed', // Current status
        fine DECIMAL(10,2) DEFAULT 0,                   // Fine amount if applicable
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, // Record creation timestamp
        FOREIGN KEY (user_id) REFERENCES library_users(id) ON DELETE CASCADE // Foreign key constraint
    )";
    
    // SQL query to create students table for performance tracking
    $sql_students = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,              // Unique student ID
        student_number VARCHAR(50) UNIQUE NOT NULL,     // Unique student number
        name VARCHAR(100) NOT NULL,                     // Student's full name
        mark1 DECIMAL(5,2) NOT NULL,                    // First subject mark
        mark2 DECIMAL(5,2) NOT NULL,                    // Second subject mark
        mark3 DECIMAL(5,2) NOT NULL,                    // Third subject mark
        mark4 DECIMAL(5,2) NOT NULL,                    // Fourth subject mark
        mark5 DECIMAL(5,2) NOT NULL,                    // Fifth subject mark
        mark6 DECIMAL(5,2) NOT NULL,                    // Sixth subject mark
        average DECIMAL(5,2) NOT NULL,                  // Calculated average
        result ENUM('Distinction', 'Pass', 'Fail') NOT NULL, // Final result
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, // Record creation timestamp
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP // Last update timestamp
    )";
    
    // Execute all SQL queries
    $conn->query($sql_parking);
    $conn->query($sql_users);
    $conn->query($sql_books);
    $conn->query($sql_students);
    
    // Close database connection
    $conn->close();
}

// ==================== PARKING MODULE FUNCTIONS ====================

/**
 * Issue a new parking permit to a person
 * @param string $type Permit type (student, staff, visitor)
 * @param string $name Person's full name
 * @param int $age Person's age
 * @return array Result with success status and message
 */
function issueParkingPermit($type, $name, $age) {
    // Validate age requirement (must be 18 or older)
    if ($age < 18) {
        return ['success' => false, 'message' => 'Person must be 18 years or older to get a parking permit.'];
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Count total active permits
    $result = $conn->query("SELECT COUNT(*) as total FROM parking_permits");
    $row = $result->fetch_assoc();
    $totalPermits = $row['total'];
    
    // Check if parking capacity is reached
    if ($totalPermits >= PARKING_CAPACITY) {
        $conn->close();
        return ['success' => false, 'message' => 'Parking capacity reached. No more permits can be issued.'];
    }
    
    // Determine price based on permit type
    $price = 0;
    switch ($type) {
        case 'student':
            $price = PARKING_PRICE_STUDENT;
            break;
        case 'staff':
            $price = PARKING_PRICE_STAFF;
            break;
        case 'visitor':
            $price = PARKING_PRICE_VISITOR;
            break;
        default:
            $conn->close();
            return ['success' => false, 'message' => 'Invalid permit type.'];
    }
    
    // Generate unique permit number
    $permit_number = 'PRK-' . strtoupper(substr($type, 0, 1)) . '-' . rand(1000, 9999) . '-' . date('Ymd');
    $issue_date = date('Y-m-d H:i:s');
    
    // Prepare and execute SQL insert
    $stmt = $conn->prepare("INSERT INTO parking_permits (permit_number, name, age, type, price, issue_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisds", $permit_number, $name, $age, $type, $price, $issue_date);
    
    if ($stmt->execute()) {
        $permit = [
            'id' => $stmt->insert_id,
            'permit_number' => $permit_number,
            'name' => $name,
            'age' => $age,
            'type' => $type,
            'price' => $price,
            'issue_date' => $issue_date
        ];
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Permit issued successfully!', 'permit' => $permit];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to issue permit. Please try again.'];
    }
}

/**
 * Get parking statistics including counts and revenue
 * @return array Statistical data about parking permits
 */
function getParkingStats() {
    // Get database connection
    $conn = getDBConnection();
    
    // Initialize statistics array with default values
    $stats = [
        'student_count' => 0,
        'staff_count' => 0,
        'visitor_count' => 0,
        'total_permits' => 0,
        'student_revenue' => 0,
        'staff_revenue' => 0,
        'visitor_revenue' => 0,
        'total_revenue' => 0,
        'available_spots' => PARKING_CAPACITY
    ];
    
    // Query to get counts and revenue by permit type
    $result = $conn->query("
        SELECT 
            type,
            COUNT(*) as count,
            SUM(price) as revenue
        FROM parking_permits
        GROUP BY type
    ");
    
    // Process query results
    while ($row = $result->fetch_assoc()) {
        $type = $row['type'];
        $count = $row['count'];
        $revenue = $row['revenue'];
        
        switch ($type) {
            case 'student':
                $stats['student_count'] = $count;
                $stats['student_revenue'] = $revenue;
                break;
            case 'staff':
                $stats['staff_count'] = $count;
                $stats['staff_revenue'] = $revenue;
                break;
            case 'visitor':
                $stats['visitor_count'] = $count;
                $stats['visitor_revenue'] = $revenue;
                break;
        }
    }
    
    // Calculate totals
    $stats['total_permits'] = $stats['student_count'] + $stats['staff_count'] + $stats['visitor_count'];
    $stats['total_revenue'] = $stats['student_revenue'] + $stats['staff_revenue'] + $stats['visitor_revenue'];
    $stats['available_spots'] = PARKING_CAPACITY - $stats['total_permits'];
    
    // Close connection and return statistics
    $conn->close();
    return $stats;
}

/**
 * Get all parking permits sorted by issue date
 * @return array List of all parking permits
 */
function getAllParkingPermits() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM parking_permits ORDER BY issue_date DESC");
    $permits = [];
    while ($row = $result->fetch_assoc()) {
        $permits[] = $row;
    }
    $conn->close();
    return $permits;
}

// ==================== LIBRARY MODULE FUNCTIONS ====================

/**
 * Calculate fine based on due date and return date
 * @param string $due_date Due date of the book
 * @param string $return_date Return date of the book
 * @param string $category Category of the book
 * @return int Calculated fine amount
 */
function calculateFine($due_date, $return_date, $category) {
    // Convert dates to timestamps for comparison
    $due = strtotime($due_date);
    $return = strtotime($return_date);
    
    // No fine if returned on or before due date
    if ($return <= $due) {
        return 0;
    }
    
    // Calculate days late
    $days_late = ceil(($return - $due) / (60 * 60 * 24));
    
    // Fine rates per category
    $rates = [
        'textbook' => 5,   // R5 per day for textbooks
        'journal' => 3,    // R3 per day for journals
        'reference' => 10  // R10 per day for reference books
    ];
    
    // Calculate fine amount
    return $days_late * ($rates[$category] ?? 5);
}

/**
 * Add a new library user to the system
 * @param string $name User's full name
 * @param string $email User's email address
 * @return array Result with success status and message
 */
function addLibraryUser($name, $email) {
    $conn = getDBConnection();
    
    // Generate unique user ID
    $user_uid = 'LIB-' . uniqid();
    $join_date = date('Y-m-d');
    
    // Prepare and execute insert query
    $stmt = $conn->prepare("INSERT INTO library_users (user_uid, name, email, join_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $user_uid, $name, $email, $join_date);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'User added successfully!', 'user_id' => $user_id];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to add user.'];
    }
}

/**
 * Borrow a book for a library user
 * @param int $user_id ID of the user borrowing the book
 * @param string $book_title Title of the book
 * @param string $category Category of the book
 * @param string $due_date Due date for returning the book
 * @return array Result with success status and message
 */
function borrowBook($user_id, $book_title, $category, $due_date) {
    $conn = getDBConnection();
    
    // Check if user exists and has fines below limit
    $user_check = $conn->query("SELECT total_fines FROM library_users WHERE id = $user_id");
    if ($user_check->num_rows == 0) {
        $conn->close();
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    $user = $user_check->fetch_assoc();
    if ($user['total_fines'] > MAX_FINE_LIMIT) {
        $conn->close();
        return ['success' => false, 'message' => 'User has outstanding fines above R' . MAX_FINE_LIMIT . '. Cannot borrow new books.'];
    }
    
    // Generate unique book ID
    $book_uid = 'BK-' . uniqid();
    $borrow_date = date('Y-m-d');
    
    // Insert borrowing record
    $stmt = $conn->prepare("INSERT INTO borrowed_books (user_id, book_uid, title, category, borrow_date, due_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $book_uid, $book_title, $category, $borrow_date, $due_date);
    
    if ($stmt->execute()) {
        $borrowing = [
            'book_id' => $stmt->insert_id,
            'book_uid' => $book_uid,
            'title' => $book_title,
            'category' => $category,
            'borrow_date' => $borrow_date,
            'due_date' => $due_date,
            'status' => 'borrowed'
        ];
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Book borrowed successfully!', 'borrowing' => $borrowing];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to borrow book.'];
    }
}

/**
 * Return a borrowed book and calculate fine if applicable
 * @param int $user_id ID of the user returning the book
 * @param string $book_uid Unique identifier of the book
 * @return array Result with success status, message, and fine amount
 */
function returnBook($user_id, $book_uid) {
    $conn = getDBConnection();
    
    // Check if user exists
    $user_check = $conn->query("SELECT id FROM library_users WHERE id = $user_id");
    if ($user_check->num_rows == 0) {
        $conn->close();
        return ['success' => false, 'message' => 'User not found.'];
    }
    
    // Find the borrowed book
    $book_query = $conn->query("SELECT * FROM borrowed_books WHERE book_uid = '$book_uid' AND user_id = $user_id AND status = 'borrowed'");
    if ($book_query->num_rows == 0) {
        $conn->close();
        return ['success' => false, 'message' => 'Book not found or already returned.'];
    }
    
    $book = $book_query->fetch_assoc();
    $return_date = date('Y-m-d');
    
    // Calculate fine
    $fine = calculateFine($book['due_date'], $return_date, $book['category']);
    
    // Update book record
    $update_stmt = $conn->prepare("UPDATE borrowed_books SET return_date = ?, status = 'returned', fine = ? WHERE id = ?");
    $update_stmt->bind_param("sdi", $return_date, $fine, $book['id']);
    $update_stmt->execute();
    
    // Update user's total fines
    if ($fine > 0) {
        $conn->query("UPDATE library_users SET total_fines = total_fines + $fine WHERE id = $user_id");
    }
    
    $update_stmt->close();
    $conn->close();
    
    return ['success' => true, 'message' => 'Book returned successfully!', 'fine' => $fine];
}

/**
 * Get all library users
 * @return array List of all library users
 */
function getAllLibraryUsers() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM library_users ORDER BY name");
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[$row['id']] = $row;
    }
    $conn->close();
    return $users;
}

/**
 * Get borrowed books for a specific user
 * @param int $user_id ID of the user
 * @return array List of books borrowed by the user
 */
function getUserBorrowedBooks($user_id) {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM borrowed_books WHERE user_id = $user_id ORDER BY borrow_date DESC");
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    $conn->close();
    return $books;
}

/**
 * Get library statistics
 * @return array Statistical data about library operations
 */
function getLibraryStats() {
    $conn = getDBConnection();
    
    // Get active borrowers count
    $active_result = $conn->query("SELECT COUNT(DISTINCT user_id) as active FROM borrowed_books WHERE status = 'borrowed'");
    $active = $active_result->fetch_assoc();
    
    // Get total fines
    $fines_result = $conn->query("SELECT SUM(total_fines) as total_fines FROM library_users");
    $fines = $fines_result->fetch_assoc();
    
    // Get total books borrowed
    $books_result = $conn->query("SELECT COUNT(*) as total FROM borrowed_books");
    $books = $books_result->fetch_assoc();
    
    $conn->close();
    
    return [
        'active_borrowers' => $active['active'] ?? 0,
        'total_fines' => $fines['total_fines'] ?? 0,
        'total_books_borrowed' => $books['total'] ?? 0
    ];
}

// ==================== PERFORMANCE MODULE FUNCTIONS ====================

/**
 * Calculate average of marks array
 * @param array $marks Array of marks
 * @return float Calculated average
 */
function calculateAverage($marks) {
    // Return 0 if no marks provided
    if (empty($marks)) return 0;
    
    // Filter out invalid marks (non-numeric or out of range)
    $valid_marks = array_filter($marks, function($mark) {
        return is_numeric($mark) && $mark >= 0 && $mark <= 100;
    });
    
    // Calculate and return average
    return array_sum($valid_marks) / count($valid_marks);
}

/**
 * Assign result based on average
 * @param float $average Student's average mark
 * @return string Result category (Distinction, Pass, or Fail)
 */
function assignResult($average) {
    if ($average >= 75) return 'Distinction';  // 75% and above
    if ($average >= 50) return 'Pass';         // 50% to 74.99%
    return 'Fail';                              // Below 50%
}

/**
 * Add a new student with their marks
 * @param string $name Student name
 * @param string $student_number Unique student number
 * @param array $marks Array of 6 marks
 * @return array Result with success status and message
 */
function addStudent($name, $student_number, $marks) {
    $conn = getDBConnection();
    
    // Calculate average and result
    $average = calculateAverage($marks);
    $result = assignResult($average);
    
    // Prepare insert statement with 6 marks
    $stmt = $conn->prepare("INSERT INTO students (student_number, name, mark1, mark2, mark3, mark4, mark5, mark6, average, result) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssddddddds", 
        $student_number, $name, 
        $marks[0], $marks[1], $marks[2], 
        $marks[3], $marks[4], $marks[5],
        $average, $result
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Student added successfully!'];
    } else {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to add student. Student number may already exist.'];
    }
}

/**
 * Get all students with their performance data
 * @return array List of all students
 */
function getAllStudents() {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM students ORDER BY average DESC");
    $students = [];
    while ($row = $result->fetch_assoc()) {
        // Combine marks into array
        $row['marks'] = [$row['mark1'], $row['mark2'], $row['mark3'], $row['mark4'], $row['mark5'], $row['mark6']];
        $students[] = $row;
    }
    $conn->close();
    return $students;
}

/**
 * Get class statistics including averages and distribution
 * @return array Statistical data about class performance
 */
function getClassStatistics() {
    $students = getAllStudents();
    
    // Return empty stats if no students
    if (empty($students)) {
        return [
            'highest_average' => 0,
            'lowest_average' => 0,
            'class_average' => 0,
            'student_count' => 0,
            'distinctions' => 0,
            'passes' => 0,
            'fails' => 0
        ];
    }
    
    // Extract averages and results
    $averages = array_column($students, 'average');
    $results = array_column($students, 'result');
    
    // Calculate statistics
    $stats = [
        'highest_average' => max($averages),
        'lowest_average' => min($averages),
        'class_average' => array_sum($averages) / count($averages),
        'student_count' => count($students),
        'distinctions' => count(array_keys($results, 'Distinction')),
        'passes' => count(array_keys($results, 'Pass')),
        'fails' => count(array_keys($results, 'Fail'))
    ];
    
    return $stats;
}

/**
 * Get top performing student
 * @return array|null Top student data or null if no students
 */
function getTopPerformer() {
    $students = getAllStudents();
    if (empty($students)) return null;
    
    // Sort by average in descending order
    usort($students, function($a, $b) {
        return $b['average'] <=> $a['average'];
    });
    
    return $students[0];
}

/**
 * Validate marks to ensure they're within valid range
 * @param array $marks Array of marks to validate
 * @return array List of validation errors
 */
function validateMarks($marks) {
    $errors = [];
    foreach ($marks as $index => $mark) {
        if (!is_numeric($mark)) {
            $errors[] = "Mark " . ($index + 1) . " must be a number.";
        } elseif ($mark < 0 || $mark > 100) {
            $errors[] = "Mark " . ($index + 1) . " must be between 0 and 100.";
        }
    }
    return $errors;
}

// ==================== AUTHENTICATION FUNCTIONS ====================

/**
 * Verify admin login credentials
 * @param string $username Admin username
 * @param string $password Admin password
 * @return bool True if credentials are valid
 */
function verifyAdminLogin($username, $password) {
    return $username === ADMIN_USERNAME && $password === ADMIN_PASSWORD;
}

/**
 * Check if admin is logged in
 * @return bool True if admin session exists
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login - redirect if not logged in
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: index.php?error=unauthorized');
        exit();
    }
}

// Initialize database on file load
initializeDatabase();
?>