<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 1: Consultant Teams";
$conn = get_db_connection();

$sql = "
    SELECT
        con.DoctorID                                        AS ConsultantID,
        con.Name                                            AS ConsultantName,
        cs.Specialty,
        CONVERT(VARCHAR(10), con.DateJoinedTeam, 120)       AS ConsultantJoined,
        d.DoctorID                                          AS MemberID,
        d.Name                                              AS MemberName,
        d.Position                                          AS MemberPosition,
        CONVERT(VARCHAR(10), d.DateJoinedTeam, 120)         AS MemberJoined
    FROM dbo.DOCTOR     con
    JOIN dbo.CONSULTANT cs  ON con.DoctorID   = cs.DoctorID
    LEFT JOIN dbo.DOCTOR d  ON d.ConsultantID = con.DoctorID
    ORDER BY con.DoctorID, d.Position, d.DoctorID
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
            <h2>Report 1 &mdash; Consultant Teams</h2>
            <p>All consultants with every member of their team, their positions, and join dates.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> row<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Consultant ID</th>
                    <th>Consultant Name</th>
                    <th>Specialty</th>
                    <th>Consultant Joined</th>
                    <th>Member ID</th>
                    <th>Member Name</th>
                    <th>Member Position</th>
                    <th>Member Joined</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?php echo htmlspecialchars($r['ConsultantID']); ?></td>
                    <td><?php echo htmlspecialchars($r['ConsultantName']); ?></td>
                    <td><span class="badge badge-teal"><?php echo htmlspecialchars($r['Specialty']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['ConsultantJoined']); ?></td>
                    <td><?php echo $r['MemberID']   ? htmlspecialchars($r['MemberID'])   : '<span style="color:#aaa;">—</span>'; ?></td>
                    <td><?php echo $r['MemberName'] ? htmlspecialchars($r['MemberName']) : '<span style="color:#aaa;">No team members</span>'; ?></td>
                    <td>
                        <?php if ($r['MemberPosition']): ?>
                        <?php
                        $cls = 'badge-gray';
                        switch ($r['MemberPosition']) {
                            case 'Registrar':           $cls = 'badge-navy';   break;
                            case 'Assistant Registrar': $cls = 'badge-purple'; break;
                            case 'Senior Houseman':     $cls = 'badge-blue';   break;
                            case 'Junior Houseman':     $cls = 'badge-teal';   break;
                            case 'Student':             $cls = 'badge-gray';   break;
                        }
                        ?>
                        <span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($r['MemberPosition']); ?></span>
                        <?php else: echo '<span style="color:#aaa;">—</span>'; endif; ?>
                    </td>
                    <td><?php echo $r['MemberJoined'] ? htmlspecialchars($r['MemberJoined']) : '<span style="color:#aaa;">—</span>'; ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="no-records">No data found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
