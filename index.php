<?php
/**
 * Campus Management System - Main Application
 * Cyberpunk Theme Edition with Admin Login
 * @author Campus Management System
 * @version 2.0
 */

// Include database configuration and functions
require_once 'config.php';

// Start session for login state management
session_start();

// Handle admin login
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Get login credentials from form
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Verify credentials using the authentication function
    if (verifyAdminLogin($username, $password)) {
        // Set session variable to indicate admin is logged in
        $_SESSION['admin_logged_in'] = true;
        // Refresh page to show admin content
        header('Location: index.php');
        exit();
    } else {
        // Set error message for invalid credentials
        $login_error = 'Invalid username or password!';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    // Destroy all session data
    session_destroy();
    // Redirect to main page
    header('Location: index.php');
    exit();
}

// Check if admin is logged in
$is_admin = isAdminLoggedIn();

// Get real-time statistics from database
$parking_stats = getParkingStats();
$library_stats = getLibraryStats();
$performance_stats = getClassStatistics();

// Get top performer for display
$top_performer = getTopPerformer();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ CYBERPUNK CAMPUS • MANAGEMENT SYSTEM</title>
    <style>
        /* ==================== CYBERPUNK THEME CSS ==================== */
        
        /* Import futuristic fonts */
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap');
        
        /* CSS Variables for cyberpunk color scheme */
        :root {
            --neon-cyan: #0ff;
            --neon-pink: #f0f;
            --neon-purple: #b300ff;
            --neon-yellow: #ff0;
            --dark-bg: #0a0a0f;
            --darker-bg: #050507;
            --glow-cyan: 0 0 10px rgba(0, 255, 255, 0.5);
            --glow-pink: 0 0 10px rgba(255, 0, 255, 0.5);
            --glow-purple: 0 0 15px rgba(179, 0, 255, 0.6);
        }
        
        /* Global reset and base styles */
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
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated cyber grid background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(var(--neon-cyan) 1px, transparent 1px),
                linear-gradient(90deg, var(--neon-cyan) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.05;
            pointer-events: none;
            animation: gridMove 20s linear infinite;
        }
        
        /* Grid movement animation */
        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        /* Glowing scanline effect */
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(transparent 50%, rgba(0, 255, 255, 0.03) 50%);
            background-size: 100% 4px;
            pointer-events: none;
            animation: scanline 8s linear infinite;
        }
        
        @keyframes scanline {
            0% {
                transform: translateY(-100%);
            }
            100% {
                transform: translateY(100%);
            }
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
            z-index: 1;
        }
        
        /* Cyberpunk Header */
        .header {
            background: rgba(10, 10, 15, 0.9);
            backdrop-filter: blur(10px);
            border: 2px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--glow-cyan), inset 0 0 30px rgba(0, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '>';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 60px;
            opacity: 0.1;
            font-family: monospace;
        }
        
        .header h1 {
            font-family: 'Orbitron', monospace;
            font-size: 2.5em;
            font-weight: 900;
            text-transform: uppercase;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-pink));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: var(--glow-cyan);
            letter-spacing: 2px;
        }
        
        .header p {
            color: var(--neon-purple);
            font-size: 1.1em;
            margin-top: 10px;
            text-shadow: 0 0 5px rgba(179, 0, 255, 0.5);
        }
        
        /* Login Form Styles */
        .login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(5px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-modal {
            background: var(--darker-bg);
            border: 2px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 40px;
            width: 90%;
            max-width: 400px;
            box-shadow: var(--glow-cyan);
            animation: glitch 0.3s ease;
        }
        
        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
        }
        
        .login-modal h2 {
            font-family: 'Orbitron', monospace;
            color: var(--neon-cyan);
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        
        .login-modal input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            border-radius: 10px;
            color: var(--neon-cyan);
            font-family: 'Share Tech Mono', monospace;
            font-size: 1em;
        }
        
        .login-modal input:focus {
            outline: none;
            box-shadow: var(--glow-cyan);
        }
        
        .login-modal button {
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
        
        .login-modal button:hover {
            transform: scale(1.05);
            box-shadow: var(--glow-cyan);
        }
        
        .error-msg {
            color: #ff3366;
            text-align: center;
            margin-bottom: 15px;
            text-shadow: 0 0 5px #ff3366;
        }
        
        /* Module Cards */
        .modules {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .module-card {
            background: rgba(10, 10, 15, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid var(--neon-cyan);
            border-radius: 20px;
            padding: 25px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }
        
        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .module-card:hover::before {
            left: 100%;
        }
        
        .module-card:hover {
            transform: translateY(-10px);
            border-color: var(--neon-pink);
            box-shadow: var(--glow-pink);
        }
        
        .module-icon {
            font-size: 3em;
            margin-bottom: 15px;
            filter: drop-shadow(var(--glow-cyan));
        }
        
        .module-title {
            font-family: 'Orbitron', monospace;
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--neon-cyan);
        }
        
        .module-description {
            color: #888;
            line-height: 1.6;
            font-size: 0.9em;
        }
        
        .module-stats {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(0, 255, 255, 0.3);
            font-size: 0.85em;
            color: var(--neon-purple);
            font-weight: bold;
        }
        
        /* Logout Button */
        .logout-btn {
            background: linear-gradient(135deg, #ff3366, #ff0066);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            font-family: 'Orbitron', monospace;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 51, 102, 0.5);
        }
        
        /* Top Performer Widget */
        .top-performer-widget {
            background: linear-gradient(135deg, rgba(0, 255, 255, 0.1), rgba(255, 0, 255, 0.1));
            border-radius: 15px;
            padding: 15px;
            margin-top: 30px;
            text-align: center;
            border: 1px solid var(--neon-yellow);
        }
        
        .top-performer-widget h3 {
            color: var(--neon-yellow);
            font-family: 'Orbitron', monospace;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: rgba(0, 255, 255, 0.5);
            border-top: 1px solid rgba(0, 255, 255, 0.2);
        }
        
        /* Admin Badge */
        .admin-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--neon-pink);
            color: var(--dark-bg);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 100;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        /* Glitch Text Effect */
        .glitch {
            animation: glitchText 0.3s infinite;
        }
        
        @keyframes glitchText {
            0%, 100% { text-shadow: var(--glow-cyan); }
            33% { text-shadow: -2px 0 var(--neon-pink), 2px 0 var(--neon-cyan); }
            66% { text-shadow: 2px 0 var(--neon-pink), -2px 0 var(--neon-cyan); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5em;
            }
            
            .modules {
                grid-template-columns: 1fr;
            }
            
            .module-icon {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>
    <!-- Login Modal - Shows only when not logged in -->
    <?php if (!$is_admin): ?>
    <div class="login-overlay">
        <div class="login-modal">
            <h2>🔐 ACCESS TERMINAL</h2>
            <?php if ($login_error): ?>
                <div class="error-msg">⚠️ <?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="USERNAME" required>
                <input type="password" name="password" placeholder="PASSWORD" required>
                <button type="submit" name="login">AUTHENTICATE</button>
            </form>
            <p style="text-align: center; margin-top: 15px; font-size: 0.8em; color: #666;">
                DEMO: admin / campus2024
            </p>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="header">
            <h1 class="glitch">⚡ CYBERPUNK CAMPUS</h1>
            <p>> SYSTEM v2.0 // ADMIN TERMINAL ACTIVE</p>
            <?php if ($is_admin): ?>
                <a href="?logout=1" class="logout-btn">[LOGOUT]</a>
            <?php endif; ?>
        </div>
        
        <?php if ($is_admin): ?>
        <div class="modules">
            <!-- Parking Module Card -->
            <a href="parking.php" class="module-card">
                <div class="module-icon">🚗</div>
                <div class="module-title">> PARKING PERMIT SYSTEM</div>
                <div class="module-description">
                    Issue digital parking permits for students, staff, and visitors.
                    Real-time tracking of capacity and revenue.
                </div>
                <div class="module-stats">
                    📊 PERMITS: <?php echo $parking_stats['total_permits']; ?>/<?php echo PARKING_CAPACITY; ?> | 
                    💰 REVENUE: R<?php echo number_format($parking_stats['total_revenue'], 2); ?>
                </div>
            </a>
            
            <!-- Library Module Card -->
            <a href="library.php" class="module-card">
                <div class="module-icon">📚</div>
                <div class="module-title">> LIBRARY DATABASE</div>
                <div class="module-description">
                    Borrow tracking system, fine calculation, and user management.
                    Real-time overdue monitoring.
                </div>
                <div class="module-stats">
                    📖 ACTIVE LOANS: <?php echo $library_stats['active_borrowers']; ?> | 
                    💸 TOTAL FINES: R<?php echo number_format($library_stats['total_fines'], 2); ?>
                </div>
            </a>
            
            <!-- Performance Module Card -->
            <a href="performance.php" class="module-card">
                <div class="module-icon">📊</div>
                <div class="module-title">> PERFORMANCE ANALYTICS</div>
                <div class="module-description">
                    Student grade tracking, average calculations, and class statistics.
                    AI-powered performance analysis.
                </div>
                <div class="module-stats">
                    🎓 STUDENTS: <?php echo $performance_stats['student_count']; ?> | 
                    📈 CLASS AVG: <?php echo number_format($performance_stats['class_average'], 1); ?>%
                </div>
            </a>
        </div>
        
        <!-- Top Performer Widget -->
        <?php if ($top_performer): ?>
        <div class="top-performer-widget">
            <h3>🏆 TOP PERFORMER // CLASS VALEDICTORIAN</h3>
            <p style="font-size: 1.2em;">
                <?php echo htmlspecialchars($top_performer['name']); ?> 
                (<?php echo htmlspecialchars($top_performer['student_number']); ?>)
                — AVG: <?php echo number_format($top_performer['average'], 1); ?>% — 
                <?php echo $top_performer['result']; ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Content shown when not logged in -->
        <div class="modules">
            <div class="module-card" style="text-align: center;">
                <div class="module-icon">🔒</div>
                <div class="module-title">> SECURITY PROTOCOL</div>
                <div class="module-description">
                    Authentication required to access system modules.
                    Please log in with valid credentials.
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>[ CAMPUS MANAGEMENT SYSTEM v2.0 ] // ENCRYPTED CONNECTION // <?php echo date('Y'); ?></p>
            <p style="font-size: 0.8em;">> SYSTEM STATUS: <span style="color: #0f0;">ACTIVE</span> // ALL SYSTEMS ONLINE</p>
        </div>
    </div>
    
    <?php if ($is_admin): ?>
    <div class="admin-badge">
        ADMIN ACCESS // <?php echo date('H:i:s'); ?>
    </div>
    <?php endif; ?>
</body>
</html>