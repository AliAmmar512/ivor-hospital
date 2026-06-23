<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Report 12: Staff Positions Count";
$conn = get_db_connection();

$sql = "
    SELECT StaffType, Position, HeadCount
    FROM (
        SELECT
            'Doctor' AS StaffType,
            Position,
            COUNT(*) AS HeadCount
        FROM dbo.DOCTOR
        GROUP BY Position

        UNION ALL

        SELECT
            'Nurse' AS StaffType,
            Position,
            COUNT(*) AS HeadCount
        FROM dbo.NURSE
        GROUP BY Position
    ) AS combined
    ORDER BY StaffType DESC, HeadCount DESC
";
$result = sqlsrv_query($conn, $sql);

$rows        = array();
$totalDocs   = 0;
$totalNurses = 0;
if ($result) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
        if ($row['StaffType'] === 'Doctor') $totalDocs   += $row['HeadCount'];
        else                                $totalNurses += $row['HeadCount'];
    }
}
$grandTotal = $totalDocs + $totalNurses;

include '../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <div>
            <h2>Report 12 &mdash; Staff Count by Position</h2>
            <p>Number of doctors and nurses at each position / grade in the hospital.</p>
        </div>
        <span class="record-pill"><?php echo $grandTotal; ?> total staff</span>
    </div>

    <!-- Summary cards -->
    <div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
        <div class="record-card" style="flex:1;min-width:180px;margin:0;">
            <div class="record-card-header" style="padding:14px 18px;display:flex;flex-direction:column;align-items:center;gap:6px;">
                <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;opacity:.8;">Total Doctors</span>
                <span style="font-size:1.8rem;font-weight:800;line-height:1;"><?php echo $totalDocs; ?></span>
            </div>
        </div>
        <div class="record-card" style="flex:1;min-width:180px;margin:0;">
            <div class="record-card-header" style="padding:14px 18px;display:flex;flex-direction:column;align-items:center;gap:6px;">
                <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;opacity:.8;">Total Nurses</span>
                <span style="font-size:1.8rem;font-weight:800;line-height:1;"><?php echo $totalNurses; ?></span>
            </div>
        </div>
        <div class="record-card" style="flex:1;min-width:180px;margin:0;">
            <div class="record-card-header" style="padding:14px 18px;background:linear-gradient(135deg,#1daa7d,#138f69);display:flex;flex-direction:column;align-items:center;gap:6px;">
                <span style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;opacity:.8;">Grand Total</span>
                <span style="font-size:1.8rem;font-weight:800;line-height:1;"><?php echo $grandTotal; ?></span>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Staff Type</th>
                    <th>Position / Grade</th>
                    <th>Head Count</th>
                    <th>Share of Type</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($rows) > 0):
                $prevType = null;
                foreach ($rows as $r):
                    $typeTotal = ($r['StaffType'] === 'Doctor') ? $totalDocs : $totalNurses;
                    $pct       = $typeTotal > 0 ? round($r['HeadCount'] / $typeTotal * 100, 1) : 0;
                    $isNewType = ($r['StaffType'] !== $prevType);
                    $prevType  = $r['StaffType'];
                    $typeBadge = ($r['StaffType'] === 'Doctor') ? 'badge-navy' : 'badge-teal';
            ?>
                <?php if ($isNewType): ?>
                <tr>
                    <td colspan="4" style="background:#f0f4f8;font-weight:600;color:#0d2740;padding:8px 14px;font-size:.8rem;text-transform:uppercase;letter-spacing:.05em;">
                        <?php echo htmlspecialchars($r['StaffType']); ?>s &mdash; <?php echo $typeTotal; ?> total
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><span class="badge <?php echo $typeBadge; ?>"><?php echo htmlspecialchars($r['StaffType']); ?></span></td>
                    <td><?php echo htmlspecialchars($r['Position']); ?></td>
                    <td style="font-weight:600;font-size:1rem;"><?php echo htmlspecialchars($r['HeadCount']); ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;background:#e2e8f0;border-radius:4px;height:8px;max-width:140px;">
                                <div style="width:<?php echo $pct; ?>%;background:linear-gradient(90deg,#1a6ea8,#1daa7d);height:8px;border-radius:4px;"></div>
                            </div>
                            <span style="font-size:.82rem;color:#7090a8;"><?php echo $pct; ?>%</span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="no-records">No staff data found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
sqlsrv_close($conn);
include '../includes/footer.php';
?>
