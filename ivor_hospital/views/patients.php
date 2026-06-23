<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Patients";
$conn = get_db_connection();

$sql = "
    SELECT
        p.PatientNo,
        p.PatientName,
        CONVERT(VARCHAR(10), p.DateOfBirth,  120) AS DateOfBirth,
        CONVERT(VARCHAR(10), p.DateAdmitted, 120) AS DateAdmitted,
        w.WardName,
        p.CareUnitNo,
        p.BedNo,
        d.Name     AS DoctorName,
        d.Position AS DoctorPosition
    FROM dbo.PATIENT p
    JOIN dbo.WARD   w ON p.WardID   = w.WardID
    JOIN dbo.DOCTOR d ON p.DoctorID = d.DoctorID
    ORDER BY p.PatientNo
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
            <h2>&#x1F6CF; Patients</h2>
            <p>All currently admitted patients with ward and doctor assignments.</p>
        </div>
        <span class="record-pill"><?php echo $count; ?> patient<?php echo $count !== 1 ? 's' : ''; ?></span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Patient No</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Date Admitted</th>
                    <th>Ward</th>
                    <th>Care Unit</th>
                    <th>Bed No</th>
                    <th>Doctor</th>
                    <th>Doctor Position</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0): ?>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['PatientNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['PatientName']); ?></td>
                    <td><?php echo htmlspecialchars($row['DateOfBirth']); ?></td>
                    <td><?php echo htmlspecialchars($row['DateAdmitted']); ?></td>
                    <td><?php echo htmlspecialchars($row['WardName']); ?></td>
                    <td><?php echo htmlspecialchars($row['CareUnitNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['BedNo']); ?></td>
                    <td><?php echo htmlspecialchars($row['DoctorName']); ?></td>
                    <td>
                        <?php
                        $pos = $row['DoctorPosition'];
                        $cls = 'badge-gray';
                        if ($pos === 'Consultant')          $cls = 'badge-dark';
                        elseif ($pos === 'Registrar')       $cls = 'badge-navy';
                        elseif ($pos === 'Senior Houseman') $cls = 'badge-blue';
                        elseif ($pos === 'Junior Houseman') $cls = 'badge-teal';
                        elseif ($pos === 'Student')         $cls = 'badge-gray';
                        else                                $cls = 'badge-purple';
                        ?>
                        <span class="badge <?php echo $cls; ?>"><?php echo htmlspecialchars($pos); ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="no-records">No patients found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
