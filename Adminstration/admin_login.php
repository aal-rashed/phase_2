<?php
session_start();
include "config.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $university_id = trim($_POST['university_id']);
    $password      = $_POST['password'];

    if (empty($university_id) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE university_id = ?");
        $stmt->bind_param("s", $university_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']   = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "University ID not found.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – ADMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-box animate-fadeUp">

    <a class="back-link" href="index.php">← Back to Portal</a>

    <div class="login-icon">🛡️</div>
    <h2>Admin Login</h2>
    <p class="login-sub">College of Computer — Staff Access</p>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="field">
            <label>University ID</label>
            <input type="text" name="university_id"
                   placeholder="e.g. admin001"
                   value="<?php echo isset($_POST['university_id']) ? htmlspecialchars($_POST['university_id']) : ''; ?>"
                   required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <br>
        <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px;">
            Sign In →
        </button>
    </form>

</div>
</body>
</html>
