<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Consultant Team Record";
$conn = get_db_connection();

// Load all doctors for the dropdown
$allDoctors = sqlsrv_query($conn,
    "SELECT DoctorID, Name, Position FROM dbo.DOCTOR ORDER BY DoctorID"
);

$selectedID  = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$doctor      = null;
$experience  = array();
$grades      = array();
$teamMembers = array();   // used only when the selected doctor is a consultant

if ($selectedID > 0) {

    // ── Doctor header info ──────────────────────────────────
    $dSql = "
        SELECT
            d.DoctorID,
            d.Name,
            d.Position,
            CONVERT(VARCHAR(10), d.DateJoinedTeam, 120) AS DateJoinedTeam,
            CASE
                WHEN d.Position = 'Consultant' THEN 'Self (Consultant)'
                ELSE ISNULL(con.Name, 'N/A')
            END AS ConsultantName,
            CASE
                WHEN d.Position = 'Consultant' THEN d.DoctorID
                ELSE ISNULL(con.DoctorID, 0)
            END AS ConsultantID,
            ISNULL(cs.Specialty, '') AS Specialty
        FROM dbo.DOCTOR d
        LEFT JOIN dbo.DOCTOR     con ON d.ConsultantID = con.DoctorID
        LEFT JOIN dbo.CONSULTANT cs  ON d.DoctorID     = cs.DoctorID
        WHERE d.DoctorID = ?
    ";
    $dRes = sqlsrv_query($conn, $dSql, array($selectedID));
    if ($dRes) {
        $doctor = sqlsrv_fetch_array($dRes, SQLSRV_FETCH_ASSOC);
    }

    if ($doctor) {

        // ── Previous experience ─────────────────────────────
        $eSql = "
            SELECT
                pe.SNO,
                CONVERT(VARCHAR(10), pe.FromDate, 120) AS FromDate,
                CASE WHEN pe.ToDate IS NULL
                     THEN NULL
                     ELSE CONVERT(VARCHAR(10), pe.ToDate, 120)
                END AS ToDate,
                pe.Position,
                pe.Establishment
            FROM dbo.PREV_EXPERIENCE pe
            WHERE pe.DoctorID = ?
            ORDER BY pe.SNO
        ";
        $eRes = sqlsrv_query($conn, $eSql, array($selectedID));
        if ($eRes) {
            while ($row = sqlsrv_fetch_array($eRes, SQLSRV_FETCH_ASSOC)) {
                $experience[] = $row;
            }
        }

        // ── Performance grades ──────────────────────────────
        $gSql = "
            SELECT
                pg.SNO,
                CONVERT(VARCHAR(10), pg.GradeDate, 120) AS GradeDate,
                pg.Grade,
                con.Name AS GradedBy
            FROM dbo.PERF_GRADE pg
            JOIN dbo.DOCTOR con ON pg.ConsultantID = con.DoctorID
            WHERE pg.DoctorID = ?
            ORDER BY pg.SNO
        ";
        $gRes = sqlsrv_query($conn, $gSql, array($selectedID));
        if ($gRes) {
            while ($row = sqlsrv_fetch_array($gRes, SQLSRV_FETCH_ASSOC)) {
                $grades[] = $row;
            }
        }

        // ── If consultant: also load their team members ─────
        if ($doctor['Position'] === 'Consultant') {
            $tSql = "
                SELECT
                    d.DoctorID,
                    d.Name,
                    d.Position,
                    CONVERT(VARCHAR(10), d.DateJoinedTeam, 120) AS DateJoinedTeam
                FROM dbo.DOCTOR d
                WHERE d.ConsultantID = ?
                ORDER BY d.DoctorID
            ";
            $tRes = sqlsrv_query($conn, $tSql, array($selectedID));
            if ($tRes) {
                while ($row = sqlsrv_fetch_array($tRes, SQLSRV_FETCH_ASSOC)) {
                    $teamMembers[] = $row;
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="record-page">

    <!-- ── Selection form ───────────────────────────────── -->
    <div class="select-form no-print">
        <div class="form-group">
            <label for="doctor_id">Select Staff Member</label>
            <select id="doctor_id">
                <option value="">— Choose a doctor —</option>
                <?php
                if ($allDoctors) {
                    while ($r = sqlsrv_fetch_array($allDoctors, SQLSRV_FETCH_ASSOC)) {
                        $sel = ($selectedID == $r['DoctorID']) ? ' selected' : '';
                        echo '<option value="' . $r['DoctorID'] . '"' . $sel . '>'
                           . '#' . $r['DoctorID'] . ' — ' . htmlspecialchars($r['Name'])
                           . ' (' . htmlspecialchars($r['Position']) . ')</option>';
                    }
                }
                ?>
            </select>
        </div>
        <button class="btn btn-primary" onclick="viewRecord()">&#x1F50D; View Record</button>
        <?php if ($doctor): ?>
            <button class="btn btn-secondary" onclick="window.print()">&#x1F5A8; Print Record</button>
        <?php endif; ?>
    </div>

    <?php if ($selectedID > 0 && !$doctor): ?>
        <div class="alert alert-error">No staff member found with Doctor ID <?php echo $selectedID; ?>.</div>

    <?php elseif ($doctor): ?>

    <!-- ── Official Record Card ─────────────────────────── -->
    <div class="record-card">

        <div class="record-card-header">
            <h1>IVOR PAINE MEMORIAL HOSPITAL</h1>
            <h2>CONSULTANT TEAM RECORD</h2>
        </div>

        <div class="record-card-body">

            <!-- Staff info grid -->
            <div class="record-info-grid">
                <div class="record-info-item">
                    <label>Staff No</label>
                    <p><?php echo htmlspecialchars($doctor['DoctorID']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Name</label>
                    <p><?php echo htmlspecialchars($doctor['Name']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Position</label>
                    <p><?php echo htmlspecialchars($doctor['Position']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Date Joined Team</label>
                    <p><?php echo htmlspecialchars($doctor['DateJoinedTeam']); ?></p>
                </div>
                <div class="record-info-item">
                    <label>Supervising Consultant</label>
                    <p><?php echo htmlspecialchars($doctor['ConsultantName']); ?></p>
                </div>
                <?php if ($doctor['Specialty'] !== ''): ?>
                <div class="record-info-item">
                    <label>Specialty</label>
                    <p><?php echo htmlspecialchars($doctor['Specialty']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- If consultant: show team members -->
            <?php if ($doctor['Position'] === 'Consultant' && count($teamMembers) > 0): ?>
            <div class="record-section-title">Team Members</div>
            <div class="record-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Doctor ID</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Date Joined Team</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($teamMembers as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['DoctorID']); ?></td>
                            <td><?php echo htmlspecialchars($t['Name']); ?></td>
                            <td><?php echo htmlspecialchars($t['Position']); ?></td>
                            <td><?php echo htmlspecialchars($t['DateJoinedTeam']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- Previous Experience table -->
            <div class="record-section-title">Previous Experience</div>

            <?php if (count($experience) > 0): ?>
            <div class="record-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>SNO</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Position Held</th>
                            <th>Establishment</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($experience as $e): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($e['SNO']); ?></td>
                            <td><?php echo htmlspecialchars($e['FromDate']); ?></td>
                            <td>
                                <?php if ($e['ToDate']): ?>
                                    <?php echo htmlspecialchars($e['ToDate']); ?>
                                <?php else: ?>
                                    <span class="badge badge-green">Current</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($e['Position']); ?></td>
                            <td><?php echo htmlspecialchars($e['Establishment']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="color:#aaa;font-style:italic;padding:10px 0;">No previous experience on record.</p>
            <?php endif; ?>

            <!-- Performance Grades / Progress table -->
            <div class="record-section-title">Progress &amp; Performance Grades</div>

            <?php if (count($grades) > 0): ?>
            <div class="record-table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>SNO</th>
                            <th>Grade Date</th>
                            <th>Grade</th>
                            <th>Graded By</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($grades as $g): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($g['SNO']); ?></td>
                            <td><?php echo htmlspecialchars($g['GradeDate']); ?></td>
                            <td><span class="grade-<?php echo strtolower($g['Grade']); ?>"><?php echo htmlspecialchars($g['Grade']); ?></span></td>
                            <td><?php echo htmlspecialchars($g['GradedBy']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="color:#aaa;font-style:italic;padding:10px 0;">
                    <?php echo $doctor['Position'] === 'Consultant'
                        ? 'Consultants are not subject to performance grading.'
                        : 'No performance grades on record.'; ?>
                </p>
            <?php endif; ?>

        </div><!-- end record-card-body -->
    </div><!-- end record-card -->

    <?php endif; ?>

</div><!-- end record-page -->

<script>
function viewRecord() {
    var val = document.getElementById('doctor_id').value;
    if (!val) { alert('Please select a staff member.'); return; }
    window.location.href = '<?php echo BASE_URL; ?>/manual_records/consultant_team_record.php?doctor_id=' + val;
}
</script>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
