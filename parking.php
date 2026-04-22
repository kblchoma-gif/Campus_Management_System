<?php
/**
 * Parking Permit Management Module
 * Cyberpunk Theme - Handles parking permit issuance and tracking
 */

require_once 'config.php';
session_start();

// Check admin authentication
requireAdminLogin();

// Handle form submission for issuing permits
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['issue_permit'])) {
    // Get form data
    $permit_type = $_POST['permit_type'];
    $name = trim($_POST['name']);
    $age = intval($_POST['age']);
    
    // Issue the permit using database function
    $result = issueParkingPermit($permit_type, $name, $age);
    
    if ($result['success']) {
        $message = $result['message'] . " Permit #: " . $result['permit']['permit_number'];
        $message_type = 'success';
    } else {
        $message = $result['message'];
        $message_type = 'error';
    }
}

// Get real-time statistics
$stats = getParkingStats();

// Get all permits for display
$all_permits = getAllParkingPermits();
$recent_permits = array_slice($all_permits, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚗 PARKING SYSTEM // CYBERPUNK CAMPUS</title>
    <style>
        /* Same cyberpunk theme as index.php */
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap');
        
        :root {
            --neon-cyan: #0ff;
            --neon-pink: #f0f;
            --neon-purple: #b300ff;
            --dark-bg: #0a0a0f;
            --darker-bg: #050507;
            --glow-cyan: 0 0 10px rgba(0, 255, 255, 0.5);
            --glow-pink: 0 0 10px rgba(255, 0, 255, 0.5);
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
            box-shadow: var(--glow-cyan);
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
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
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
            border: 1px solid #0f0;
            color: #0f0;
        }
        
        .error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #f00;
            color: #f66;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-item {
            background: rgba(0, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid rgba(0, 255, 255, 0.3);
        }
        
        .stat-label {
            color: var(--neon-purple);
            font-size: 0.85em;
            margin-bottom: 8px;
        }
        
        .stat-value {
            color: var(--neon-cyan);
            font-size: 1.8em;
            font-weight: bold;
            font-family: 'Orbitron', monospace;
        }
        
        .capacity-bar {
            width: 100%;
            height: 25px;
            background: rgba(0, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            margin: 15px 0;
            border: 1px solid var(--neon-cyan);
        }
        
        .capacity-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--neon-cyan), var(--neon-purple));
            transition: width 0.5s ease;
            border-radius: 15px;
        }
        
        .permit-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .permit-item {
            background: rgba(0, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 3px solid var(--neon-cyan);
        }
        
        .permit-item strong {
            color: var(--neon-pink);
        }
        
        .permit-item small {
            color: #888;
            display: block;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚗 PARKING PERMIT SYSTEM</h1>
            <a href="index.php" class="back-btn">⏎ BACK TO TERMINAL</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                > <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <!-- Issue Permit Form -->
            <div class="card">
                <h2>> ISSUE NEW PERMIT</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>FULL NAME</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>AGE</label>
                        <input type="number" name="age" min="18" max="120" placeholder="Must be 18+" required>
                    </div>
                    <div class="form-group">
                        <label>PERMIT TYPE</label>
                        <select name="permit_type" required>
                            <option value="student">STUDENT - R<?php echo PARKING_PRICE_STUDENT; ?>/month</option>
                            <option value="staff">STAFF - R<?php echo PARKING_PRICE_STAFF; ?>/month</option>
                            <option value="visitor">VISITOR - R<?php echo PARKING_PRICE_VISITOR; ?>/day</option>
                        </select>
                    </div>
                    <button type="submit" name="issue_permit" class="btn">ISSUE PERMIT</button>
                </form>
                
                <div class="capacity-bar">
                    <div class="capacity-fill" style="width: <?php echo ($stats['total_permits'] / PARKING_CAPACITY) * 100; ?>%"></div>
                </div>
                <p style="text-align: center; color: var(--neon-purple);">
                    CAPACITY: <?php echo $stats['total_permits']; ?>/<?php echo PARKING_CAPACITY; ?> SPOTS OCCUPIED
                </p>
            </div>
            
            <!-- Statistics and Recent Permits -->
            <div class="card">
                <h2>> SYSTEM STATISTICS</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">STUDENT PERMITS</div>
                        <div class="stat-value"><?php echo $stats['student_count']; ?></div>
                        <small>REV: R<?php echo number_format($stats['student_revenue'], 2); ?></small>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">STAFF PERMITS</div>
                        <div class="stat-value"><?php echo $stats['staff_count']; ?></div>
                        <small>REV: R<?php echo number_format($stats['staff_revenue'], 2); ?></small>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">VISITOR PERMITS</div>
                        <div class="stat-value"><?php echo $stats['visitor_count']; ?></div>
                        <small>REV: R<?php echo number_format($stats['visitor_revenue'], 2); ?></small>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">TOTAL REVENUE</div>
                        <div class="stat-value">R<?php echo number_format($stats['total_revenue'], 2); ?></div>
                        <small>AVAIL: <?php echo $stats['available_spots']; ?> SPOTS</small>
                    </div>
                </div>
                
                <h2 style="margin-top: 25px;">> RECENT PERMITS</h2>
                <div class="permit-list">
                    <?php foreach ($recent_permits as $permit): ?>
                    <div class="permit-item">
                        <strong><?php echo htmlspecialchars($permit['name']); ?></strong> - 
                        <?php echo strtoupper($permit['type']); ?> PERMIT
                        <br>
                        <small>🔑 #: <?php echo $permit['permit_number']; ?></small>
                        <small>📅 ISSUED: <?php echo date('d/m/Y H:i', strtotime($permit['issue_date'])); ?></small>
                        <small>💰 AMOUNT: R<?php echo number_format($permit['price'], 2); ?></small>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recent_permits)): ?>
                    <p style="text-align: center; color: #666; padding: 40px;">
                        > NO PERMITS ISSUED YET
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>