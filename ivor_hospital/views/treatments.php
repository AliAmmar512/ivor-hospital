<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Treatments";
$conn = get_db_connection();

$sql = "
    SELECT
        t.TreatmentCode,
        t.Description,
        (SELECT COUNT(*) FROM dbo.TREATED_WITH  tw WHERE tw.TreatmentCode = t.TreatmentCode) AS LinkedComplaints,
        (SELECT COUNT(*) FROM dbo.MEDICAL_HISTORY mh WHERE mh.TreatmentCode = t.TreatmentCode) AS TimesUsed
    FROM dbo.TREATMENT t
    ORDER BY t.TreatmentCode
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
            <h2>&#x1F489; Treatments</h2>
            <p>All treatment codes with usage statistics.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> treatment<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Treatment Code</th>
                    <th>Description</th>
                    <th>Linked Complaints</th>
                    <th>Times Used</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['TreatmentCode']); ?></td>
                    <td><?php echo htmlspecialchars($row['Description']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($row['LinkedComplaints']); ?></span></td>
                    <td>
                        <?php if ($row['TimesUsed'] > 0): ?>
                            <span class="badge badge-blue"><?php echo htmlspecialchars($row['TimesUsed']); ?></span>
                        <?php else: ?>
                            <span class="badge badge-gray">0</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="no-records">No treatments found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
