<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Medical History";
$conn = get_db_connection();

$sql = "
    SELECT
        mh.PatientNo,
        p.PatientName,
        mh.ComplaintCode,
        c.Description  AS ComplaintDesc,
        mh.TreatmentCode,
        t.Description  AS TreatmentDesc,
        d.Name         AS DoctorName,
        d.Position     AS DoctorPosition,
        mh.SNO,
        CONVERT(VARCHAR(10), mh.DateStarted, 120) AS DateStarted,
        CASE WHEN mh.DateEnded IS NULL
             THEN NULL
             ELSE CONVERT(VARCHAR(10), mh.DateEnded, 120)
        END AS DateEnded
    FROM dbo.MEDICAL_HISTORY mh
    JOIN dbo.PATIENT   p ON mh.PatientNo    = p.PatientNo
    JOIN dbo.COMPLAINT c ON mh.ComplaintCode = c.ComplaintCode
    JOIN dbo.TREATMENT t ON mh.TreatmentCode = t.TreatmentCode
    JOIN dbo.DOCTOR    d ON mh.DoctorID      = d.DoctorID
    ORDER BY mh.PatientNo, mh.ComplaintCode, mh.SNO
";
$result = sqlsrv_query($conn, $sql);

$count  = 0;
$active = 0;
$rows   = array();
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        $count++;
        if ($row['DateEnded'] === null) $active++;
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>&#x1F4D6; Medical History</h2>
            <p>
                <span class="badge badge-green" style="margin-right:6px;">&#x25CF; <?php echo $active; ?> Active</span>
                <span class="badge badge-gray">&#x25CF; <?php echo $count - $active; ?> Completed</span>
            </p>
        </div>
        <span class="record-pill"><?php echo $count; ?> record<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Complaint</th>
                    <th>Treatment</th>
                    <th>Doctor</th>
                    <th>SNO</th>
                    <th>Date Started</th>
                    <th>Date Ended</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                    <td>
                        <span class="badge badge-orange"><?php echo htmlspecialchars($row['ComplaintCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($row['ComplaintDesc']); ?></small>
                    </td>
                    <td>
                        <span class="badge badge-blue"><?php echo htmlspecialchars($row['TreatmentCode']); ?></span>
                        <br><small style="color:#7090a8;"><?php echo htmlspecialchars($row['TreatmentDesc']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                    <td><?php echo htmlspecialchars($row['SNO']); ?></td>
                    <td><?php echo htmlspecialchars($row['DateStarted']); ?></td>
                    <td><?php echo $row['DateEnded'] ? htmlspecialchars($row['DateEnded']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td>
                        <?php if ($row['DateEnded'] === null): ?>
                            <span class="badge badge-green">Active</span>
                        <?php else: ?>
                            <span class="badge badge-gray">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="no-records">No medical history records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
