<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Complaints";
$conn = get_db_connection();

$sql = "
    SELECT
        c.ComplaintCode,
        c.Description,
        (SELECT COUNT(*) FROM dbo.HAS_COMPLAINT hc WHERE hc.ComplaintCode = c.ComplaintCode) AS PatientCount,
        (SELECT COUNT(*) FROM dbo.TREATED_WITH  tw WHERE tw.ComplaintCode = c.ComplaintCode) AS TreatmentCount
    FROM dbo.COMPLAINT c
    ORDER BY c.ComplaintCode
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
            <h2>&#x1F4CB; Complaints</h2>
            <p>All medical complaints with patient and treatment counts.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> complaint<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Complaint Code</th>
                    <th>Description</th>
                    <th>Patients Affected</th>
                    <th>Treatments Available</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ComplaintCode']); ?></td>
                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                    <td>
                        <?php if ($row['PatientCount'] > 0): ?>
                            <span class="badge badge-orange"><?php echo htmlspecialchars($row['PatientCount']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-gray">0</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($row['TreatmentCount']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="no-records">No complaints found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
