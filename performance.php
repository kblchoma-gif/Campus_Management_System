<?php
/**
 * Student Performance Analytics Module
 * Cyberpunk Theme - Handles student grades and performance tracking
 */

require_once 'config.php';
session_start();

// Check admin authentication
requireAdminLogin();

$message = '';
$message_type = '';
$validation_errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    // Get student information
    $name = trim($_POST['name']);
    $student_number = trim($_POST['student_number']);
    
    // Collect 6 marks
    $marks = [
        floatval($_POST['mark1']),
        floatval($_POST['mark2']),
        floatval($_POST['mark3']),
        floatval($_POST['mark4']),
        floatval($_POST['mark5']),
        floatval($_POST['mark6'])
    ];
    
    // Validate marks
    $validation_errors = validateMarks($marks);
    
    if (empty($validation_errors)) {
        $result = addStudent($name, $student_number, $marks);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'error';
    } else {
        $message = "Validation errors detected.";
        $message_type = 'error';
    }
}

// Get performance data
$stats = getClassStatistics();
$top_student = getTopPerformer();
$students = getAllStudents();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 PERFORMANCE ANALYTICS // CYBERPUNK CAMPUS</title>
    <style>
        /* Cyberpunk theme styles */
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap');
        
        :root {
            --neon-cyan: #0ff;
            --neon-pink: #f0f;
            --neon-purple: #b300ff;
            --neon-green: #0f0;
            --neon-yellow: #ff0;
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
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
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
        
        .form-group input {
            width: 100%;
            padding: 12px;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            border-radius: 10px;
            color: var(--neon-cyan);
            font-family: 'Share Tech Mono', monospace;
            font-size: 1em;
        }
        
        .form-group input.error {
            border-color: #ff3366;
            box-shadow: 0 0 10px rgba(255, 51, 102, 0.5);
        }
        
        .form-group input:focus {
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
        
        .error-list {
            background: rgba(255, 0, 0, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f00;
        }
        
        .error-list li {
            margin-left: 20px;
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
        
        .stat-item.distinction {
            border-top: 3px solid var(--neon-green);
        }
        
        .stat-item.pass {
            border-top: 3px solid var(--neon-yellow);
        }
        
        .stat-item.fail {
            border-top: 3px solid #ff3366;
        }
        
        .top-performer {
            background: linear-gradient(135deg, rgba(255, 255, 0, 0.2), rgba(255, 0, 255, 0.2));
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            text-align: center;
            border: 1px solid var(--neon-yellow);
        }
        
        .top-performer h3 {
            color: var(--neon-yellow);
            margin-bottom: 10px;
        }
        
        .marks-input-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .student-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .student-item {
            background: rgba(0, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid var(--neon-cyan);
        }
        
        .student-item.Distinction {
            border-left-color: var(--neon-green);
        }
        
        .student-item.Pass {
            border-left-color: var(--neon-yellow);
        }
        
        .student-item.Fail {
            border-left-color: #ff3366;
        }
        
        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .student-name {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--neon-pink);
        }
        
        .student-average {
            font-size: 1.5em;
            font-weight: bold;
            font-family: 'Orbitron', monospace;
        }
        
        .student-result {
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.85em;
        }
        
        .result-Distinction {
            background: var(--neon-green);
            color: var(--dark-bg);
        }
        
        .result-Pass {
            background: var(--neon-yellow);
            color: var(--dark-bg);
        }
        
        .result-Fail {
            background: #ff3366;
            color: white;
        }
        
        .marks-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-top: 10px;
        }
        
        .mark-item {
            background: rgba(0, 255, 255, 0.1);
            padding: 5px;
            text-align: center;
            border-radius: 5px;
            font-size: 0.85em;
            border: 1px solid rgba(0, 255, 255, 0.3);
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .marks-input-grid {
                grid-template-columns: 1fr;
            }
            
            .marks-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 PERFORMANCE ANALYTICS</h1>
            <a href="index.php" class="back-btn">⏎ BACK TO TERMINAL</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                > <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($validation_errors)): ?>
            <div class="error-list">
                <strong>> VALIDATION ERRORS:</strong>
                <ul>
                    <?php foreach ($validation_errors as $error): ?>
                    <li>⚠️ <?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="content">
            <!-- Add Student Form -->
            <div class="card">
                <h2>> REGISTER STUDENT RECORD</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>STUDENT NAME</label>
                        <input type="text" name="name" placeholder="Full name" required
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>STUDENT NUMBER</label>
                        <input type="text" name="student_number" placeholder="STU-2024-XXXX" required
                               value="<?php echo isset($_POST['student_number']) ? htmlspecialchars($_POST['student_number']) : ''; ?>">
                    </div>
                    
                    <h3 style="margin: 20px 0 10px; color: var(--neon-purple);">>> ENTER 6 MARKS (0-100)</h3>
                    <div class="marks-input-grid">
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="form-group">
                            <label>MARK <?php echo $i; ?></label>
                            <input type="number" name="mark<?php echo $i; ?>" min="0" max="100" step="0.5" required
                                   class="<?php echo (!empty($validation_errors)) ? 'error' : ''; ?>"
                                   value="<?php echo isset($_POST['mark'.$i]) ? htmlspecialchars($_POST['mark'.$i]) : ''; ?>">
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <button type="submit" name="add_student" class="btn">COMMIT RECORD</button>
                </form>
            </div>
            
            <!-- Statistics and Student List -->
            <div class="card">
                <h2>> CLASS ANALYTICS</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">TOTAL STUDENTS</div>
                        <div class="stat-value"><?php echo $stats['student_count']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">CLASS AVERAGE</div>
                        <div class="stat-value"><?php echo number_format($stats['class_average'], 1); ?>%</div>
                    </div>
                    <div class="stat-item distinction">
                        <div class="stat-label">🏆 DISTINCTIONS</div>
                        <div class="stat-value"><?php echo $stats['distinctions']; ?></div>
                    </div>
                    <div class="stat-item pass">
                        <div class="stat-label">✅ PASSES</div>
                        <div class="stat-value"><?php echo $stats['passes']; ?></div>
                    </div>
                    <div class="stat-item fail">
                        <div class="stat-label">❌ FAILS</div>
                        <div class="stat-value"><?php echo $stats['fails']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">HIGHEST AVG</div>
                        <div class="stat-value"><?php echo number_format($stats['highest_average'], 1); ?>%</div>
                    </div>
                </div>
                
                <?php if ($top_student): ?>
                <div class="top-performer">
                    <h3>> TOP PERFORMER // VALEDICTORIAN</h3>
                    <p><strong><?php echo htmlspecialchars($top_student['name']); ?></strong></p>
                    <p>ID: <?php echo htmlspecialchars($top_student['student_number']); ?></p>
                    <p style="font-size: 1.2em; margin-top: 10px;">
                        AVG: <?php echo number_format($top_student['average'], 1); ?>% - 
                        <?php echo $top_student['result']; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <h2 style="margin-top: 30px;">>> STUDENT DATABASE</h2>
                <div class="student-list">
                    <?php foreach ($students as $student): ?>
                    <div class="student-item <?php echo $student['result']; ?>">
                        <div class="student-header">
                            <div>
                                <span class="student-name"><?php echo htmlspecialchars($student['name']); ?></span>
                                <span style="color: #888; font-size: 0.85em;"> (<?php echo htmlspecialchars($student['student_number']); ?>)</span>
                            </div>
                            <span class="student-average"><?php echo number_format($student['average'], 1); ?>%</span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span class="student-result result-<?php echo $student['result']; ?>">
                                <?php echo $student['result']; ?>
                            </span>
                        </div>
                        
                        <div class="marks-grid">
                            <?php foreach ($student['marks'] as $mark): ?>
                            <div class="mark-item"><?php echo number_format($mark, 1); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($students)): ?>
                    <p style="text-align: center; color: #666; padding: 40px;">
                        > NO STUDENT RECORDS FOUND
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>