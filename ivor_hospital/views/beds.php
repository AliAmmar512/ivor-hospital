<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Beds";
$conn = get_db_connection();

$sql = "
    SELECT
        b.BedNo,
        w.WardName,
        CASE WHEN p.PatientNo IS NOT NULL THEN 'Occupied' ELSE 'Available' END AS Status,
        p.PatientName,
        p.PatientNo
    FROM dbo.BED b
    JOIN  dbo.WARD    w ON b.WardID = w.WardID
    LEFT JOIN dbo.PATIENT p ON b.BedNo = p.BedNo
    ORDER BY b.WardID, b.BedNo
";
$result = sqlsrv_query($conn, $sql);

$count     = 0;
$occupied  = 0;
$rows      = array();
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        $count++;
        if ($row['Status'] === 'Occupied') $occupied++;
    }
}
$available = $count - $occupied;

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>&#x1F6CF; Beds</h2>
            <p>
                <span class="badge badge-red" style="margin-right:6px;">&#x25CF; <?php echo $occupied; ?> Occupied</span>
                <span class="badge badge-green">&#x25CF; <?php echo $available; ?> Available</span>
            </p>
        </div>
        <span class="record-pill"><?php echo $count; ?> total beds</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Bed No</th>
                    <th>Ward</th>
                    <th>Status</th>
                    <th>Occupied By</th>
                    <th>Patient No</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['BedNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['WardName']); ?></td>
                    <td>
                        <?php if ($row['Status'] === 'Occupied'): ?>
                            <span class="badge badge-red">Occupied</span>
                        <?php else: ?>
                            <span class="badge badge-green">Available</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['PatientName'] ? htmlspecialchars($row['PatientName']) : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $row['PatientNo']   ? htmlspecialchars($row['PatientNo'])   : '<span style="color:#aaa;">—</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="no-records">No beds found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
