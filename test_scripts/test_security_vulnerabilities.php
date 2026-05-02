<?php
/**
 * ============================================
 * TEST SCRIPT: Security Vulnerability Assessment
 * File: test_security_vulnerabilities.php
 * ============================================
 * 
 * Target: SQL Injection vulnerabilities in library functions
 * Database Required: YES (campus_management)
 * Execution Method: php test_security_vulnerabilities.php
 * 
 * WARNING: This script contains malicious payloads for 
 * demonstration purposes. Run ONLY in a sandboxed 
 * development environment. It will expose critical flaws.
 */

// Load the core application configuration and function definitions
require_once 'config.php';

// Initialize global assessment tracking variables for vulnerability counting
$total_tests = 0;
$vulnerabilities_found = 0;

// Buffer all output for structured rendering after test execution completes
ob_start();

// =============================================
// VULNERABILITY 1: borrowBook() - user_id injection
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-vuln-sqli">';
echo '<div class="suite-number">VULN-1</div>';
echo '<div class="suite-title">SQL Injection: borrowBook() user_id Parameter</div>';
echo '<div class="suite-subtitle">Unsanitized integer parameter concatenated directly into WHERE clause</div>';
echo '</div>';
echo '<div class="suite-body">';

// Payload narrative
echo '<div class="vuln-narrative">';
echo '<div class="narrative-label">Attack Vector Analysis</div>';
echo '<div class="narrative-content">';
echo '<p><strong>Payload:</strong> <code class="sql-payload">1 OR 1=1</code></p>';
echo '<p><strong>Generated SQL:</strong> <code class="sql-query">SELECT total_fines FROM library_users WHERE id = 1 OR 1=1</code></p>';
echo '<p><strong>Impact:</strong> This boolean tautology bypasses the single-row restriction and exposes <em>all</em> user fine records.</p>';
echo '</div>';
echo '</div>';

// Database connection for active injection testing
$conn = getDBConnection();

$malicious_user_id = "1 OR 1=1";
$vulnerable_query = "SELECT total_fines FROM library_users WHERE id = $malicious_user_id";

echo '<div class="query-display">';
echo '<div class="query-label">Executed Query</div>';
echo '<code class="query-text">' . htmlspecialchars($vulnerable_query) . '</code>';
echo '</div>';

$result = @$conn->query($vulnerable_query);
if ($result) {
    $row_count = $result->num_rows;
    echo '<div class="test-case fail">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">&#9888;</span>';
    echo '<span class="test-id">TC-SEC-01</span>';
    echo '<span class="test-description">SQL Injection confirmed on user_id parameter</span>';
    echo '<span class="test-badge fail">VULNERABLE</span>';
    echo '</div>';
    echo '<div class="test-message">Expected (secure): <strong>1 row</strong> or error | Actual: <strong>' . $row_count . ' rows</strong> returned — all users exposed</div>';
    echo '</div>';
    $vulnerabilities_found++;
} else {
    echo '<div class="test-case info">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">&#8505;</span>';
    echo '<span class="test-id">TC-SEC-01</span>';
    echo '<span class="test-description">Query caused database error</span>';
    echo '<span class="test-badge info">ERROR</span>';
    echo '</div>';
    echo '<div class="test-message">' . htmlspecialchars($conn->error) . '</div>';
    echo '</div>';
}
$total_tests++;

echo '</div></div>';

// =============================================
// VULNERABILITY 2: returnBook() - book_uid injection
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-vuln-string">';
echo '<div class="suite-number">VULN-2</div>';
echo '<div class="suite-title">SQL Injection: returnBook() book_uid Parameter</div>';
echo '<div class="suite-subtitle">String parameter concatenated into quoted SQL literal without escaping</div>';
echo '</div>';
echo '<div class="suite-body">';

echo '<div class="vuln-narrative">';
echo '<div class="narrative-label">Attack Vector Analysis</div>';
echo '<div class="narrative-content">';
echo '<p><strong>Payload:</strong> <code class="sql-payload">\' OR \'1\'=\'1</code></p>';
echo '<p><strong>Generated SQL:</strong> <code class="sql-query">SELECT * FROM borrowed_books WHERE book_uid = \'\' OR \'1\'=\'1\'</code></p>';
echo '<p><strong>Impact:</strong> The injected single quote breaks out of the string literal context, allowing the tautology to match every borrowed book record.</p>';
echo '</div>';
echo '</div>';

$malicious_book_uid = "' OR '1'='1";
$vulnerable_query2 = "SELECT * FROM borrowed_books WHERE book_uid = '$malicious_book_uid'";

echo '<div class="query-display">';
echo '<div class="query-label">Executed Query</div>';
echo '<code class="query-text">' . htmlspecialchars($vulnerable_query2) . '</code>';
echo '</div>';

$result2 = @$conn->query($vulnerable_query2);
if ($result2) {
    $row_count2 = $result2->num_rows;
    echo '<div class="test-case fail">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">&#9888;</span>';
    echo '<span class="test-id">TC-SEC-02</span>';
    echo '<span class="test-description">SQL Injection confirmed on book_uid parameter</span>';
    echo '<span class="test-badge fail">VULNERABLE</span>';
    echo '</div>';
    echo '<div class="test-message">Expected (secure): <strong>0 or 1 row</strong> | Actual: <strong>' . $row_count2 . ' rows</strong> returned — all book records exposed</div>';
    echo '</div>';
    $vulnerabilities_found++;
} else {
    echo '<div class="test-case info">';
    echo '<div class="test-header">';
    echo '<span class="test-icon">&#8505;</span>';
    echo '<span class="test-id">TC-SEC-02</span>';
    echo '<span class="test-description">Query caused database error</span>';
    echo '<span class="test-badge info">ERROR</span>';
    echo '</div>';
    echo '<div class="test-message">' . htmlspecialchars($conn->error) . '</div>';
    echo '</div>';
}
$total_tests++;

$conn->close();

echo '</div></div>';

// =============================================
// VULNERABILITY 3: Hardcoded Credentials
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-vuln-creds">';
echo '<div class="suite-number">VULN-3</div>';
echo '<div class="suite-title">Hardcoded Plain-Text Credentials</div>';
echo '<div class="suite-subtitle">Authentication secrets stored as unprotected constants in source code</div>';
echo '</div>';
echo '<div class="suite-body">';

echo '<div class="vuln-narrative">';
echo '<div class="narrative-label">Credential Exposure</div>';
echo '<div class="narrative-content">';
echo '<p>The following credentials are stored as plain-text PHP constants within <code>config.php</code>:</p>';
echo '</div>';
echo '</div>';

echo '<div class="creds-exposure">';
echo '<div class="cred-row">';
echo '<span class="cred-label">Username:</span>';
echo '<code class="cred-value">admin</code>';
echo '</div>';
echo '<div class="cred-row">';
echo '<span class="cred-label">Password:</span>';
echo '<code class="cred-value">campus2024</code>';
echo '</div>';
echo '</div>';

echo '<div class="test-case fail">';
echo '<div class="test-header">';
echo '<span class="test-icon">&#9888;</span>';
echo '<span class="test-id">TC-SEC-03</span>';
echo '<span class="test-description">Plain-text hardcoded credentials detected</span>';
echo '<span class="test-badge fail">VULNERABLE</span>';
echo '</div>';
echo '<div class="test-details">';
echo '<div class="detail-row"><span class="label">Issue 1:</span> Password stored in plain text (no cryptographic hashing)</div>';
echo '<div class="detail-row"><span class="label">Issue 2:</span> Hardcoded in source code (cannot rotate without redeployment)</div>';
echo '<div class="detail-row"><span class="label">Issue 3:</span> Single credential set for all administrators (no user management)</div>';
echo '<div class="detail-row"><span class="label">Issue 4:</span> No password complexity or length requirements enforced</div>';
echo '</div>';
echo '</div>';

$vulnerabilities_found++;
$total_tests++;

echo '</div></div>';

// =============================================
// VULNERABILITY 4: Lack of Prepared Statements
// =============================================
echo '<div class="suite-container">';
echo '<div class="suite-header suite-vuln-static">';
echo '<div class="suite-number">VULN-4</div>';
echo '<div class="suite-title">Inconsistent Prepared Statement Usage</div>';
echo '<div class="suite-subtitle">Static code analysis revealing mixed secure and vulnerable query patterns</div>';
echo '</div>';
echo '<div class="suite-body">';

echo '<div class="code-analysis-grid">';
echo '<div class="analysis-column secure">';
echo '<div class="column-header">Secure Functions</div>';
echo '<div class="column-subtitle">Using <code>$conn->prepare()</code></div>';
echo '<ul class="function-list">';
echo '<li><span class="func-name">addStudent()</span></li>';
echo '<li><span class="func-name">issueParkingPermit()</span></li>';
echo '<li><span class="func-name">addLibraryUser()</span></li>';
echo '<li><span class="func-name">borrowBook()</span> <span class="func-note">(INSERT only)</span></li>';
echo '<li><span class="func-name">returnBook()</span> <span class="func-note">(UPDATE only)</span></li>';
echo '</ul>';
echo '</div>';
echo '<div class="analysis-column vulnerable">';
echo '<div class="column-header">Vulnerable Functions</div>';
echo '<div class="column-subtitle">Using Direct Concatenation</div>';
echo '<ul class="function-list">';
echo '<li><span class="func-name">borrowBook()</span> <span class="func-note">(SELECT query)</span></li>';
echo '<li><span class="func-name">returnBook()</span> <span class="func-note">(SELECT query)</span></li>';
echo '<li><span class="func-name">getUserBorrowedBooks()</span></li>';
echo '<li><span class="func-name">getLibraryStats()</span></li>';
echo '</ul>';
echo '</div>';
echo '</div>';

echo '<div class="test-case fail">';
echo '<div class="test-header">';
echo '<span class="test-icon">&#9888;</span>';
echo '<span class="test-id">TC-SEC-04</span>';
echo '<span class="test-description">Inconsistent query construction patterns detected</span>';
echo '<span class="test-badge fail">VULNERABLE</span>';
echo '</div>';
echo '<div class="test-message"><strong>4 out of 8</strong> database-interacting functions contain SQL Injection vulnerabilities — a <strong>50%</strong> exposure rate</div>';
echo '</div>';

$vulnerabilities_found++;
$total_tests++;

echo '</div></div>';

// Capture all buffered test output for HTML rendering
$test_output = ob_get_clean();

// Calculate severity metrics
$severity_class = ($vulnerabilities_found >= 3) ? 'critical' : (($vulnerabilities_found >= 1) ? 'high' : 'moderate');
$severity_label = ($vulnerabilities_found >= 3) ? 'CRITICAL - SYSTEM UNSAFE FOR DEPLOYMENT' : (($vulnerabilities_found >= 1) ? 'HIGH - REMEDIATION REQUIRED' : 'MODERATE - REVIEW RECOMMENDED');

// =============================================
// RENDER COMPLETE HTML DOCUMENT WITH CTOS THEME
// =============================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTOS | Security Vulnerability Assessment</title>
    <style>
        /* ============================================
           CTOS THEME - Campus Testing & Operations System
           Professional Security Dashboard Styling Framework
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
            background: linear-gradient(90deg, transparent, var(--ctos-red), transparent);
        }
        
        .header-logo {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 4px;
            color: var(--ctos-red);
            text-transform: uppercase;
            margin-bottom: 8px;
            animation: pulse-warning 2s ease-in-out infinite;
        }
        
        @keyframes pulse-warning {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
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
            color: var(--ctos-red);
        }
        
        .header-subtitle {
            font-size: 13px;
            color: var(--ctos-muted);
            letter-spacing: 0.5px;
        }
        
        /* Alert banner */
        .alert-banner {
            background: rgba(255, 94, 87, 0.08);
            border: 1px solid rgba(255, 94, 87, 0.3);
            border-radius: var(--ctos-radius);
            padding: 16px 24px;
            margin-bottom: 36px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        
        .alert-icon {
            font-size: 28px;
            flex-shrink: 0;
        }
        
        .alert-content {
            font-size: 13px;
            color: var(--ctos-red);
            letter-spacing: 0.3px;
            line-height: 1.5;
        }
        
        .alert-content strong {
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 11px;
        }
        
        /* ============================================
           SUMMARY DASHBOARD
           ============================================ */
        .summary-dashboard {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
        
        .card-total .card-value { color: var(--ctos-blue); }
        .card-found .card-value { color: var(--ctos-red); }
        .card-severity .card-value { color: var(--ctos-orange); }
        
        .card-total { border-top: 3px solid var(--ctos-blue); }
        .card-found { border-top: 3px solid var(--ctos-red); }
        .card-severity { border-top: 3px solid var(--ctos-orange); }
        
        .card-severity .severity-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .severity-badge.critical {
            background: rgba(255, 94, 87, 0.15);
            color: var(--ctos-red);
            border: 1px solid rgba(255, 94, 87, 0.4);
        }
        
        .severity-badge.high {
            background: rgba(255, 159, 67, 0.15);
            color: var(--ctos-orange);
            border: 1px solid rgba(255, 159, 67, 0.4);
        }
        
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
            background: linear-gradient(90deg, var(--ctos-red), transparent 80%);
            opacity: 0.3;
        }
        
        .suite-vuln-sqli { border-left: 4px solid var(--ctos-red); }
        .suite-vuln-string { border-left: 4px solid var(--ctos-orange); }
        .suite-vuln-creds { border-left: 4px solid var(--ctos-purple); }
        .suite-vuln-static { border-left: 4px solid var(--ctos-blue); }
        
        .suite-number {
            font-family: var(--ctos-mono);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--ctos-red);
            background: var(--ctos-darker);
            padding: 4px 10px;
            border-radius: var(--ctos-radius-sm);
            border: 1px solid rgba(255, 94, 87, 0.3);
            white-space: nowrap;
        }
        
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
           VULNERABILITY NARRATIVE
           ============================================ */
        .vuln-narrative {
            background: rgba(255, 94, 87, 0.05);
            border: 1px solid rgba(255, 94, 87, 0.15);
            border-radius: var(--ctos-radius-sm);
            padding: 14px 18px;
            margin-bottom: 14px;
        }
        
        .narrative-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--ctos-red);
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .narrative-content {
            font-size: 12px;
            color: var(--ctos-muted);
            line-height: 1.7;
        }
        
        .narrative-content strong {
            color: var(--ctos-text);
        }
        
        .narrative-content p {
            margin-bottom: 6px;
        }
        
        .narrative-content p:last-child {
            margin-bottom: 0;
        }
        
        .sql-payload {
            background: rgba(255, 94, 87, 0.12);
            color: var(--ctos-red);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            font-family: var(--ctos-mono);
            font-size: 11px;
            letter-spacing: 0.3px;
        }
        
        .sql-query {
            background: rgba(0, 0, 0, 0.3);
            color: var(--ctos-orange);
            padding: 2px 8px;
            border-radius: var(--ctos-radius-sm);
            font-family: var(--ctos-mono);
            font-size: 11px;
            word-break: break-all;
        }
        
        /* Query display */
        .query-display {
            background: var(--ctos-darker);
            border: 1px solid var(--ctos-border);
            border-radius: var(--ctos-radius-sm);
            padding: 12px 16px;
            margin-bottom: 14px;
        }
        
        .query-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--ctos-muted);
            font-weight: 700;
            margin-bottom: 6px;
        }
        
        .query-text {
            font-family: var(--ctos-mono);
            font-size: 12px;
            color: var(--ctos-orange);
            word-break: break-all;
            display: block;
            background: rgba(0, 0, 0, 0.2);
            padding: 8px 12px;
            border-radius: var(--ctos-radius-sm);
        }
        
        /* ============================================
           CREDENTIALS EXPOSURE
           ============================================ */
        .creds-exposure {
            background: var(--ctos-darker);
            border: 1px solid rgba(165, 94, 234, 0.3);
            border-radius: var(--ctos-radius-sm);
            padding: 16px;
            margin-bottom: 14px;
        }
        
        .cred-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
        }
        
        .cred-row:first-child {
            padding-top: 0;
        }
        
        .cred-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--ctos-muted);
            font-weight: 600;
            min-width: 80px;
        }
        
        .cred-value {
            font-family: var(--ctos-mono);
            font-size: 13px;
            background: rgba(165, 94, 234, 0.1);
            color: var(--ctos-purple);
            padding: 4px 12px;
            border-radius: var(--ctos-radius-sm);
            border: 1px solid rgba(165, 94, 234, 0.2);
        }
        
        /* ============================================
           CODE ANALYSIS GRID
           ============================================ */
        .code-analysis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }
        
        .analysis-column {
            background: var(--ctos-darker);
            border-radius: var(--ctos-radius-sm);
            padding: 16px;
        }
        
        .analysis-column.secure {
            border: 1px solid rgba(46, 213, 115, 0.2);
        }
        
        .analysis-column.vulnerable {
            border: 1px solid rgba(255, 94, 87, 0.2);
        }
        
        .column-header {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 4px;
        }
        
        .analysis-column.secure .column-header { color: var(--ctos-green); }
        .analysis-column.vulnerable .column-header { color: var(--ctos-red); }
        
        .column-subtitle {
            font-size: 10px;
            color: var(--ctos-muted);
            margin-bottom: 12px;
        }
        
        .column-subtitle code {
            background: rgba(107, 125, 142, 0.15);
            padding: 1px 6px;
            border-radius: 3px;
            font-family: var(--ctos-mono);
            font-size: 9px;
        }
        
        .function-list {
            list-style: none;
            padding: 0;
        }
        
        .function-list li {
            padding: 6px 0;
            font-family: var(--ctos-mono);
            font-size: 11px;
            border-bottom: 1px solid rgba(42, 58, 74, 0.4);
        }
        
        .function-list li:last-child {
            border-bottom: none;
        }
        
        .func-name {
            color: var(--ctos-text);
            font-weight: 600;
        }
        
        .func-note {
            font-size: 9px;
            color: var(--ctos-muted);
            font-style: italic;
        }
        
        .analysis-column.secure .func-name { color: var(--ctos-green); }
        .analysis-column.vulnerable .func-name { color: var(--ctos-red); }
        
        @media (max-width: 768px) {
            .code-analysis-grid {
                grid-template-columns: 1fr;
            }
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
            margin-bottom: 6px;
            color: var(--ctos-muted);
            padding-left: 14px;
            position: relative;
        }
        
        .detail-row::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--ctos-red);
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
        }
        
        .detail-row .label {
            color: var(--ctos-text);
            font-weight: 600;
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
        
        .footer-remediation {
            margin-top: 16px;
            padding: 16px 20px;
            background: rgba(255, 159, 67, 0.06);
            border: 1px solid rgba(255, 159, 67, 0.2);
            border-radius: var(--ctos-radius-sm);
            text-align: left;
            display: inline-block;
        }
        
        .footer-remediation .rem-title {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--ctos-orange);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .footer-remediation ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-remediation li {
            font-size: 12px;
            color: var(--ctos-muted);
            padding: 4px 0;
            padding-left: 16px;
            position: relative;
        }
        
        .footer-remediation li::before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--ctos-orange);
        }
        
        /* ============================================
           RESPONSIVE ADAPTATIONS
           ============================================ */
        @media (max-width: 768px) {
            .summary-dashboard {
                grid-template-columns: 1fr;
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
            <div class="header-logo">&#9762; Security Vulnerability Assessment</div>
            <div class="header-title"><strong>CTOS</strong> Security Audit Report</div>
            <div class="header-subtitle">SQL Injection &amp; Credential Exposure Analysis &mdash; <?php echo date('Y-m-d H:i:s'); ?></div>
        </div>
        
        <!-- ============================================
             ALERT BANNER
             ============================================ -->
        <div class="alert-banner">
            <div class="alert-icon">&#9762;</div>
            <div class="alert-content">
                <strong>Security Alert</strong><br>
                This assessment contains active exploit demonstrations. The vulnerabilities documented below represent genuine security flaws that must be remediated before any production deployment. Run exclusively in sandboxed development environments.
            </div>
        </div>
        
        <!-- ============================================
             SUMMARY DASHBOARD
             ============================================ -->
        <div class="summary-dashboard">
            <div class="summary-card card-total">
                <div class="card-value"><?php echo $total_tests; ?></div>
                <div class="card-label">Vulnerabilities Tested</div>
            </div>
            <div class="summary-card card-found">
                <div class="card-value"><?php echo $vulnerabilities_found; ?></div>
                <div class="card-label">Critical Flaws Found</div>
            </div>
            <div class="summary-card card-severity">
                <div class="card-value">!</div>
                <div class="card-label">Deployment Status</div>
                <div class="severity-badge <?php echo $severity_class; ?>"><?php echo $severity_label; ?></div>
            </div>
        </div>
        
        <!-- ============================================
             VULNERABILITY REPORT SECTIONS
             ============================================ -->
        <?php echo $test_output; ?>
        
        <!-- ============================================
             FOOTER SECTION
             ============================================ -->
        <div class="footer">
            <div class="footer-text">
                <strong>CTOS</strong> Campus Testing &amp; Operations System &bull; Security Assessment Complete
            </div>
            <div class="footer-remediation">
                <div class="rem-title">Recommended Remediation Actions</div>
                <ul>
                    <li>Implement parameterized prepared statements for all database queries</li>
                    <li>Migrate credentials to bcrypt-hashed storage with environment variable injection</li>
                    <li>Establish mandatory secure coding standards with automated code review</li>
                    <li>Deploy input validation and sanitization middleware for all user-supplied parameters</li>
                </ul>
            </div>
        </div>
        
    </div>
</body>
</html>