<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 5: Unique Specialties";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID,
        d.Name,
        cs.Specialty,
        CONVERT(VARCHAR(10), d.DateJoinedTeam, 120)     AS DateJoinedTeam,
        (SELECT COUNT(*) FROM dbo.DOCTOR t WHERE t.ConsultantID = d.DoctorID) AS TeamSize
    FROM dbo.DOCTOR     d
    JOIN dbo.CONSULTANT cs ON d.DoctorID = cs.DoctorID
    WHERE cs.Specialty IN (
        SELECT   Specialty
        FROM     dbo.CONSULTANT
        GROUP BY Specialty
        HAVING   COUNT(*) = 1
    )
    ORDER BY d.DoctorID
";
$result = sqlsrv_query($conn, $sql);

$rows  = array();
$count = 0;
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
            <h2>Report 5 &mdash; Consultants with a Unique Specialty</h2>
            <p>Consultants whose medical specialty is not shared by any other consultant in the hospital.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> consultant<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Consultant Name</th>
                    <th>Specialty</th>
                    <th>Date Joined</th>
                    <th>Team Size</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['DoctorID']); ?></td>
                    <td><?php echo htmlspecialchars($r['Name']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($r['Specialty']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['DateJoinedTeam']); ?></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($r['TeamSize']); ?> member<?php echo $r['TeamSize'] != 1 ? 's' : ''; ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="no-records">No consultants with a unique specialty found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
