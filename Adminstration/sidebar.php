<?php
// Determine active page
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">

    <div class="sidebar-logo">
        <span class="badge-label">ADMS</span>
        <h2>Admin Panel</h2>
    </div>

    <nav class="sidebar-nav">
        <span class="nav-section-label">Main</span>

        <a href="dashboard.php"    class="nav-link <?php echo $current == 'dashboard.php'    ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="alumni.php"       class="nav-link <?php echo $current == 'alumni.php'       ? 'active' : ''; ?>">
            <span class="nav-icon">🎓</span> Alumni Records
        </a>
        <a href="import_alumni.php" class="nav-link <?php echo $current == 'import_alumni.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📥</span> Import from Excel
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-pill">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
            </div>
            <div class="admin-info">
                <div class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
                <div class="admin-role">Administrator</div>
            </div>
        </div>
        <a class="logout-link" href="logout.php">🚪 Logout</a>
    </div>

</aside>
