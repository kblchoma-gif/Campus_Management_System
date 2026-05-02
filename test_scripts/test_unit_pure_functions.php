<?php
/**
 * ============================================
 * TEST SCRIPT: Unit Tests for Pure Logic Functions
 * File: test_unit_pure_functions.php
 * ============================================
 * 
 * Target Functions: calculateAverage(), assignResult()
 * Database Required: No
 * Execution Method: php test_unit_pure_functions.php
 * 
 * This script isolates and tests the mathematical logic
 * and classification rules independently from the database.
 */

// Include the actual config file to get the real functions
require_once 'config.php';

// Initialize test tracking variables
$total_tests = 0;
$passed_tests = 0;
$failed_tests = 0;
$test_results = [];

// Buffer all output for structured rendering after test execution completes
ob_start();

/**
 * Helper function to record and display test results
 */
function recordTest($test_id, $description, $expected, $actual) {
    global $total_tests, $passed_tests, $failed_tests, $test_results;
    $total_tests++;
    $passed = ($expected === $actual);
    
    $result = [
        'id' => $test_id,
        'description' => $description,
        'expected' => $expected,
        'actual' => $actual,
        'status' => $passed ? 'PASS' : 'FAIL'
    ];
    
    if ($passed) {
        $passed_tests++;
    } else {
        $failed_tests++;
    }
    
    $test_results[] = $result;
    return $result;
}

// =============================================
// TEST SUITE 1: calculateAverage() Function
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-average">';
echo '<div class="suite-number">SUITE 1</div>';
echo '<div class="suite-title">calculateAverage() — Arithmetic Computation Engine</div>';
echo '<div class="suite-subtitle">Input filtering, boundary value analysis, and floating-point precision verification</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-CA-01
 */
$marks = [80, 70, 90, 85, 75, 95];
$result = calculateAverage($marks);
$expected = (80 + 70 + 90 + 85 + 75 + 95) / 6;
recordTest('TC-CA-01', 'Six valid marks (happy path)', $expected, $result);

/*
 * TEST CASE: TC-CA-02
 */
$marks = [100, 100, 100, 100, 100, 100];
$result = calculateAverage($marks);
$expected = 100.0;
recordTest('TC-CA-02', 'All marks at upper boundary (100)', $expected, $result);

/*
 * TEST CASE: TC-CA-03
 */
$marks = [0, 0, 0, 0, 0, 0];
$result = calculateAverage($marks);
$expected = 0.0;
recordTest('TC-CA-03', 'All marks at lower boundary (0)', $expected, $result);

/*
 * TEST CASE: TC-CA-04
 */
$marks = [99.5, 88.5, 77.5, 66.5, 55.5, 44.5];
$result = calculateAverage($marks);
$expected = (99.5 + 88.5 + 77.5 + 66.5 + 55.5 + 44.5) / 6;
recordTest('TC-CA-04', 'Marks with decimal values', round($expected, 10), round($result, 10));

/*
 * TEST CASE: TC-CA-05
 */
$marks = [90, "abc", 80, "def", 70, null];
$result = calculateAverage($marks);
$expected = (90 + 80 + 70) / 3;
recordTest('TC-CA-05', 'Mixed valid marks with non-numeric strings', $expected, $result);

/*
 * TEST CASE: TC-CA-06
 */
$marks = [120, -10, 90, 80, 105, -5];
$result = calculateAverage($marks);
$expected = (90 + 80) / 2;
recordTest('TC-CA-06', 'Marks with out-of-range values (>100, <0)', $expected, $result);

/*
 * TEST CASE: TC-CA-07
 */
$marks = ["abc", -50, 200, null, "xyz", 500];
$result = calculateAverage($marks);
$expected = 0.0;
recordTest('TC-CA-07', 'All six marks invalid', $expected, $result);

/*
 * TEST CASE: TC-CA-08
 */
$marks = [];
$result = calculateAverage($marks);
$expected = 0.0;
recordTest('TC-CA-08', 'Empty marks array', $expected, $result);

echo '</div></div>';

// =============================================
// TEST SUITE 2: assignResult() Function
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-classify">';
echo '<div class="suite-number">SUITE 2</div>';
echo '<div class="suite-title">assignResult() — Classification Threshold Engine</div>';
echo '<div class="suite-subtitle">Boundary value analysis at Distinction (75) and Pass (50) classification thresholds</div>';
echo '</div>';
echo '<div class="suite-body">';

/*
 * TEST CASE: TC-AR-01
 */
$result = assignResult(92.0);
recordTest('TC-AR-01', 'Average 92.0 (clear Distinction)', 'Distinction', $result);

/*
 * TEST CASE: TC-AR-02
 */
$result = assignResult(75.0);
recordTest('TC-AR-02', 'Boundary: Average exactly 75.0', 'Distinction', $result);

/*
 * TEST CASE: TC-AR-03
 */
$result = assignResult(74.9);
recordTest('TC-AR-03', 'Boundary: Average 74.9 (just below Distinction)', 'Pass', $result);

/*
 * TEST CASE: TC-AR-04
 */
$result = assignResult(62.3);
recordTest('TC-AR-04', 'Average 62.3 (mid-range Pass)', 'Pass', $result);

/*
 * TEST CASE: TC-AR-05
 */
$result = assignResult(50.0);
recordTest('TC-AR-05', 'Boundary: Average exactly 50.0', 'Pass', $result);

/*
 * TEST CASE: TC-AR-06
 */
$result = assignResult(49.9);
recordTest('TC-AR-06', 'Boundary: Average 49.9 (just below Pass)', 'Fail', $result);

/*
 * TEST CASE: TC-AR-07
 */
$result = assignResult(0.0);
recordTest('TC-AR-07', 'Average 0.0 (extreme Fail)', 'Fail', $result);

/*
 * TEST CASE: TC-AR-08
 */
$result = assignResult(100.0);
recordTest('TC-AR-08', 'Average 100.0 (perfect score)', 'Distinction', $result);

echo '</div></div>';

// Now render all individual test results as HTML
foreach ($test_results as $test) {
    $status_class = ($test['status'] === 'PASS') ? 'pass' : 'fail';
    $status_icon = ($test['status'] === 'PASS') ? '&#10004;' : '&#10008;';
    
    echo '<div class="test-case ' . $status_class . '">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">' . $status_icon . '</span>';
    echo '<span class="test-id">' . $test['id'] . '</span>';
    echo '<span class="test-description">' . $test['description'] . '</span>';
    echo '<span class="test-badge ' . $status_class . '">' . $test['status'] . '</span>';
    echo '</div>';
    
    if ($test['status'] === 'FAIL') {
        echo '<div class="test-details">';
        echo '<div class="detail-row"><span class="label">Expected:</span> <code>' . var_export($test['expected'], true) . '</code></div>';
        echo '<div class="detail-row"><span class="label">Actual:</span> <code>' . var_export($test['actual'], true) . '</code></div>';
        echo '</div>';
    }
    
    echo '</div>';
}

// Capture all buffered test output for HTML rendering
$test_output = ob_get_clean();

// Calculate aggregate metrics for the summary dashboard
$success_rate = ($total_tests > 0) ? round(($passed_tests / $total_tests) * 100, 1) : 0;

// =============================================
// RENDER COMPLETE HTML DOCUMENT WITH CTOS THEME
// =============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTOS | Unit Test Results</title>
    <style>
        :root {
            --ctos-dark: #0f1923;
            --ctos-darker: #0a1219;
            --ctos-panel: #1a2733;
            --ctos-border: #2a3a4a;
            --ctos-text: #c8d6e5;
            --ctos-muted: #6b7d8e;
            --ctos-accent: #00d4aa;
            --ctos-blue: #4da6ff;
            --ctos-orange: #ff9f43;
            --ctos-red: #ff5e57;
            --ctos-green: #2ed573;
            --ctos-purple: #a55eea;
            --ctos-font: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
            --ctos-mono: 'SF Mono', 'Cascadia Code', 'Consolas', 'Monaco', monospace;
            --ctos-radius: 8px;
            --ctos-radius-sm: 4px;
            --ctos-transition: 0.2s ease;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: var(--ctos-dark);
            color: var(--ctos-text);
            font-family: var(--ctos-font);
            line-height: 1.6;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: 
                linear-gradient(rgba(42,58,74,0.15) 1px, transparent 1px),
                linear-gradient(90deg, rgba(42,58,74,0.15) 1px, transparent 1px);
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
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 32px;
            background: var(--ctos-panel);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius);
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0; left: 50%;
            transform: translateX(-50%);
            width: 60%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--ctos-blue), transparent);
        }
        
        .header-logo {
            font-size: 14px; font-weight: 700;
            letter-spacing: 4px;
            color: var(--ctos-blue);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        
        .header-title {
            font-size: 28px; font-weight: 300;
            letter-spacing: 1px;
            color: #ffffff;
            margin-bottom: 4px;
        }
        
        .header-title strong { font-weight: 700; color: var(--ctos-blue); }
        .header-subtitle { font-size: 13px; color: var(--ctos-muted); }
        
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
            transition: transform var(--ctos-transition), box-shadow var(--ctos-transition);
        }
        
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
        }
        
        .summary-card .card-value {
            font-size: 36px; font-weight: 700;
            font-family: var(--ctos-mono);
            margin-bottom: 4px;
        }
        
        .summary-card .card-label {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--ctos-muted);
            font-weight: 600;
        }
        
        .card-total .card-value { color: var(--ctos-blue); }
        .card-passed .card-value { color: var(--ctos-green); }
        .card-failed .card-value { color: var(--ctos-red); }
        .card-rate .card-value { color: var(--ctos-accent); }
        
        .card-total { border-top: 3px solid var(--ctos-blue); }
        .card-passed { border-top: 3px solid var(--ctos-green); }
        .card-failed { border-top: 3px solid var(--ctos-red); }
        .card-rate { border-top: 3px solid var(--ctos-accent); }
        
        .suite-container {
            background: var(--ctos-panel);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius);
            margin-bottom: 24px;
            overflow: hidden;
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
            bottom: 0; left: 0;
            width: 100%; height: 1px;
            background: linear-gradient(90deg, var(--ctos-accent), transparent 80%);
            opacity: 0.3;
        }
        
        .suite-average { border-left: 4px solid var(--ctos-blue); }
        .suite-classify { border-left: 4px solid var(--ctos-purple); }
        
        .suite-number {
            font-family: var(--ctos-mono);
            font-size: 11px; font-weight: 700;
            letter-spacing: 2px;
            color: var(--ctos-muted);
            background: var(--ctos-darker);
            padding: 4px 10px;
            border-radius: var(--ctos-radius-sm);
            white-space: nowrap;
        }
        
        .suite-average .suite-number { color: var(--ctos-blue); border: 1px solid rgba(77,166,255,0.3); }
        .suite-classify .suite-number { color: var(--ctos-purple); border: 1px solid rgba(165,94,234,0.3); }
        
        .suite-title { font-size: 16px; font-weight: 600; color: #ffffff; }
        .suite-subtitle { font-size: 12px; color: var(--ctos-muted); margin-left: auto; text-align: right; max-width: 340px; }
        .suite-body { padding: 16px; }
        
        .test-case {
            background: var(--ctos-darker);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius-sm);
            padding: 14px 18px;
            margin-bottom: 10px;
            transition: border-color var(--ctos-transition), box-shadow var(--ctos-transition);
        }
        
        .test-case:last-child { margin-bottom: 0; }
        .test-case:hover { box-shadow: 0 2px 12px rgba(0,0,0,0.2); }
        .test-case.pass { border-left: 3px solid var(--ctos-green); }
        .test-case.fail { border-left: 3px solid var(--ctos-red); }
        
        .test-header {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .test-icon { font-size: 18px; width: 24px; text-align: center; flex-shrink: 0; }
        .test-case.pass .test-icon { color: var(--ctos-green); }
        .test-case.fail .test-icon { color: var(--ctos-red); }
        
        .test-id {
            font-family: var(--ctos-mono);
            font-size: 12px; font-weight: 600;
            color: var(--ctos-muted);
            background: rgba(107,125,142,0.1);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            white-space: nowrap;
        }
        
        .test-description {
            font-size: 13px; font-weight: 500;
            color: #e8edf5;
            flex: 1; min-width: 200px;
        }
        
        .test-badge {
            font-size: 10px; font-weight: 700;
            letter-spacing: 1.5px;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }
        
        .test-badge.pass { background: rgba(46,213,115,0.12); color: var(--ctos-green); border: 1px solid rgba(46,213,115,0.25); }
        .test-badge.fail { background: rgba(255,94,87,0.12); color: var(--ctos-red); border: 1px solid rgba(255,94,87,0.25); }
        
        .test-details {
            margin-top: 10px;
            padding: 12px 14px;
            background: rgba(255,94,87,0.05);
            border: 1px solid rgba(255,94,87,0.15);
            border-radius: var(--ctos-radius-sm);
            font-family: var(--ctos-mono);
            font-size: 12px;
        }
        
        .detail-row { margin-bottom: 4px; color: var(--ctos-muted); }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-row .label { color: var(--ctos-text); font-weight: 600; }
        
        .detail-row code {
            background: rgba(0,0,0,0.3);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            color: var(--ctos-red);
            font-family: var(--ctos-mono);
            font-size: 11px;
        }
        
        .footer {
            text-align: center;
            padding: 24px;
            margin-top: 16px;
            border-top: 1px solid var(--ctos-border);
        }
        
        .footer-text { font-size: 11px; color: var(--ctos-muted); }
        .footer-text strong { color: var(--ctos-accent); }
        
        @media (max-width: 768px) {
            .summary-dashboard { grid-template-columns: repeat(2, 1fr); }
            .suite-subtitle { display: none; }
            .container { padding: 16px 12px; }
        }
        
        @media (max-width: 480px) {
            .summary-dashboard { grid-template-columns: 1fr; }
            .header-title { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <div class="header-logo">&#9670; Campus Testing &amp; Operations System</div>
            <div class="header-title"><strong>CTOS</strong> Unit Test Report</div>
            <div class="header-subtitle">Pure Logic Function Verification &mdash; <?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
        
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
        
        <?php echo $test_output; ?>
        
        <div class="footer">
            <div class="footer-text">
                <strong>CTOS</strong> Campus Testing &amp; Operations System &bull; Unit Test Execution Complete
            </div>
        </div>
        
    </div>
</body>
</html>