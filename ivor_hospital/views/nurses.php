<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Nurses";
$conn = get_db_connection();

$sql = "
    SELECT
        n.NurseID,
        n.Name,
        n.Position,
        w.WardName,
        n.CareUnitNo,
        CASE WHEN cu.HeadNurseID = n.NurseID THEN 'Yes' ELSE 'No' END AS IsHeadNurse
    FROM dbo.NURSE     n
    JOIN dbo.WARD      w  ON n.WardID     = w.WardID
    JOIN dbo.CARE_UNIT cu ON n.CareUnitNo = cu.CareUnitNo
    ORDER BY n.NurseID
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

function nurse_badge($pos) {
    switch ($pos) {
        case 'Day Sister':            return 'badge-purple';
        case 'Night Sister':          return 'badge-navy';
        case 'Staff Nurse':           return 'badge-blue';
        case 'Non Registered Nurse':  return 'badge-gray';
        default:                      return 'badge-gray';
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>&#x1F469;&#x200D;&#x2695;&#xFE0F; Nurses</h2>
            <p>Nursing staff across all wards and care units.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> nurse<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nurse ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Ward</th>
                    <th>Care Unit</th>
                    <th>Head Nurse?</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['NurseID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><span class="badge <?php echo nurse_badge($row['Position']); ?>"><?php echo htmlspecialchars($row['Position']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['WardName']); ?></td>
                    <td><?php echo htmlspecialchars($row['CareUnitNo']); ?></td>
                    <td>
                        <?php if ($row['IsHeadNurse'] === 'Yes'): ?>
                            <span class="badge badge-green">&#x2713; Yes</span>
                        <?php else: ?>
                            <span style="color:#aaa;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="no-records">No nurses found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
