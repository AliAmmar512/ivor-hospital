<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Previous Experience";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID,
        d.Name            AS DoctorName,
        d.Position        AS CurrentPosition,
        pe.SNO,
        CONVERT(VARCHAR(10), pe.FromDate, 120) AS FromDate,
        CASE WHEN pe.ToDate IS NULL
             THEN NULL
             ELSE CONVERT(VARCHAR(10), pe.ToDate, 120)
        END AS ToDate,
        pe.Position       AS PreviousPosition,
        pe.Establishment
    FROM dbo.PREV_EXPERIENCE pe
    JOIN dbo.DOCTOR d ON pe.DoctorID = d.DoctorID
    ORDER BY pe.DoctorID, pe.SNO
";
$result = sqlsrv_query($conn, $sql);

$count = 0;
$rows  = array();
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        $count++;
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>&#x1F4BC; Previous Experience</h2>
            <p>Doctors' employment history prior to joining this hospital.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> record<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Doctor Name</th>
                    <th>Current Position</th>
                    <th>SNO</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Previous Position</th>
                    <th>Establishment</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['DoctorID']); ?></td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                    <td><span class="badge badge-navy"><?php echo htmlspecialchars($row['CurrentPosition']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['SNO']); ?></td>
                    <td><?php echo htmlspecialchars($row['FromDate']); ?></td>
                    <td><?php echo $row['ToDate'] ? htmlspecialchars($row['ToDate']) : '<span class="badge badge-green">Current</span>'; ?></td>
                    <td><?php echo htmlspecialchars($row['PreviousPosition']); ?></td>
                    <td><?php echo htmlspecialchars($row['Establishment']); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="no-records">No experience records found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
