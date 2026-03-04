<?php
session_start();
include "config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$total_alumni = $conn->query("SELECT COUNT(*) as c FROM alumni")->fetch_assoc()['c'];
$total_male   = $conn->query("SELECT COUNT(*) as c FROM alumni WHERE gender='Male'")->fetch_assoc()['c'];
$total_female = $conn->query("SELECT COUNT(*) as c FROM alumni WHERE gender='Female'")->fetch_assoc()['c'];
$total_majors = $conn->query("SELECT COUNT(DISTINCT major) as c FROM alumni")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – ADMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <!-- Sidebar -->
    <?php include "sidebar.php"; ?>

    <!-- Main -->
    <main class="main-content animate-fadeIn">

        <div class="page-header">
            <div>
                <h1>Dashboard</h1>
                <p class="page-sub">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?> 👋 — <?php echo date('l, F j, Y'); ?></p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎓</div>
                <div class="stat-label">Total Alumni</div>
                <div class="stat-value"><?php echo number_format($total_alumni); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👨</div>
                <div class="stat-label">Male</div>
                <div class="stat-value"><?php echo number_format($total_male); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👩</div>
                <div class="stat-label">Female</div>
                <div class="stat-value"><?php echo number_format($total_female); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-label">Majors</div>
                <div class="stat-value"><?php echo number_format($total_majors); ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-title">Quick Actions</div>
        <div class="actions-grid">
            <a class="action-card" href="alumni.php">
                <div class="action-icon">🎓</div>
                <div class="action-text">
                    <div class="action-title">Alumni Records</div>
                    <div class="action-desc">View, search & manage all graduates</div>
                </div>
            </a>
            <a class="action-card" href="add_alumni.php">
                <div class="action-icon">➕</div>
                <div class="action-text">
                    <div class="action-title">Add Alumni</div>
                    <div class="action-desc">Manually add a new graduate</div>
                </div>
            </a>
            <a class="action-card" href="import_alumni.php">
                <div class="action-icon">📥</div>
                <div class="action-text">
                    <div class="action-title">Import Excel</div>
                    <div class="action-desc">Bulk import alumni from Excel file</div>
                </div>
            </a>
        </div>

    </main>
</div>
</body>
</html>
