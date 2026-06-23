<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Doctors";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID,
        d.Name,
        d.Position,
        CONVERT(VARCHAR(10), d.DateJoinedTeam, 120) AS DateJoinedTeam,
        c.Name AS ConsultantName,
        d.ConsultantID
    FROM dbo.DOCTOR d
    LEFT JOIN dbo.DOCTOR c ON d.ConsultantID = c.DoctorID
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

// Helper to map position to badge class
function pos_badge($pos) {
    switch ($pos) {
        case 'Consultant':          return 'badge-dark';
        case 'Registrar':           return 'badge-navy';
        case 'Assistant Registrar': return 'badge-purple';
        case 'Senior Houseman':     return 'badge-blue';
        case 'Junior Houseman':     return 'badge-teal';
        case 'Student':             return 'badge-gray';
        default:                    return 'badge-gray';
    }
}

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>&#x1F3E5; Doctors</h2>
            <p>All medical staff with their positions and supervising consultants.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> doctor<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Date Joined Team</th>
                    <th>Supervising Consultant</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['DoctorID']); ?></td>
                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                    <td><span class="badge <?php echo pos_badge($row['Position']); ?>"><?php echo htmlspecialchars($row['Position']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['DateJoinedTeam']); ?></td>
                    <td>
                        <?php if ($row['ConsultantName']): ?>
                            <?php echo htmlspecialchars($row['ConsultantName']); ?>
                        <?php else: ?>
                            <span class="badge badge-dark">Consultant</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="no-records">No doctors found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
