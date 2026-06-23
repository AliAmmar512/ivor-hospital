<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Consultants";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID,
        d.Name,
        con.Specialty,
        CONVERT(VARCHAR(10), d.DateJoinedTeam, 120) AS DateJoinedTeam,
        (SELECT COUNT(*) FROM dbo.DOCTOR t WHERE t.ConsultantID = d.DoctorID) AS TeamSize
    FROM dbo.DOCTOR    d
    JOIN dbo.CONSULTANT con ON d.DoctorID = con.DoctorID
    ORDER BY d.DoctorID
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
            <h2>&#x2695; Consultants</h2>
            <p>Senior consultants and their medical specialties.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> consultant<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Date Joined</th>
                    <th>Team Size</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['DoctorID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($row['Specialty']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['DateJoinedTeam']); ?></td>
                    <td><span class="badge badge-blue"><?php echo htmlspecialchars($row['TeamSize']); ?> member<?php echo $row['TeamSize'] != 1 ? 's' : ''; ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="no-records">No consultants found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
