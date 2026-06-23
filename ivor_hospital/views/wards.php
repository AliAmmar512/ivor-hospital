<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Wards";
$conn = get_db_connection();

$sql = "
    SELECT
        w.WardID,
        w.WardName,
        w.Specialty,
        (SELECT COUNT(*) FROM dbo.BED     b WHERE b.WardID = w.WardID) AS BedCount,
        (SELECT COUNT(*) FROM dbo.PATIENT p WHERE p.WardID = w.WardID) AS PatientCount,
        (SELECT COUNT(*) FROM dbo.NURSE   n WHERE n.WardID = w.WardID) AS NurseCount,
        (SELECT COUNT(*) FROM dbo.CARE_UNIT cu WHERE cu.WardID = w.WardID) AS CUCount
    FROM dbo.WARD w
    ORDER BY w.WardID
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
            <h2>&#x1F3D7; Wards</h2>
            <p>All hospital wards with bed, patient, and staff counts.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> ward<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Ward ID</th>
                    <th>Ward Name</th>
                    <th>Specialty</th>
                    <th>Care Units</th>
                    <th>Beds</th>
                    <th>Nurses</th>
                    <th>Patients</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['WardID']); ?></td>
                    <td><?php echo htmlspecialchars($row['WardName']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($row['Specialty']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['CUCount']); ?></td>
                    <td><?php echo htmlspecialchars($row['BedCount']); ?></td>
                    <td><?php echo htmlspecialchars($row['NurseCount']); ?></td>
                    <td>
                        <?php if ($row['PatientCount'] > 0): ?>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($row['PatientCount']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-gray">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-records">No wards found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
