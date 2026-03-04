<?php
session_start();
include "config.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message  = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
    $file     = $_FILES['excel_file'];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmp_path = $file['tmp_name'];

    if (!in_array($ext, ['xlsx', 'csv'])) {
        $message  = "Invalid file type. Please upload .xlsx or .csv";
        $msg_type = "error";
    } elseif ($file['error'] !== 0) {
        $message  = "File upload error. Please try again.";
        $msg_type = "error";
    } else {
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        if ($ext === 'csv') {
            $handle    = fopen($tmp_path, "r");
            $header    = array_map(fn($h) => trim($h), fgetcsv($handle));
            $col_count = count($header);
            $row_num   = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $row_num++;
                if (count(array_filter($row)) == 0) continue;
                $row    = array_pad(array_slice($row, 0, $col_count), $col_count, '');
                $data   = array_combine($header, $row);
                $result = insertAlumni($conn, $data, '');
                if ($result === true)       $inserted++;
                elseif ($result === 'skip') $skipped++;
                else                        $errors[] = "Row $row_num: $result";
            }
            fclose($handle);

        } else {
            // XLSX
            if (!class_exists('ZipArchive')) {
                $message  = "ZipArchive extension is not enabled. Open php.ini, enable extension=zip, then restart Apache.";
                $msg_type = "error";
                goto done;
            }

            // ── Read ALL sheets ──
            $sheets_data = readAllSheets($tmp_path);

            if (empty($sheets_data)) {
                $message  = "The file appears to be empty or could not be read.";
                $msg_type = "error";
                goto done;
            }

            foreach ($sheets_data as $sheet_name => $rows) {
                if (empty($rows) || count($rows) < 2) continue;

                $header    = array_map(fn($h) => trim($h), $rows[0]);
                $col_count = count($header);
                $row_num   = 1;

                for ($i = 1; $i < count($rows); $i++) {
                    $row_num++;
                    $row = $rows[$i];
                    if (count(array_filter($row)) == 0) continue;
                    // pad/trim to match header
                    $row    = array_pad(array_slice($row, 0, $col_count), $col_count, '');
                    $data   = array_combine($header, $row);
                    // Pass sheet name as graduation_term
                    // Normalize keys too (column names may have font differences)
                    $normalized_data = [];
                    foreach ($data as $k => $v) {
                        $normalized_data[normalizeArabic($k)] = $v;
                    }
                    $result = insertAlumni($conn, $normalized_data, $sheet_name);
                    if ($result === true)       $inserted++;
                    elseif ($result === 'skip') $skipped++;
                    else                        $errors[] = "Sheet $sheet_name / Row $row_num: $result";
                }
            }
        }

        done:
        if (!$message) {
            $msg_type = $inserted > 0 ? "success" : "warning";
            $message  = "Import complete! ✅ Inserted: <strong>$inserted</strong> records | ⏭ Skipped (duplicate): <strong>$skipped</strong>";
            if (!empty($errors)) {
                $message .= "<br><br>⚠️ Errors in " . count($errors) . " row(s):<br>" . implode("<br>", array_slice($errors, 0, 15));
            }
        }
    }
}

// ── Normalize Arabic text ──
// Fixes different Unicode forms of same Arabic letters causing duplicates
function normalizeArabic($text) {
    if (empty($text)) return $text;

    // Normalize Unicode (NFC form)
    if (function_exists('normalizer_normalize')) {
        $text = normalizer_normalize($text, Normalizer::FORM_C);
    }

    // Unify Alef variants → plain Alef
    $text = str_replace(['أ','إ','آ','ٱ'], 'ا', $text);

    // Unify Yaa variants (dotless Yaa at end → Yaa with dots)
    $text = str_replace('ى', 'ي', $text);

    // Unify Taa Marbuta variants
    $text = str_replace('ة', 'ة', $text);

    // Remove Tashkeel (diacritics/harakat)
    $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

    // Trim whitespace and extra spaces
    $text = preg_replace('/\s+/u', ' ', trim($text));

    return $text;
}

// ── Insert one alumni record ──
function insertAlumni($conn, $data, $term) {
    $map = [
        'campus'          => ['المقر',              'campus'],
        'gender'          => ['الجنس',              'gender',         'sex'],
        'academic_degree' => ['الدرجة العلمية',     'الدرجة',         'academic_degree', 'degree'],
        'college'         => ['الكلية',             'college'],
        'major'           => ['التخصص',             'major',          'specialization'],
        'study_type'      => ['نوع الدراسة',        'study_type'],
        'student_id'      => ['رقم الطالب',         'student_id',     'studentid'],
        'name'            => ['اسم الطالب',         'الاسم',          'name',           'full_name'],
        'national_id'     => ['السجل المدني',       'الهوية',         'national_id'],
        'nationality'     => ['الجنسية',            'nationality'],
        'gpa'             => ['المعدل',             'gpa',            'grade_point'],
        'academic_grade'  => ['التقدير',            'academic_grade', 'grade'],
        'mobile'          => ['الجوال',             'mobile',         'phone'],
        'email'           => ['البريد الإلكتروني', 'البريد الالكتروني', 'البريد',  'email', 'Email', 'EMAIL'],
        'honor_rank'      => ['مرتبة الشرف',        'honor_rank'],
    ];

    $clean = [];
    foreach ($map as $field => $aliases) {
        $clean[$field] = null;
        foreach ($aliases as $alias) {
            // Try original alias
            if (isset($data[$alias]) && trim($data[$alias]) !== '') {
                $clean[$field] = trim($data[$alias]);
                break;
            }
            // Try normalized alias
            $norm_alias = normalizeArabic($alias);
            if (isset($data[$norm_alias]) && trim($data[$norm_alias]) !== '') {
                $clean[$field] = trim($data[$norm_alias]);
                break;
            }
        }
    }

    // graduation_term comes from sheet name
    $clean['graduation_term'] = !empty($term) ? trim($term) : null;

    if (empty($clean['name'])) return 'Missing name';

    // Normalize Arabic text fields to fix font/encoding differences
    $arabic_fields = ['campus','gender','academic_degree','college','major','study_type','name','nationality','academic_grade','honor_rank'];
    foreach ($arabic_fields as $f) {
        if (!empty($clean[$f])) $clean[$f] = normalizeArabic($clean[$f]);
    }

    // Check duplicate
    if (!empty($clean['student_id'])) {
        $check = $conn->prepare("SELECT id FROM alumni WHERE student_id = ?");
        $check->bind_param("s", $clean['student_id']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $check->close();
            return 'skip';
        }
        $check->close();
    }

    // GPA
    $gpa = null;
    if (!empty($clean['gpa'])) {
        $gpa_str = str_replace(',', '.', $clean['gpa']);
        if (is_numeric($gpa_str)) $gpa = floatval($gpa_str);
    }

    $stmt = $conn->prepare("
        INSERT INTO alumni
            (campus, gender, academic_degree, college, major, study_type, graduation_term,
             student_id, name, national_id, nationality,
             gpa, academic_grade, mobile, email, honor_rank)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");

    $stmt->bind_param("sssssssssssdssss",
        $clean['campus'],
        $clean['gender'],
        $clean['academic_degree'],
        $clean['college'],
        $clean['major'],
        $clean['study_type'],
        $clean['graduation_term'],
        $clean['student_id'],
        $clean['name'],
        $clean['national_id'],
        $clean['nationality'],
        $gpa,
        $clean['academic_grade'],
        $clean['mobile'],
        $clean['email'],
        $clean['honor_rank']
    );

    if ($stmt->execute()) { $stmt->close(); return true; }
    else { $err = $stmt->error; $stmt->close(); return $err; }
}

// ── Read ALL sheets from XLSX ──
function readAllSheets($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== true) return [];

    // 1. Shared strings
    $strings       = [];
    $sharedStrings = $zip->getFromName('xl/sharedStrings.xml');
    if ($sharedStrings) {
        $xml = simplexml_load_string($sharedStrings);
        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string)$si->t;
            } else {
                $text = '';
                foreach ($si->r as $r) $text .= (string)$r->t;
                $strings[] = $text;
            }
        }
    }

    // 2. Workbook — get sheet names & IDs
    $workbookXml = $zip->getFromName('xl/workbook.xml');
    $sheet_names = [];
    if ($workbookXml) {
        $wb = simplexml_load_string($workbookXml);
        $wb->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $sheets = $wb->xpath('//ns:sheet');
        foreach ($sheets as $s) {
            $attrs = $s->attributes();
            $rid   = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $sheet_names[$rid] = (string)$attrs['name'];
        }
    }

    // 3. Relationships — map rId → sheet file
    $relsXml  = $zip->getFromName('xl/_rels/workbook.xml.rels');
    $sheet_files = []; // rid → filename
    if ($relsXml) {
        $rels = simplexml_load_string($relsXml);
        foreach ($rels->Relationship as $rel) {
            $id     = (string)$rel['Id'];
            $target = (string)$rel['Target'];
            if (strpos($target, 'sheet') !== false) {
                $sheet_files[$id] = 'xl/' . ltrim($target, '/');
            }
        }
    }

    // 4. Read each sheet
    $all_sheets = [];
    foreach ($sheet_names as $rid => $name) {
        $file = $sheet_files[$rid] ?? null;
        if (!$file) continue;

        $sheetXml = $zip->getFromName($file);
        if (!$sheetXml) continue;

        $rows = parseSheet($sheetXml, $strings);
        if (!empty($rows)) {
            $all_sheets[$name] = $rows;
        }
    }

    $zip->close();
    return $all_sheets;
}

// ── Parse one sheet XML into rows array ──
function parseSheet($sheetXml, $strings) {
    $xml  = simplexml_load_string($sheetXml);
    $rows = [];

    foreach ($xml->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $cell) {
            $type  = (string)$cell['t'];
            $raw   = (string)$cell->v;

            if ($type === 's') {
                $value = $strings[(int)$raw] ?? '';
            } elseif ($type === 'b') {
                $value = $raw ? 'TRUE' : 'FALSE';
            } else {
                $value = $raw;
            }

            $colRef = preg_replace('/[0-9]/', '', (string)$cell['r']);
            $colIdx = 0;
            for ($i = 0; $i < strlen($colRef); $i++) {
                $colIdx = $colIdx * 26 + (ord($colRef[$i]) - ord('A') + 1);
            }
            $rowData[$colIdx - 1] = $value;
        }

        if (!empty($rowData)) {
            $max  = max(array_keys($rowData));
            $full = [];
            for ($i = 0; $i <= $max; $i++) $full[] = $rowData[$i] ?? '';
            $rows[] = $full;
        }
    }

    return $rows;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Excel – ADMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <?php include "sidebar.php"; ?>

    <main class="main-content animate-fadeIn" style="max-width:780px;">

        <div class="page-header">
            <div>
                <h1>Import from Excel</h1>
                <p class="page-sub">Upload your Excel file — all sheets (terms) will be imported automatically.</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" enctype="multipart/form-data">

                <div class="drop-zone" id="dropZone"
                     onclick="document.getElementById('excel_file').click()">
                    <div class="dz-icon">📊</div>
                    <div class="dz-title">Click to choose file or drag & drop</div>
                    <div class="dz-sub">Supports .xlsx and .csv — all sheets will be imported</div>
                    <div class="dz-file" id="fileName"></div>
                </div>

                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.csv">

                <button type="submit" class="btn btn-primary"
                        style="width:100%; justify-content:center; padding:13px; font-size:15px;">
                    📥 Import All Sheets
                </button>
            </form>

            <!--<div class="column-guide" style="margin-top:24px;">
                <div class="guide-title">Expected Column Names (first row = header)</div>
                <div class="cols-grid">
                    <span class="col-tag">المقر</span>
                    <span class="col-tag">الجنس</span>
                    <span class="col-tag">الدرجة العلمية</span>
                    <span class="col-tag">الكلية</span>
                    <span class="col-tag">التخصص</span>
                    <span class="col-tag">نوع الدراسة</span>
                    <span class="col-tag">رقم الطالب</span>
                    <span class="col-tag">اسم الطالب</span>
                    <span class="col-tag">السجل المدني</span>
                    <span class="col-tag">الجنسية</span>
                    <span class="col-tag">المعدل</span>
                    <span class="col-tag">التقدير</span>
                    <span class="col-tag">الجوال</span>
                    <span class="col-tag">البريد الإلكتروني</span>
                    <span class="col-tag">مرتبة الشرف</span>
                </div> -->
                <p style="font-size:12px; color:var(--muted); margin-top:12px;">
                    💡 Each sheet name will be saved as the graduation term automatically.<br>
                    💡 Duplicate records (same student ID) will be skipped.
                </p>
            </div>
        </div>

    </main>
</div>

<script>
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('excel_file');
const fileName  = document.getElementById('fileName');

fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) fileName.textContent = '📎 ' + fileInput.files[0].name;
});
dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    fileInput.files = e.dataTransfer.files;
    if (fileInput.files[0]) fileName.textContent = '📎 ' + fileInput.files[0].name;
});
</script>
</body>
</html>
