<?php
session_start();
include "config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: alumni.php");
    exit();
}

$id   = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM alumni WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: alumni.php");
    exit();
}
$alumni = $result->fetch_assoc();
$stmt->close();

$success = "";
$error   = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name             = trim($_POST['name']);
    $national_id      = trim($_POST['national_id']);
    $student_id       = trim($_POST['student_id']);    
    $gender           = $_POST['gender'];
    $nationality      = trim($_POST['nationality']);
    $college          = trim($_POST['college']);
    $major            = trim($_POST['major']);
    $academic_degree  = $_POST['academic_degree'];
    $study_type       = $_POST['study_type'];
    $gpa              = $_POST['gpa'];
    $academic_grade   = trim($_POST['academic_grade']);
    $mobile           = trim($_POST['mobile']);
    $email            = trim($_POST['email']);
    $honor_rank       = trim($_POST['honor_rank']);
    $campus           = trim($_POST['campus']);

    $sql  = "UPDATE alumni SET
                name=?, national_id=?,student_id=?, gender=?, nationality=?,
                college=?, major=?, academic_degree=?, study_type=?,
                gpa=?, academic_grade=?, mobile=?, email=?,
                honor_rank=?, campus=?
             WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssdsssssi",
        $name, $national_id,$student_id, $gender, $nationality,
        $college, $major, $academic_degree, $study_type,
        $gpa, $academic_grade, $mobile, $email,
        $honor_rank, $campus, $id
    );

    if ($stmt->execute()) {
        $success = "Alumni record updated successfully!";
        $s2 = $conn->prepare("SELECT * FROM alumni WHERE id = ?");
        $s2->bind_param("i", $id);
        $s2->execute();
        $alumni = $s2->get_result()->fetch_assoc();
        $s2->close();
    } else {
        $error = "Update failed: " . $conn->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alumni – ADMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include "sidebar.php"; ?>

    <main class="main-content animate-fadeIn" style="max-width:900px;">

        <h1 style="font-family:var(--font-display);font-size:26px;margin-bottom:6px;">Edit Alumni</h1>
        <div class="breadcrumb">
            <a href="alumni.php">Alumni Records</a> /
            Edit: <?php echo htmlspecialchars($alumni['name'] ?? ''); ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
        <div class="card">

            <!-- Personal Info -->
            <div class="form-section-title">Personal Information</div>
            <div class="grid-2">
                <div class="field">
                    <label>Full Name — اسم الطالب</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($alumni['name'] ?? ''); ?>" required>
                </div>
                <div class="field ">
                    <label>Student ID — رقم الطالب </label>
                    <input type="text" value="<?php echo htmlspecialchars($alumni['student_id'] ?? ''); ?>" required>
                </div>
                <div class="field">
                    <label>National ID — السجل المدني</label>
                    <input type="text" name="national_id" value="<?php echo htmlspecialchars($alumni['national_id'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Gender — الجنس</label>
                    <select name="gender">
                        <option value="">-- Select --</option>
                        <option value="Male"   <?php echo ($alumni['gender'] ?? '')==='Male'   ? 'selected':''; ?>>Male — ذكر</option>
                        <option value="Female" <?php echo ($alumni['gender'] ?? '')==='Female' ? 'selected':''; ?>>Female — أنثى</option>
                    </select>
                </div>
                <div class="field">
                    <label>Nationality — الجنسية</label>
                    <input type="text" name="nationality" value="<?php echo htmlspecialchars($alumni['nationality'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Mobile — الجوال</label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($alumni['mobile'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Email — البريد الإلكتروني</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($alumni['email'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Campus — المقر</label>
                    <input type="text" name="campus" value="<?php echo htmlspecialchars($alumni['campus'] ?? ''); ?>">
                </div>
            </div>

            <!-- Academic Info -->
            <div class="form-section-title">Academic Information</div>
            <div class="grid-3">
                <div class="field">
                    <label>College — الكلية</label>
                    <input type="text" name="college" value="<?php echo htmlspecialchars($alumni['college'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Major — التخصص</label>
                    <input type="text" name="major" value="<?php echo htmlspecialchars($alumni['major'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Degree — الدرجة العلمية</label>
                    <select name="academic_degree">
                        <option value="">-- Select --</option>
                        <option value="Bachelor" <?php echo ($alumni['academic_degree'] ?? '')==='Bachelor' ? 'selected':''; ?>>Bachelor — بكالوريوس</option>
                        <option value="Master"   <?php echo ($alumni['academic_degree'] ?? '')==='Master'   ? 'selected':''; ?>>Master — ماجستير</option>
                        <option value="PhD"      <?php echo ($alumni['academic_degree'] ?? '')==='PhD'      ? 'selected':''; ?>>PhD — دكتوراه</option>
                    </select>
                </div>
                <div class="field">
                    <label>Study Type — نوع الدراسة</label>
                    <input type="text" name="study_type" value="<?php echo htmlspecialchars($alumni['study_type'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>GPA — المعدل</label>
                    <input type="number" step="0.01" min="0" max="5" name="gpa"
                           value="<?php echo htmlspecialchars($alumni['gpa'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Grade — التقدير</label>
                    <input type="text" name="academic_grade" value="<?php echo htmlspecialchars($alumni['academic_grade'] ?? ''); ?>">
                </div>
                <div class="field">
                    <label>Honor Rank — مرتبة الشرف</label>
                    <input type="text" name="honor_rank" value="<?php echo htmlspecialchars($alumni['honor_rank'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                <a href="alumni.php" class="btn btn-outline">Cancel</a>
            </div>

        </div>
        </form>

    </main>
</div>
</body>
</html>
