<?php
/**
 * ============================================
 * TEST SCRIPT: Integration Tests for Database Functions
 * File: test_integration_database.php
 * ============================================
 * 
 * Target Functions: addStudent(), getAllStudents(), getClassStatistics(),
 *                   addLibraryUser(), borrowBook(), returnBook(),
 *                   issueParkingPermit(), getParkingStats()
 * Database Required: YES (campus_management)
 * Execution Method: php test_integration_database.php
 * 
 * WARNING: This script adds and potentially removes test data.
 * Run in a development environment only.
 */

// Load the core application configuration and function definitions
require_once 'config.php';

// Initialize telemetry accumulators for aggregate statistical reporting
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;
$test_data_cleanup = [];

// Buffer all output for structured rendering after test execution completes
ob_start();

/**
 * Integration test recording and rendering function
 */
function recordIntegrationTest($test_id, $description, $expected_success, $actual_result) {
    global $total_tests, $passed_tests, $failed_tests;
    $total_tests++;
    
    $actual_success = $actual_result['success'];
    $passed = ($expected_success === $actual_success);
    
    $status_class = $passed ? 'pass' : 'fail';
    $status_icon = $passed ? '&#10004;' : '&#10008;';
    
    echo '<div class="test-case ' . $status_class . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon . '</span>';
    echo '<span class="test-id">' . $test_id . '</span>';
    echo '<span class="test-description">' . $description . '</span>';
    echo '<span class="test-badge ' . $status_class . '">' . strtoupper($status_class) . '</span>';
    echo '</div>';
    echo '<div class="test-message">' . $actual_result['message'] . '</div>';
    
    if (!$passed) {
        echo '<div class="test-details">';
        echo '<div class="detail-row"><span class="label">Expected success:</span> <code>' . var_export($expected_success, true) . '</code></div>';
        echo '<div class="detail-row"><span class="label">Actual success:</span> <code>' . var_export($actual_success, true) . '</code></div>';
        echo '</div>';
        $failed_tests++;
    } else {
        $passed_tests++;
    }
    
    echo '</div>';
    
    return $actual_result;
}

// =============================================
// TEST SUITE 1: Student Performance Module
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-student">';
echo '<div class="suite-number">SUITE 1</div>';
echo '<div class="suite-title">Student Performance Module</div>';
echo '<div class="suite-subtitle">Insertion, retrieval, duplicate prevention, and statistics calculation</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-INT-01
 */
$student_name = "Test Student Alpha";
$student_number = "STU-TEST-" . uniqid();
$marks = [75, 80, 85, 90, 95, 88];
$result = addStudent($student_name, $student_number, $marks);
recordIntegrationTest('TC-INT-01', 'Add valid student with marks', true, $result);

/*
 * TEST CASE: TC-INT-02
 */
$result = addStudent($student_name, $student_number, $marks);
recordIntegrationTest('TC-INT-02', 'Add duplicate student number (should fail)', false, $result);

/*
 * TEST CASE: TC-INT-03
 */
$student_name2 = "Test Student Beta";
$student_number2 = "STU-TEST-" . uniqid();
$marks2 = [95, 96, 94, 97, 98, 93];
$result = addStudent($student_name2, $student_number2, $marks2);
recordIntegrationTest('TC-INT-03', 'Add second student (Distinction marks)', true, $result);

/*
 * TEST CASE: TC-INT-04
 */
$student_name3 = "Test Student Gamma";
$student_number3 = "STU-TEST-" . uniqid();
$marks3 = [45, 42, 48, 40, 50, 44];
$result = addStudent($student_name3, $student_number3, $marks3);
recordIntegrationTest('TC-INT-04', 'Add third student (Fail marks)', true, $result);

/*
 * TEST CASE: TC-INT-05
 */
$all_students = getAllStudents();
$student_count = count($all_students);
$passed_count_check = ($student_count >= 3);
$status_class5 = $passed_count_check ? 'pass' : 'fail';
$status_icon5 = $passed_count_check ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class5 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon5 . '</span>';
echo '<span class="test-id">TC-INT-05</span>';
echo '<span class="test-description">Retrieve all students (count >= 3)</span>';
echo '<span class="test-badge ' . $status_class5 . '">' . strtoupper($status_class5) . '</span>';
echo '</div>';
echo '<div class="test-message">Student count: ' . $student_count . '</div>';
echo '</div>';
$total_tests++;
if ($passed_count_check) $passed_tests++; else $failed_tests++;

/*
 * TEST CASE: TC-INT-06
 */
$stats = getClassStatistics();
$dist_ok = ($stats['distinctions'] >= 1);
$pass_ok = ($stats['passes'] >= 1);
$fail_ok = ($stats['fails'] >= 1);
$stats_pass = ($dist_ok && $pass_ok && $fail_ok);
$status_class6 = $stats_pass ? 'pass' : 'fail';
$status_icon6 = $stats_pass ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class6 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon6 . '</span>';
echo '<span class="test-id">TC-INT-06</span>';
echo '<span class="test-description">Class statistics calculation</span>';
echo '<span class="test-badge ' . $status_class6 . '">' . strtoupper($status_class6) . '</span>';
echo '</div>';
echo '<div class="test-message">Distinctions: ' . $stats['distinctions'] . ' | Passes: ' . $stats['passes'] . ' | Fails: ' . $stats['fails'] . '</div>';
echo '</div>';
$total_tests++;
if ($stats_pass) $passed_tests++; else $failed_tests++;

/*
 * TEST CASE: TC-INT-07
 */
$top = getTopPerformer();
$top_correct = ($top && $top['name'] === $student_name2 && $top['average'] > 95);
$status_class7 = $top_correct ? 'pass' : 'fail';
$status_icon7 = $top_correct ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class7 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon7 . '</span>';
echo '<span class="test-id">TC-INT-07</span>';
echo '<span class="test-description">Top performer identification</span>';
echo '<span class="test-badge ' . $status_class7 . '">' . strtoupper($status_class7) . '</span>';
echo '</div>';
echo '<div class="test-message">Top Performer: ' . $top['name'] . ' - ' . $top['average'] . '%</div>';
echo '</div>';
$total_tests++;
if ($top_correct) $passed_tests++; else $failed_tests++;

echo '</div></div>';

// =============================================
// TEST SUITE 2: Library Module
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-library">';
echo '<div class="suite-number">SUITE 2</div>';
echo '<div class="suite-title">Library Module</div>';
echo '<div class="suite-subtitle">Patron registration, book borrowing, fine calculation, and return workflow</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-INT-08
 */
$patron_name = "Test Patron Delta";
$patron_email = "delta@campus.test";
$result = addLibraryUser($patron_name, $patron_email);
$patron_id = $result['user_id'] ?? null;
recordIntegrationTest('TC-INT-08', 'Add new library patron', true, $result);

/*
 * TEST CASE: TC-INT-09
 */
$book_title = "Introduction to Software Testing";
$category = "textbook";
$due_date = date('Y-m-d', strtotime('+14 days'));
$result = borrowBook($patron_id, $book_title, $category, $due_date);
$first_book_uid = $result['borrowing']['book_uid'] ?? null;
recordIntegrationTest('TC-INT-09', 'Borrow book for patron (valid, no fines)', true, $result);

/*
 * TEST CASE: TC-INT-10
 */
$result = borrowBook(99999, "Test Book", "journal", $due_date);
recordIntegrationTest('TC-INT-10', 'Borrow book for non-existent user (should fail)', false, $result);

/*
 * TEST CASE: TC-INT-11
 */
$past_due_date = date('Y-m-d', strtotime('-3 days'));
$result = borrowBook($patron_id, "Overdue Test Book", "textbook", $past_due_date);
$overdue_book_uid = $result['borrowing']['book_uid'] ?? null;

if ($overdue_book_uid) {
    $result = returnBook($patron_id, $overdue_book_uid);
    $fine_correct = ($result['success'] && $result['fine'] >= 15);
    $status_class11 = $fine_correct ? 'pass' : 'fail';
    $status_icon11 = $fine_correct ? '&#10004;' : '&#10008;';
    echo '<div class="test-case ' . $status_class11 . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon11 . '</span>';
    echo '<span class="test-id">TC-INT-11</span>';
    echo '<span class="test-description">Return overdue book with fine</span>';
    echo '<span class="test-badge ' . $status_class11 . '">' . strtoupper($status_class11) . '</span>';
    echo '</div>';
    echo '<div class="test-message">' . $result['message'] . ' | Fine Assessed: <strong>R' . $result['fine'] . '</strong></div>';
    echo '</div>';
    $total_tests++;
    if ($fine_correct) $passed_tests++; else $failed_tests++;
}

/*
 * TEST CASE: TC-INT-12
 */
$result = returnBook($patron_id, "BK-NONEXISTENT-XYZ");
recordIntegrationTest('TC-INT-12', 'Return non-existent book (should fail)', false, $result);

echo '</div></div>';

// =============================================
// TEST SUITE 3: Parking Module
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-parking">';
echo '<div class="suite-number">SUITE 3</div>';
echo '<div class="suite-title">Parking Module</div>';
echo '<div class="suite-subtitle">Permit issuance, age validation, capacity tracking, and revenue calculation</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-INT-13
 */
$result = issueParkingPermit('student', 'Parking Test Student', 21);
recordIntegrationTest('TC-INT-13', 'Issue valid student permit (age 21)', true, $result);

/*
 * TEST CASE: TC-INT-14
 */
$result = issueParkingPermit('student', 'Underage Applicant', 17);
recordIntegrationTest('TC-INT-14', 'Issue permit to minor (age 17, should fail)', false, $result);

/*
 * TEST CASE: TC-INT-15
 */
$result = issueParkingPermit('staff', 'Staff Member Test', 35);
$price_correct = ($result['success'] && $result['permit']['price'] == 750);
$status_class15 = $price_correct ? 'pass' : 'fail';
$status_icon15 = $price_correct ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class15 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon15 . '</span>';
echo '<span class="test-id">TC-INT-15</span>';
echo '<span class="test-description">Issue staff permit with correct price</span>';
echo '<span class="test-badge ' . $status_class15 . '">' . strtoupper($status_class15) . '</span>';
echo '</div>';
echo '<div class="test-message">Permit Price: <strong>R' . ($result['permit']['price'] ?? 'N/A') . '</strong></div>';
echo '</div>';
$total_tests++;
if ($price_correct) $passed_tests++; else $failed_tests++;

/*
 * TEST CASE: TC-INT-16
 */
$stats = getParkingStats();
$stats_ok = ($stats['total_permits'] >= 2 && $stats['total_revenue'] > 0);
$status_class16 = $stats_ok ? 'pass' : 'fail';
$status_icon16 = $stats_ok ? '&#10004;' : '&#10008;';
echo '<div class="test-case ' . $status_class16 . '">';
echo '<div class="test-header">';
echo '<span class="test-icon">' . $status_icon16 . '</span>';
echo '<span class="test-id">TC-INT-16</span>';
echo '<span class="test-description">Parking statistics calculation</span>';
echo '<span class="test-badge ' . $status_class16 . '">' . strtoupper($status_class16) . '</span>';
echo '</div>';
echo '<div class="test-message">Total Permits: ' . $stats['total_permits'] . ' | Total Revenue: <strong>R' . $stats['total_revenue'] . '</strong></div>';
echo '</div>';
$total_tests++;
if ($stats_ok) $passed_tests++; else $failed_tests++;

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
        
        /* Background subtle grid pattern for depth */
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
        .suite-library { border-left: 4px solid var(--ctos-purple); }
        .suite-parking { border-left: 4px solid var(--ctos-orange); }
        
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
        .suite-library .suite-number { color: var(--ctos-purple); border: 1px solid rgba(165, 94, 234, 0.3); }
        .suite-parking .suite-number { color: var(--ctos-orange); border: 1px solid rgba(255, 159, 67, 0.3); }
        
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
        
        .footer-warning {
            margin-top: 12px;
            padding: 12px 18px;
            background: rgba(255, 159, 67, 0.08);
            border: 1px solid rgba(255, 159, 67, 0.2);
            border-radius: var(--ctos-radius-sm);
            font-size: 12px;
            color: var(--ctos-orange);
            display: inline-block;
            letter-spacing: 0.3px;
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
            <div class="header-title"><strong>CTOS</strong> Integration Test Report</div>
            <div class="header-subtitle">Database Function Validation &mdash; <?php echo date('Y-m-d H:i:s'); ?></div>
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
                <strong>CTOS</strong> Campus Testing &amp; Operations System &bull; Test Execution Complete
            </div>
            <div class="footer-warning">
                &#9888; Test data has been added to the database for verification. It is recommended to review and clean test records after execution.
            </div>
        </div>
        
    </div>
</body>
</html>