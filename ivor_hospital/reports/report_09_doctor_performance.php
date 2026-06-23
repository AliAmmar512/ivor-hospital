<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 9: Doctor Performance";
$conn = get_db_connection();

// Build doctor list for dropdown (non-consultants only — consultants are not graded)
$doctorsSql = "SELECT DoctorID, Name, Position FROM dbo.DOCTOR WHERE Position <> 'Consultant' ORDER BY Name";
$doctorsRes  = sqlsrv_query($conn, $doctorsSql);
$doctors     = array();
if ($doctorsRes) {
    while ($dr = sqlsrv_fetch_array($doctorsRes, SQLSRV_FETCH_ASSOC)) {
        $doctors[] = $dr;
    }
}

$selectedDoctor = isset($_GET['doctor_id']) ? trim($_GET['doctor_id']) : '';
$rows           = array();
$doctorInfo     = null;
$count          = 0;

if ($selectedDoctor !== '') {
    // Fetch doctor header info
    $infoSql    = "SELECT DoctorID, Name, Position FROM dbo.DOCTOR WHERE DoctorID = ?";
    $infoRes    = sqlsrv_query($conn, $infoSql, array($selectedDoctor));
    if ($infoRes) {
        $doctorInfo = sqlsrv_fetch_array($infoRes, SQLSRV_FETCH_ASSOC);
    }

    // Fetch grades
    $gradeSql = "
        SELECT
            pg.SNO,
            pg.Grade,
            CONVERT(VARCHAR(10), pg.DateOfGrade, 120)   AS DateOfGrade,
            pg.Remarks,
            con.DoctorID                                AS ConsultantID,
            con.Name                                    AS ConsultantName,
            cs.Specialty
        FROM dbo.PERF_GRADE pg
        JOIN  dbo.DOCTOR    con ON pg.ConsultantID = con.DoctorID
        JOIN  dbo.CONSULTANT cs ON cs.DoctorID     = con.DoctorID
        WHERE pg.DoctorID = ?
        ORDER BY pg.DateOfGrade DESC, pg.SNO DESC
    ";
    $gradeRes = sqlsrv_query($conn, $gradeSql, array($selectedDoctor));
    if ($gradeRes) {
        while ($row = sqlsrv_fetch_array($gradeRes, SQLSRV_FETCH_ASSOC)) {
            $rows[] = $row;
            $count++;
        }
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 9 &mdash; Doctor Performance Grades</h2>
            <p>Select a doctor to view all their performance assessments issued by consultants.</p>
        </div>
        <?php if ($selectedDoctor !== ''): ?>
            <span class="record-pill"><?php echo $count; ?> grade<?php echo $count !== 1 ? 's' : ''; ?></span>
        <?php endif; ?>
    </div>

    <!-- Filter form -->
    <div class="report-filter no-print">
        <form method="GET" action="">
            <div>
                <label for="doctor_id">Doctor</label>
                <select name="doctor_id" id="doctor_id">
                    <option value="">-- Select a doctor --</option>
                    <?php foreach ($doctors as $doc): ?>
                        <option value="<?php echo htmlspecialchars($doc['DoctorID']); ?>"
                            <?php echo ($doc['DoctorID'] === $selectedDoctor) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($doc['Name'] . ' (' . $doc['Position'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">View Grades</button>
            <?php if ($selectedDoctor !== ''): ?>
                <a href="report_09_doctor_performance.php" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($selectedDoctor !== '' && $doctorInfo): ?>
    <div class="record-card" style="margin:0 0 20px 0;">
        <div class="record-card-header" style="display:flex;justify-content:space-between;align-items:center;text-align:left;">
            <div>
                <span style="font-size:1.1rem;font-weight:600;"><?php echo htmlspecialchars($doctorInfo['Name']); ?></span>
                <span style="margin-left:12px;opacity:.75;font-size:.85rem;"><?php echo htmlspecialchars($doctorInfo['DoctorID']); ?></span>
            </div>
            <span class="badge badge-navy"><?php echo htmlspecialchars($doctorInfo['Position']); ?></span>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>SNO</th>
                    <th>Grade</th>
                    <th>Date of Grade</th>
                    <th>Graded By</th>
                    <th>Specialty</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <?php
                    $gradeLetter = strtoupper(trim($r['Grade']));
                    $gradeClass  = 'grade-' . strtolower($gradeLetter);
                    $gradeLabels = array('A'=>'Outstanding','B'=>'Good','C'=>'Satisfactory','D'=>'Needs Improvement','E'=>'Unsatisfactory');
                    $gradeLabel  = isset($gradeLabels[$gradeLetter]) ? $gradeLabels[$gradeLetter] : $gradeLetter;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['SNO']); ?></td>
                    <td>
                        <span class="badge <?php echo $gradeClass; ?>"><?php echo htmlspecialchars($gradeLetter); ?></span>
                        <br><small style="color:#7090a8;"><?php echo $gradeLabel; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($r['DateOfGrade']); ?></td>
                    <td><?php echo htmlspecialchars($r['ConsultantName']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($r['Specialty']); ?></span></td>
                    <td><?php echo $r['Remarks'] ? htmlspecialchars($r['Remarks']) : '<span style="color:#aaa;">—</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-records">No performance grades found for this doctor.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php elseif ($selectedDoctor !== ''): ?>
        <p style="padding:20px;color:#aaa;">Doctor not found.</p>
    <?php else: ?>
        <p style="padding:20px;color:#7090a8;">Select a doctor above to view their performance grades.</p>
    <?php endif; ?>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
