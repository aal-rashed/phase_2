<?php
session_start();
include "config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ── Dropdown values from DB ──
$majors        = $conn->query("SELECT DISTINCT major           FROM alumni WHERE major           IS NOT NULL AND major           != '' ORDER BY major");
$campuses      = $conn->query("SELECT DISTINCT campus          FROM alumni WHERE campus          IS NOT NULL AND campus          != '' ORDER BY campus");
$study_types   = $conn->query("SELECT DISTINCT study_type      FROM alumni WHERE study_type      IS NOT NULL AND study_type      != '' ORDER BY study_type");
$grades        = $conn->query("SELECT DISTINCT academic_grade  FROM alumni WHERE academic_grade  IS NOT NULL AND academic_grade  != '' ORDER BY academic_grade");
$honors        = $conn->query("SELECT DISTINCT honor_rank      FROM alumni WHERE honor_rank      IS NOT NULL AND honor_rank      != '' ORDER BY honor_rank");
$nationalities = $conn->query("SELECT DISTINCT nationality     FROM alumni WHERE nationality     IS NOT NULL AND nationality     != '' ORDER BY nationality");
$colleges      = $conn->query("SELECT DISTINCT college         FROM alumni WHERE college         IS NOT NULL AND college         != '' ORDER BY college");
$terms         = $conn->query("SELECT DISTINCT graduation_term FROM alumni WHERE graduation_term IS NOT NULL AND graduation_term != '' ORDER BY graduation_term DESC");

// ── Build query ──
$where  = "WHERE 1=1";
$params = [];
$types  = "";

if (!empty($_GET['search'])) {
    $s = "%" . $_GET['search'] . "%";
    $where .= " AND (name LIKE ? OR student_id LIKE ? OR national_id LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    array_push($params, $s, $s, $s, $s, $s);
    $types .= "sssss";
}

$dropdown_filters = ['gender','academic_degree','college','major','campus','study_type','graduation_term','academic_grade','honor_rank','nationality'];
foreach ($dropdown_filters as $col) {
    if (!empty($_GET[$col])) {
        $where   .= " AND $col = ?";
        $params[] = $_GET[$col];
        $types   .= "s";
    }
}

if (!empty($_GET['gpa_min'])) {
    $where   .= " AND gpa >= ?";
    $params[] = floatval($_GET['gpa_min']);
    $types   .= "d";
}
if (!empty($_GET['gpa_max'])) {
    $where   .= " AND gpa <= ?";
    $params[] = floatval($_GET['gpa_max']);
    $types   .= "d";
}

$stmt = $conn->prepare("SELECT * FROM alumni $where ORDER BY id DESC");
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$total  = $result->num_rows;

// Count active filters
$filter_keys = ['search','gender','academic_degree','college','major','campus','study_type','graduation_term','academic_grade','honor_rank','nationality','gpa_min','gpa_max'];
$active_count = 0;
foreach ($filter_keys as $k) { if (!empty($_GET[$k])) $active_count++; }

function sel($key, $val) {
    return (isset($_GET[$key]) && $_GET[$key] == $val) ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Records – ADMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .filters-panel { background:var(--card); border:1px solid var(--border); border-radius:var(--radius-lg); margin-bottom:24px; overflow:hidden; }
        .filters-header { display:flex; align-items:center; justify-content:space-between; padding:14px 20px; cursor:pointer; user-select:none; border-bottom:1px solid transparent; transition:border-color .2s; }
        .filters-header.open { border-bottom-color:var(--border); }
        .filters-header-left { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:500; }
        .active-count { background:var(--gold); color:#0d1117; border-radius:20px; padding:1px 8px; font-size:11px; font-weight:700; }
        .toggle-icon { color:var(--muted); font-size:12px; transition:transform .3s; }
        .toggle-icon.open { transform:rotate(180deg); }
        .filters-body { display:none; padding:20px; }
        .filters-body.open { display:block; }
        .search-row { margin-bottom:16px; }
        .search-wrap { position:relative; }
        .search-wrap input { width:100%; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:var(--radius-sm); padding:10px 14px 10px 38px; color:var(--text); font-family:var(--font-body); font-size:13px; outline:none; transition:border-color .2s; }
        .search-wrap input:focus { border-color:var(--gold); }
        .search-wrap input::placeholder { color:#4a5568; }
        .search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; }
        .filters-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin-bottom:16px; }
        .gpa-row { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
        .gpa-row label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:1px; color:var(--muted); white-space:nowrap; }
        .gpa-row input { width:110px; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:var(--radius-sm); padding:8px 12px; color:var(--text); font-family:var(--font-body); font-size:13px; outline:none; transition:border-color .2s; }
        .gpa-row input:focus { border-color:var(--gold); }
        .gpa-row span { color:var(--muted); }
        .filter-actions { display:flex; gap:10px; border-top:1px solid var(--border); padding-top:16px; }
    </style>
</head>
<body>
<div class="layout">

    <?php include "sidebar.php"; ?>

    <main class="main-content animate-fadeIn">

        <div class="page-header">
            <div>
                <h1>Alumni Records</h1>
                <p class="page-sub">Manage and search all College of Computer graduates</p>
            </div>
            <a class="btn btn-primary" href="add_alumni.php">➕ Add Alumni</a>
        </div>

        <!-- Filters -->
        <form method="GET" action="alumni.php">
        <div class="filters-panel">
            <div class="filters-header <?php echo $active_count > 0 ? 'open':''; ?>" id="filtersToggle">
                <div class="filters-header-left">
                    🔍 Search & Filter
                    <?php if ($active_count > 0): ?>
                        <span class="active-count"><?php echo $active_count; ?> active</span>
                    <?php endif; ?>
                </div>
                <span class="toggle-icon <?php echo $active_count > 0 ? 'open':''; ?>" id="toggleIcon">▼</span>
            </div>

            <div class="filters-body <?php echo $active_count > 0 ? 'open':''; ?>" id="filtersBody">

                <!-- Search -->
                <div class="search-row">
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search"
                               placeholder="Search by name, student ID, national ID, email, or mobile..."
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>
                </div>

                <!-- Dropdowns -->
                <div class="filters-grid">

                    <div class="filter-group">
                        <label>Gender — الجنس</label>
                        <select name="gender">
                            <option value="">All</option>
                            <option value="Male"   <?php echo sel('gender','Male'); ?>>Male — ذكر</option>
                            <option value="Female" <?php echo sel('gender','Female'); ?>>Female — أنثى</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Degree — الدرجة العلمية</label>
                        <select name="academic_degree">
                            <option value="">All</option>
                            <option value="Bachelor" <?php echo sel('academic_degree','Bachelor'); ?>>Bachelor — بكالوريوس</option>
                            <option value="Master"   <?php echo sel('academic_degree','Master'); ?>>Master — ماجستير</option>
                            <option value="PhD"      <?php echo sel('academic_degree','PhD'); ?>>PhD — دكتوراه</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>College — الكلية</label>
                        <select name="college">
                            <option value="">All</option>
                            <?php while($r = $colleges->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['college']); ?>" <?php echo sel('college',$r['college']); ?>>
                                    <?php echo htmlspecialchars($r['college']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Major — التخصص</label>
                        <select name="major">
                            <option value="">All</option>
                            <?php while($r = $majors->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['major']); ?>" <?php echo sel('major',$r['major']); ?>>
                                    <?php echo htmlspecialchars($r['major']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Campus — المقر</label>
                        <select name="campus">
                            <option value="">All</option>
                            <?php while($r = $campuses->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['campus']); ?>" <?php echo sel('campus',$r['campus']); ?>>
                                    <?php echo htmlspecialchars($r['campus']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Study Type — نوع الدراسة</label>
                        <select name="study_type">
                            <option value="">All</option>
                            <?php while($r = $study_types->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['study_type']); ?>" <?php echo sel('study_type',$r['study_type']); ?>>
                                    <?php echo htmlspecialchars($r['study_type']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Term — الترم</label>
                        <select name="graduation_term">
                            <option value="">All Terms</option>
                            <?php while($r = $terms->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['graduation_term']); ?>" <?php echo sel('graduation_term',$r['graduation_term']); ?>>
                                    <?php echo htmlspecialchars($r['graduation_term']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Grade — التقدير</label>
                        <select name="academic_grade">
                            <option value="">All</option>
                            <?php while($r = $grades->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['academic_grade']); ?>" <?php echo sel('academic_grade',$r['academic_grade']); ?>>
                                    <?php echo htmlspecialchars($r['academic_grade']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Honor Rank — مرتبة الشرف</label>
                        <select name="honor_rank">
                            <option value="">All</option>
                            <?php while($r = $honors->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['honor_rank']); ?>" <?php echo sel('honor_rank',$r['honor_rank']); ?>>
                                    <?php echo htmlspecialchars($r['honor_rank']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Nationality — الجنسية</label>
                        <select name="nationality">
                            <option value="">All</option>
                            <?php while($r = $nationalities->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($r['nationality']); ?>" <?php echo sel('nationality',$r['nationality']); ?>>
                                    <?php echo htmlspecialchars($r['nationality']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                </div>

                <!-- GPA Range -->
                <div class="gpa-row">
                    <label>GPA — المعدل:</label>
                    <input type="number" name="gpa_min" step="0.01" min="0" max="5"
                           placeholder="Min (0.00)"
                           value="<?php echo htmlspecialchars($_GET['gpa_min'] ?? ''); ?>">
                    <span>—</span>
                    <input type="number" name="gpa_max" step="0.01" min="0" max="5"
                           placeholder="Max (5.00)"
                           value="<?php echo htmlspecialchars($_GET['gpa_max'] ?? ''); ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="alumni.php" class="btn btn-outline">✕ Reset All</a>
                </div>

            </div>
        </div>
        </form>

        <!-- Table -->
        <div class="table-wrap">
            <div class="table-meta">
                Showing <span class="count-badge"><?php echo number_format($total); ?></span> result(s)
            </div>

            <?php if ($total == 0): ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <p>No alumni found matching your criteria.</p>
                </div>
            <?php else: ?>
            <div style="overflow-x:auto;">
            <table>
                <tr>
                    <th>#</th>
                    <th>Term — الترم</th>
                    <th>Name — اسم الطالب</th>
                    <th>Student ID — رقم الطالب</th>
                    <th>College — الكلية</th>
                    <th>Major — التخصص</th>
                    <th>Degree — الدرجة</th>
                    <th>Gender — الجنس</th>
                    <th>GPA — المعدل</th>
                    <th>Grade — التقدير</th>
                    <th>Honor — مرتبة الشرف</th>
                    <th>Campus — المقر</th>
                    <th>Nationality — الجنسية</th>
                    <th>Email — البريد</th>
                    <th>Mobile — الجوال</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['graduation_term'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['student_id'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['college'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['major'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['academic_degree'] ?? '—'); ?></td>
                    <td>
                        <?php if(!empty($row['gender'])): ?>
                            <span class="badge <?php echo $row['gender']=='Male' ? 'badge-male':'badge-female'; ?>">
                                <?php echo htmlspecialchars($row['gender']); ?>
                            </span>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td><?php echo $row['gpa'] !== null ? number_format($row['gpa'],2) : '—'; ?></td>
                    <td><?php echo htmlspecialchars($row['academic_grade'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['honor_rank'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['campus'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['nationality'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['email'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['mobile'] ?? '—'); ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="btn btn-success" href="edit_alumni.php?id=<?php echo $row['id']; ?>">Edit</a>
                            <a class="btn btn-danger"  href="delete_alumni.php?id=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this alumni record?')">Delete</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
const toggle = document.getElementById('filtersToggle');
const body   = document.getElementById('filtersBody');
const icon   = document.getElementById('toggleIcon');

toggle.addEventListener('click', () => {
    const open = body.classList.contains('open');
    body.classList.toggle('open', !open);
    icon.classList.toggle('open', !open);
    toggle.classList.toggle('open', !open);
});
</script>
</body>
</html>
