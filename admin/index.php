<?php
/**
 * admin/index.php – Admin dashboard with session-based authentication.
 */
session_start();

define('APP_ROOT', dirname(__DIR__));

// -----------------------------------------------------------------------
// Bootstrap config (no DB needed just for the HTML shell)
// -----------------------------------------------------------------------
if (file_exists(APP_ROOT . '/src/Config/Constants.php')) {
    require_once APP_ROOT . '/src/Config/Constants.php';
    \App\Config\Constants::load();
}

// -----------------------------------------------------------------------
// Handle admin login POST
// -----------------------------------------------------------------------
$loginError = '';
$adminUser  = getenv('ADMIN_USER') ?: 'admin';
$adminPass  = getenv('ADMIN_PASS') ?: 'Admin@12345!';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $inputUser = trim($_POST['username'] ?? '');
    $inputPass = $_POST['password'] ?? '';

    if ($inputUser === $adminUser && hash_equals(hash('sha256', $adminPass), hash('sha256', $inputPass))) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $inputUser;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = 'Invalid credentials';
    }
}

// -----------------------------------------------------------------------
// Handle logout
// -----------------------------------------------------------------------
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CompilerHub Admin</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="<?= $isLoggedIn ? 'dashboard' : 'login-page' ?>">

<?php if (!$isLoggedIn): ?>
<!-- ================================================================
     LOGIN FORM
     ================================================================ -->
<div class="login-container">
    <div class="login-card">
        <div class="login-logo">
            <span class="logo-icon">⚙</span>
            <h1>CompilerHub</h1>
            <p>Admin Panel</p>
        </div>
        <?php if ($loginError): ?>
        <div class="alert alert-error"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="hidden" name="admin_login" value="1">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus
                       placeholder="Admin username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required
                       placeholder="Admin password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- ================================================================
     DASHBOARD
     ================================================================ -->
<div class="app-layout">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span class="logo-icon">⚙</span>
            <span class="logo-text">CompilerHub</span>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-section="dashboard">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="#" class="nav-item" data-section="users">
                <span class="nav-icon">👥</span> Users
            </a>
            <a href="#" class="nav-item" data-section="analytics">
                <span class="nav-icon">📈</span> Analytics
            </a>
            <a href="#" class="nav-item" data-section="logs">
                <span class="nav-icon">📋</span> Logs
            </a>
            <a href="#" class="nav-item" data-section="settings">
                <span class="nav-icon">⚙</span> Settings
            </a>
        </nav>
        <div class="sidebar-footer">
            <span><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></span>
            <a href="?logout=1" class="btn-logout">Logout</a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">

        <!-- Top bar -->
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle">☰</button>
            <h2 class="page-title" id="pageTitle">Dashboard</h2>
            <div class="topbar-actions">
                <button class="btn btn-sm btn-outline" id="refreshBtn">↻ Refresh</button>
            </div>
        </header>

        <!-- Dashboard section -->
        <section id="section-dashboard" class="content-section active">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-body">
                        <div class="stat-value" id="statUsers">—</div>
                        <div class="stat-label">Total Users</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-body">
                        <div class="stat-value" id="statCompilations">—</div>
                        <div class="stat-label">Compilations</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🌐</div>
                    <div class="stat-body">
                        <div class="stat-value" id="statSessions">—</div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⚠</div>
                    <div class="stat-body">
                        <div class="stat-value" id="statErrors">—</div>
                        <div class="stat-label">Errors (24h)</div>
                    </div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-card">
                    <h3>Daily Activity (30 days)</h3>
                    <canvas id="dailyChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Language Usage</h3>
                    <canvas id="languageChart"></canvas>
                </div>
            </div>
        </section>

        <!-- Users section -->
        <section id="section-users" class="content-section">
            <div class="section-toolbar">
                <input type="text" id="userSearch" placeholder="Search users…" class="input-search">
                <select id="userRoleFilter" class="input-select">
                    <option value="">All roles</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="table-container">
                <table class="data-table" id="usersTable">
                    <thead>
                        <tr>
                            <th>ID</th><th>Username</th><th>Email</th>
                            <th>Role</th><th>Status</th><th>Joined</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="7" class="loading-cell">Loading…</td></tr>
                    </tbody>
                </table>
                <div class="pagination" id="usersPagination"></div>
            </div>
        </section>

        <!-- Analytics section -->
        <section id="section-analytics" class="content-section">
            <div class="charts-row">
                <div class="chart-card full-width">
                    <h3>Language Statistics</h3>
                    <canvas id="analyticsLangChart"></canvas>
                </div>
            </div>
            <div id="analyticsStats" class="stats-detail"></div>
        </section>

        <!-- Logs section -->
        <section id="section-logs" class="content-section">
            <div class="section-toolbar">
                <select id="logTypeSelect" class="input-select">
                    <option value="error">Error Log</option>
                    <option value="access">Access Log</option>
                </select>
                <select id="logLinesSelect" class="input-select">
                    <option value="50">50 lines</option>
                    <option value="100" selected>100 lines</option>
                    <option value="500">500 lines</option>
                </select>
                <button class="btn btn-sm btn-outline" id="refreshLogsBtn">↻ Refresh</button>
            </div>
            <div class="log-viewer" id="logViewer">
                <div class="log-placeholder">Select a log type and click Refresh.</div>
            </div>
        </section>

        <!-- Settings section -->
        <section id="section-settings" class="content-section">
            <div class="settings-card">
                <h3>System Information</h3>
                <table class="info-table">
                    <tr><td>PHP Version</td><td><?= phpversion() ?></td></tr>
                    <tr><td>Server Software</td><td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') ?></td></tr>
                    <tr><td>APP_ROOT</td><td><?= htmlspecialchars(defined('APP_ROOT') ? APP_ROOT : 'N/A') ?></td></tr>
                    <tr><td>Debug Mode</td><td><?= (defined('DEBUG_MODE') && DEBUG_MODE) ? 'Enabled' : 'Disabled' ?></td></tr>
                </table>
            </div>
        </section>

    </main>
</div>

<?php endif; ?>

<script src="js/dashboard.js"></script>
</body>
</html>
