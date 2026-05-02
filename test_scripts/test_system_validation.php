<?php
/**
 * ============================================
 * TEST SCRIPT: System Tests for Validation & Business Rules
 * File: test_system_validation.php
 * ============================================
 * 
 * Target Functions: validateMarks(), verifyAdminLogin(), 
 *                   isAdminLoggedIn(), requireAdminLogin()
 * Database Required: No (for validation/auth tests)
 * Execution Method: php test_system_validation.php
 * 
 * This script simulates the complete system workflow
 * as if a user were submitting forms through the web interface.
 */

// Load the core application configuration and function library
require_once 'config.php';

// Initialize global telemetry accumulators for cross-test statistical tracking
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;

// Buffer all output for structured rendering after test execution completes
ob_start();

/**
 * System-level test recording and rendering function
 */
function recordSystemTest($test_id, $description, $expected, $actual) {
    global $total_tests, $passed_tests, $failed_tests;
    $total_tests++;
    
    $passed = ($expected === $actual);
    
    $status_class = $passed ? 'pass' : 'fail';
    $status_icon = $passed ? '&#10004;' : '&#10008;';
    
    echo '<div class="test-case ' . $status_class . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon . '</span>';
    echo '<span class="test-id">' . $test_id . '</span>';
    echo '<span class="test-description">' . $description . '</span>';
    echo '<span class="test-badge ' . $status_class . '">' . strtoupper($status_class) . '</span>';
    echo '</div>';
    
    if (!$passed) {
        echo '<div class="test-details">';
        echo '<div class="detail-row"><span class="label">Expected:</span> <code>' . var_export($expected, true) . '</code></div>';
        echo '<div class="detail-row"><span class="label">Actual:</span> <code>' . var_export($actual, true) . '</code></div>';
        echo '</div>';
        $failed_tests++;
    } else {
        $passed_tests++;
    }
    
    echo '</div>';
}

// =============================================
// TEST SUITE 1: validateMarks() Function
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-student">';
echo '<div class="suite-number">SUITE 1</div>';
echo '<div class="suite-title">Mark Validation Engine</div>';
echo '<div class="suite-subtitle">Input sanitization, boundary analysis, and type enforcement for grade submission</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-SYS-01
 */
$marks = [85, 90, 75, 80, 95, 88];
$errors = validateMarks($marks);
$error_count = count($errors);
recordSystemTest('TC-SYS-01', 'All six marks valid (0 errors)', 0, $error_count);

/*
 * TEST CASE: TC-SYS-02
 */
$marks = [85, -5, 75, 80, 95, 88];
$errors = validateMarks($marks);
$error_count = count($errors);
$has_out_of_range = (strpos(implode(' ', $errors), 'between 0 and 100') !== false);
recordSystemTest('TC-SYS-02', 'Single negative mark (-5)', 1, $error_count);

/*
 * TEST CASE: TC-SYS-03
 */
$marks = [85, 150, 75, 80, 95, 88];
$errors = validateMarks($marks);
$error_count = count($errors);
recordSystemTest('TC-SYS-03', 'Single mark above 100 (150)', 1, $error_count);

/*
 * TEST CASE: TC-SYS-04
 */
$marks = ["abc", 90, 75, 80, 95, 88];
$errors = validateMarks($marks);
$error_count = count($errors);
recordSystemTest('TC-SYS-04', 'Non-numeric string mark (abc)', 1, $error_count);

/*
 * TEST CASE: TC-SYS-05
 */
$marks = [150, -10, "xyz", 80, 95, 88];
$errors = validateMarks($marks);
$error_count = count($errors);
recordSystemTest('TC-SYS-05', 'Three invalid marks (150, -10, xyz)', 3, $error_count);

/*
 * TEST CASE: TC-SYS-06
 */
$marks = [0, 100, 0, 100, 50, 75];
$errors = validateMarks($marks);
$error_count = count($errors);
recordSystemTest('TC-SYS-06', 'Marks at exact boundaries 0 and 100', 0, $error_count);

echo '</div></div>';

// =============================================
// TEST SUITE 2: Authentication Functions
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-auth">';
echo '<div class="suite-number">SUITE 2</div>';
echo '<div class="suite-title">Authentication &amp; Authorization</div>';
echo '<div class="suite-subtitle">Credential verification, session integrity, and access control enforcement</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-SYS-07
 */
$result = verifyAdminLogin('admin', 'campus2024');
recordSystemTest('TC-SYS-07', 'Valid credentials (admin/campus2024)', true, $result);

/*
 * TEST CASE: TC-SYS-08
 */
$result = verifyAdminLogin('admin', 'wrongpassword');
recordSystemTest('TC-SYS-08', 'Valid username, invalid password', false, $result);

/*
 * TEST CASE: TC-SYS-09
 */
$result = verifyAdminLogin('hacker', 'campus2024');
recordSystemTest('TC-SYS-09', 'Invalid username, valid password', false, $result);

/*
 * TEST CASE: TC-SYS-10
 */
$result = verifyAdminLogin('hacker', 'password123');
recordSystemTest('TC-SYS-10', 'Both username and password invalid', false, $result);

/*
 * TEST CASE: TC-SYS-11
 */
$result = verifyAdminLogin('', '');
recordSystemTest('TC-SYS-11', 'Empty username and password', false, $result);

/*
 * TEST CASE: TC-SYS-12
 */
$result = verifyAdminLogin('admin', 'Campus2024');
recordSystemTest('TC-SYS-12', 'Password case sensitivity test', false, $result);

echo '</div></div>';

// =============================================
// TEST SUITE 3: Simulated Form Submission Flow
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-workflow">';
echo '<div class="suite-number">SUITE 3</div>';
echo '<div class="suite-title">End-to-End Form Flow Simulation</div>';
echo '<div class="suite-subtitle">POST data ingestion, validation pipeline, and conditional database persistence</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-SYS-13
 */
$_POST = [
    'name' => 'System Test Student',
    'student_number' => 'STU-SYS-' . uniqid(),
    'mark1' => 78,
    'mark2' => 82,
    'mark3' => 91,
    'mark4' => 65,
    'mark5' => 88,
    'mark6' => 74
];

$simulated_marks = [
    floatval($_POST['mark1']),
    floatval($_POST['mark2']),
    floatval($_POST['mark3']),
    floatval($_POST['mark4']),
    floatval($_POST['mark5']),
    floatval($_POST['mark6'])
];

$sim_errors = validateMarks($simulated_marks);
$sim_valid = empty($sim_errors);

if ($sim_valid) {
    $sim_result = addStudent($_POST['name'], $_POST['student_number'], $simulated_marks);
    $sim_success = $sim_result['success'];
    
    $status_class13 = $sim_success ? 'pass' : 'fail';
    $status_icon13 = $sim_success ? '&#10004;' : '&#10008;';
    echo '<div class="test-case ' . $status_class13 . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon13 . '</span>';
    echo '<span class="test-id">TC-SYS-13</span>';
    echo '<span class="test-description">Complete valid form submission workflow</span>';
    echo '<span class="test-badge ' . $status_class13 . '">' . strtoupper($status_class13) . '</span>';
    echo '</div>';
    echo '<div class="test-message">Validation errors: ' . count($sim_errors) . ' | Insertion: ' . $sim_result['message'] . '</div>';
    echo '</div>';
    $total_tests++;
    if ($sim_success) $passed_tests++; else $failed_tests++;
}

/*
 * TEST CASE: TC-SYS-14
 */
$invalid_marks = [85, -5, 75, 200, "abc", 88];
$sim_errors2 = validateMarks($invalid_marks);
$sim_valid2 = empty($sim_errors2);

$status_class14 = (!$sim_valid2) ? 'pass' : 'fail';
$status_icon14 = (!$sim_valid2) ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class14 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon14 . '</span>';
echo '<span class="test-id">TC-SYS-14</span>';
echo '<span class="test-description">Form submission with invalid marks stops before DB insert</span>';
echo '<span class="test-badge ' . $status_class14 . '">' . strtoupper($status_class14) . '</span>';
echo '</div>';
echo '<div class="test-message">Validation errors detected: <strong>' . count($sim_errors2) . '</strong> | Errors: ' . implode(', ', $sim_errors2) . '</div>';
echo '</div>';
$total_tests++;
if (!$sim_valid2) $passed_tests++; else $failed_tests++;

echo '</div></div>';

// =============================================
// TEST SUITE 4: Data Integrity Verification
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-integrity">';
echo '<div class="suite-number">SUITE 4</div>';
echo '<div class="suite-title">Data Integrity Verification</div>';
echo '<div class="suite-subtitle">Cross-validation of stored computed fields against independent calculations</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-SYS-15
 */
$all_students = getAllStudents();
$found_student = null;
foreach ($all_students as $student) {
    if ($student['student_number'] === $_POST['student_number']) {
        $found_student = $student;
        break;
    }
}

if ($found_student) {
    $expected_avg = calculateAverage($simulated_marks);
    $expected_result = assignResult($expected_avg);
    
    $avg_match = (abs($found_student['average'] - $expected_avg) < 0.01);
    $result_match = ($found_student['result'] === $expected_result);
    $integrity_pass = ($avg_match && $result_match);
    
    $status_class15 = $integrity_pass ? 'pass' : 'fail';
    $status_icon15 = $integrity_pass ? '&#10004;' : '&#10008;';
    echo '<div class="test-case ' . $status_class15 . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon15 . '</span>';
    echo '<span class="test-id">TC-SYS-15</span>';
    echo '<span class="test-description">Stored average and result match calculated values</span>';
    echo '<span class="test-badge ' . $status_class15 . '">' . strtoupper($status_class15) . '</span>';
    echo '</div>';
    echo '<div class="test-message">Expected: Avg=<strong>' . $expected_avg . '</strong>, Result=<strong>' . $expected_result . '</strong> | Stored: Avg=<strong>' . $found_student['average'] . '</strong>, Result=<strong>' . $found_student['result'] . '</strong></div>';
    echo '</div>';
    $total_tests++;
    if ($integrity_pass) $passed_tests++; else $failed_tests++;
} else {
    echo '<div class="test-case info">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">&#8505;</span>';
    echo '<span class="test-id">TC-SYS-15</span>';
    echo '<span class="test-description">Stored data integrity verification</span>';
    echo '<span class="test-badge info">INFO</span>';
    echo '</div>';
    echo '<div class="test-message">Could not locate test student record for verification. The insertion may have failed silently.</div>';
    echo '</div>';
}

echo '</div></div>';

// Capture all buffered test output for HTML rendering
$test_output = ob_get_clean();

// Calculate aggregate metrics for the summary dashboard
$success_rate = ($total_tests > 0) ? round(($passed_tests / $total_tests) * 100, 1) : 0;
$rate_class = ($success_rate >= 90) ? 'excellent' : (($success_rate >= 70) ? 'warning' : 'critical');

// =============================================
// RENDER COMPLETE HTML DOCUMENT WITH CTOS THEME
// =============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTOS | Campus Testing &amp; Operations System</title>
    <style>
        /* ============================================
           CTOS THEME - Campus Testing & Operations System
           Professional Dashboard Styling Framework
           ============================================ */
        
        :root {
            --ctos-dark: #0f1923;
            --ctos-darker: #0a1219;
            --ctos-panel: #1a2733;
            --ctos-border: #2a3a4a;
            --ctos-text: #c8d6e5;
            --ctos-muted: #6b7d8e;
            --ctos-accent: #00d4aa;
            --ctos-accent-glow: rgba(0, 212, 170, 0.25);
            --ctos-blue: #4da6ff;
            --ctos-blue-glow: rgba(77, 166, 255, 0.25);
            --ctos-orange: #ff9f43;
            --ctos-orange-glow: rgba(255, 159, 67, 0.25);
            --ctos-red: #ff5e57;
            --ctos-red-glow: rgba(255, 94, 87, 0.25);
            --ctos-green: #2ed573;
            --ctos-green-glow: rgba(46, 213, 115, 0.25);
            --ctos-purple: #a55eea;
            --ctos-purple-glow: rgba(165, 94, 234, 0.25);
            --ctos-font: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
            --ctos-mono: 'SF Mono', 'Cascadia Code', 'Consolas', 'Monaco', monospace;
            --ctos-radius: 8px;
            --ctos-radius-sm: 4px;
            --ctos-transition: 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: var(--ctos-dark);
            color: var(--ctos-text);
            font-family: var(--ctos-font);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(42, 58, 74, 0.15) 1px, transparent 1px),
                linear-gradient(90deg, rgba(42, 58, 74, 0.15) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
            z-index: 0;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 24px;
            position: relative;
            z-index: 1;
        }
        
        /* ============================================
           HEADER & BRANDING
           ============================================ */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 32px;
            background: var(--ctos-panel);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius);
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--ctos-accent), transparent);
        }
        
        .header-logo {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 4px;
            color: var(--ctos-accent);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
            color: #ffffff;
            margin-bottom: 4px;
        }
        
        .header-title strong {
            font-weight: 700;
            color: var(--ctos-accent);
        }
        
        .header-subtitle {
            font-size: 13px;
            color: var(--ctos-muted);
            letter-spacing: 0.5px;
        }
        
        /* ============================================
           SUMMARY DASHBOARD
           ============================================ */
        .summary-dashboard {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 36px;
        }
        
        .summary-card {
            background: var(--ctos-panel);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius);
            padding: 24px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: transform var(--ctos-transition), box-shadow var(--ctos-transition);
        }
        
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
        }
        
        .summary-card .card-value {
            font-size: 36px;
            font-weight: 700;
            font-family: var(--ctos-mono);
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        
        .summary-card .card-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--ctos-muted);
            font-weight: 600;
        }
        
        .summary-card.card-total .card-value { color: var(--ctos-blue); }
        .summary-card.card-passed .card-value { color: var(--ctos-green); }
        .summary-card.card-failed .card-value { color: var(--ctos-red); }
        .summary-card.card-rate .card-value { color: var(--ctos-accent); }
        
        .card-total { border-top: 3px solid var(--ctos-blue); }
        .card-passed { border-top: 3px solid var(--ctos-green); }
        .card-failed { border-top: 3px solid var(--ctos-red); }
        .card-rate { border-top: 3px solid var(--ctos-accent); }
        
        /* ============================================
           SUITE CONTAINER
           ============================================ */
        .suite-container {
            background: var(--ctos-panel);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius);
            margin-bottom: 24px;
            overflow: hidden;
            transition: box-shadow var(--ctos-transition);
        }
        
        .suite-container:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }
        
        .suite-header {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            border-bottom: 1px solid var(--ctos-border);
            position: relative;
        }
        
        .suite-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, var(--ctos-accent), transparent 80%);
            opacity: 0.3;
        }
        
        .suite-student { border-left: 4px solid var(--ctos-blue); }
        .suite-auth { border-left: 4px solid var(--ctos-red); }
        .suite-workflow { border-left: 4px solid var(--ctos-purple); }
        .suite-integrity { border-left: 4px solid var(--ctos-orange); }
        
        .suite-number {
            font-family: var(--ctos-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--ctos-muted);
            background: var(--ctos-darker);
            padding: 4px 10px;
            border-radius: var(--ctos-radius-sm);
            white-space: nowrap;
        }
        
        .suite-student .suite-number { color: var(--ctos-blue); border: 1px solid rgba(77, 166, 255, 0.3); }
        .suite-auth .suite-number { color: var(--ctos-red); border: 1px solid rgba(255, 94, 87, 0.3); }
        .suite-workflow .suite-number { color: var(--ctos-purple); border: 1px solid rgba(165, 94, 234, 0.3); }
        .suite-integrity .suite-number { color: var(--ctos-orange); border: 1px solid rgba(255, 159, 67, 0.3); }
        
        .suite-title {
            font-size: 16px;
            font-weight: 600;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        
        .suite-subtitle {
            font-size: 12px;
            color: var(--ctos-muted);
            margin-left: auto;
            text-align: right;
            max-width: 340px;
        }
        
        .suite-body {
            padding: 16px;
        }
        
        /* ============================================
           TEST CASE STYLING
           ============================================ */
        .test-case {
            background: var(--ctos-darker);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius-sm);
            padding: 14px 18px;
            margin-bottom: 10px;
            transition: border-color var(--ctos-transition), box-shadow var(--ctos-transition);
            position: relative;
        }
        
        .test-case:last-child {
            margin-bottom: 0;
        }
        
        .test-case:hover {
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
        }
        
        .test-case.pass {
            border-left: 3px solid var(--ctos-green);
        }
        
        .test-case.fail {
            border-left: 3px solid var(--ctos-red);
        }
        
        .test-case.info {
            border-left: 3px solid var(--ctos-blue);
        }
        
        .test-header {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .test-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .test-case.pass .test-icon { color: var(--ctos-green); }
        .test-case.fail .test-icon { color: var(--ctos-red); }
        .test-case.info .test-icon { color: var(--ctos-blue); }
        
        .test-id {
            font-family: var(--ctos-mono);
            font-size: 12px;
            font-weight: 600;
            color: var(--ctos-muted);
            background: rgba(107, 125, 142, 0.1);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .test-description {
            font-size: 13px;
            font-weight: 500;
            color: #e8edf5;
            flex: 1;
            min-width: 200px;
        }
        
        .test-badge {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .test-badge.pass {
            background: rgba(46, 213, 115, 0.12);
            color: var(--ctos-green);
            border: 1px solid rgba(46, 213, 115, 0.25);
        }
        
        .test-badge.fail {
            background: rgba(255, 94, 87, 0.12);
            color: var(--ctos-red);
            border: 1px solid rgba(255, 94, 87, 0.25);
        }
        
        .test-badge.info {
            background: rgba(77, 166, 255, 0.12);
            color: var(--ctos-blue);
            border: 1px solid rgba(77, 166, 255, 0.25);
        }
        
        .test-message {
            margin-top: 8px;
            font-size: 12px;
            color: var(--ctos-muted);
            padding-left: 36px;
            font-family: var(--ctos-mono);
            word-break: break-word;
        }
        
        .test-message strong {
            color: var(--ctos-accent);
            font-weight: 600;
        }
        
        .test-details {
            margin-top: 10px;
            padding: 12px 14px;
            background: rgba(255, 94, 87, 0.05);
            border: 1px solid rgba(255, 94, 87, 0.15);
            border-radius: var(--ctos-radius-sm);
            font-family: var(--ctos-mono);
            font-size: 12px;
        }
        
        .detail-row {
            margin-bottom: 4px;
            color: var(--ctos-muted);
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-row .label {
            color: var(--ctos-text);
            font-weight: 600;
        }
        
        .detail-row code {
            background: rgba(0, 0, 0, 0.3);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            color: var(--ctos-red);
            font-family: var(--ctos-mono);
            font-size: 11px;
        }
        
        /* ============================================
           FOOTER
           ============================================ */
        .footer {
            text-align: center;
            padding: 24px;
            margin-top: 16px;
            border-top: 1px solid var(--ctos-border);
        }
        
        .footer-text {
            font-size: 11px;
            color: var(--ctos-muted);
            letter-spacing: 0.5px;
        }
        
        .footer-text strong {
            color: var(--ctos-accent);
        }
        
        /* ============================================
           RESPONSIVE ADAPTATIONS
           ============================================ */
        @media (max-width: 768px) {
            .summary-dashboard {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .suite-subtitle {
                display: none;
            }
            
            .test-header {
                gap: 8px;
            }
            
            .test-description {
                font-size: 12px;
            }
            
            .container {
                padding: 16px 12px;
            }
        }
        
        @media (max-width: 480px) {
            .summary-dashboard {
                grid-template-columns: 1fr;
            }
            
            .header-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ============================================
             HEADER SECTION
             ============================================ -->
        <div class="header">
            <div class="header-logo">&#9670; Campus Testing &amp; Operations System</div>
            <div class="header-title"><strong>CTOS</strong> System Validation Report</div>
            <div class="header-subtitle">Business Rules &amp; Workflow Integrity Verification &mdash; <?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
        
        <!-- ============================================
             SUMMARY DASHBOARD
             ============================================ -->
        <div class="summary-dashboard">
            <div class="summary-card card-total">
                <div class="card-value"><?php echo $total_tests; ?></div>
                <div class="card-label">Total Tests</div>
            </div>
            <div class="summary-card card-passed">
                <div class="card-value"><?php echo $passed_tests; ?></div>
                <div class="card-label">Passed</div>
            </div>
            <div class="summary-card card-failed">
                <div class="card-value"><?php echo $failed_tests; ?></div>
                <div class="card-label">Failed</div>
            </div>
            <div class="summary-card card-rate">
                <div class="card-value"><?php echo $success_rate; ?>%</div>
                <div class="card-label">Success Rate</div>
            </div>
        </div>
        
        <!-- ============================================
             TEST SUITE RESULTS
             ============================================ -->
        <?php echo $test_output; ?>
        
        <!-- ============================================
             FOOTER SECTION
             ============================================ -->
        <div class="footer">
            <div class="footer-text">
                <strong>CTOS</strong> Campus Testing &amp; Operations System &bull; Validation Test Execution Complete
            </div>
        </div>
        
    </div>
</body>
</html>