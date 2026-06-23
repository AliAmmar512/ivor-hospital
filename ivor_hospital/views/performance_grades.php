<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Performance Grades";
$conn = get_db_connection();

$sql = "
    SELECT
        d.DoctorID,
        d.Name     AS DoctorName,
        d.Position AS DoctorPosition,
        pg.SNO,
        c.Name     AS GradedBy,
        CONVERT(VARCHAR(10), pg.GradeDate, 120) AS GradeDate,
        pg.Grade
    FROM dbo.PERF_GRADE pg
    JOIN dbo.DOCTOR d ON pg.DoctorID     = d.DoctorID
    JOIN dbo.DOCTOR c ON pg.ConsultantID = c.DoctorID
    ORDER BY pg.DoctorID, pg.SNO
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
            <h2>&#x2B50; Performance Grades</h2>
            <p>Consultant-assigned performance grades for all medical staff.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> grade<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Doctor Name</th>
                    <th>Position</th>
                    <th>SNO</th>
                    <th>Graded By</th>
                    <th>Grade Date</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['DoctorID']); ?></td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                    <td><span class="badge badge-navy"><?php echo htmlspecialchars($row['DoctorPosition']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['SNO']); ?></td>
                    <td><?php echo htmlspecialchars($row['GradedBy']); ?></td>
                    <td><?php echo htmlspecialchars($row['GradeDate']); ?></td>
                    <td>
                        <?php
                        $g = $row['Grade'];
                        echo '<span class="grade-' . strtolower($g) . '">' . htmlspecialchars($g) . '</span>';
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-records">No performance grades found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
