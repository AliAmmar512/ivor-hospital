<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 11: Treatments Between Dates";
$conn = get_db_connection();

// Build complaint list for dropdown
$compSql = "SELECT ComplaintCode, Description FROM dbo.COMPLAINT ORDER BY ComplaintCode";
$compRes  = sqlsrv_query($conn, $compSql);
$complaints = array();
if ($compRes) {
    while ($cr = sqlsrv_fetch_array($compRes, SQLSRV_FETCH_ASSOC)) {
        $complaints[] = $cr;
    }
}

$selectedComplaint = isset($_GET['complaint_code']) ? trim($_GET['complaint_code']) : '';
$startDate         = isset($_GET['start_date'])     ? trim($_GET['start_date'])     : '';
$endDate           = isset($_GET['end_date'])       ? trim($_GET['end_date'])       : '';
$rows              = array();
$count             = 0;
$filterError       = '';

$filtered = ($selectedComplaint !== '' && $startDate !== '' && $endDate !== '');

if ($filtered) {
    if ($startDate > $endDate) {
        $filterError = 'Start date cannot be later than end date.';
    } else {
        $sql = "
            SELECT
                mh.ComplaintCode,
                c.Description                               AS ComplaintDesc,
                mh.TreatmentCode,
                t.Description                               AS TreatmentDesc,
                p.PatientNo,
                p.PatientName,
                d.Name                                      AS TreatingDoctor,
                d.Position                                  AS DoctorPosition,
                CONVERT(VARCHAR(10), mh.DateStarted, 120)   AS DateStarted,
                CASE WHEN mh.DateEnded IS NULL THEN NULL
                     ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
                END                                         AS DateEnded
            FROM dbo.MEDICAL_HISTORY mh
            JOIN  dbo.COMPLAINT  c  ON mh.ComplaintCode  = c.ComplaintCode
            JOIN  dbo.TREATMENT  t  ON mh.TreatmentCode  = t.TreatmentCode
            JOIN  dbo.PATIENT    p  ON mh.PatientNo       = p.PatientNo
            JOIN  dbo.DOCTOR     d  ON mh.DoctorID        = d.DoctorID
            WHERE mh.ComplaintCode = ?
              AND mh.DateStarted  >= ?
              AND mh.DateStarted  <= ?
            ORDER BY mh.TreatmentCode, mh.DateStarted, p.PatientNo
        ";
        $params = array($selectedComplaint, $startDate, $endDate);
        $result = sqlsrv_query($conn, $sql, $params);
        if ($result) {
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $rows[] = $row;
                $count++;
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 11 &mdash; Treatments Between Dates</h2>
            <p>Select a complaint and a date range to list all treatments started within that period.</p>
        </div>
        <?php if ($filtered && !$filterError): ?>
            <span class="record-pill"><?php echo $count; ?> record<?php echo $count !== 1 ? 's' : ''; ?></span>
        <?php endif; ?>
    </div>

    <!-- Filter form -->
    <div class="report-filter no-print">
        <form method="GET" action="">
            <div>
                <label for="complaint_code">Complaint</label>
                <select name="complaint_code" id="complaint_code" required>
                    <option value="">-- Select complaint --</option>
                    <?php foreach ($complaints as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['ComplaintCode']); ?>"
                            <?php echo ($c['ComplaintCode'] === $selectedComplaint) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['ComplaintCode'] . ' — ' . $c['Description']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="start_date">From Date</label>
                <input type="date" name="start_date" id="start_date"
                       value="<?php echo htmlspecialchars($startDate); ?>" required>
            </div>
            <div>
                <label for="end_date">To Date</label>
                <input type="date" name="end_date" id="end_date"
                       value="<?php echo htmlspecialchars($endDate); ?>" required>
            </div>
            <button type="submit" class="btn-primary">Search</button>
            <?php if ($filtered): ?>
                <a href="report_11_treatments_between_dates.php" class="btn-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($filterError): ?>
        <div class="alert alert-error" style="margin:16px 0;"><?php echo htmlspecialchars($filterError); ?></div>
    <?php elseif ($filtered): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Treatment</th>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Treating Doctor</th>
                    <th>Position</th>
                    <th>Date Started</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <span class="badge badge-blue"><?php echo htmlspecialchars($r['TreatmentCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($r['TreatmentDesc']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($r['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($r['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($r['TreatingDoctor']); ?></td>
                    <td><span class="badge badge-navy"><?php echo htmlspecialchars($r['DoctorPosition']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['DateStarted']); ?></td>
                    <td><?php echo $r['DateEnded'] ? htmlspecialchars($r['DateEnded']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td>
                        <?php if ($r['DateEnded'] === null): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Done</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="no-records">No treatments found for this complaint in the selected date range.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p style="padding:20px;color:#7090a8;">Select a complaint and date range above to run the report.</p>
    <?php endif; ?>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
