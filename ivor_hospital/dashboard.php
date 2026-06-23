<?php
require_once 'db_config.php';
require_once 'includes/auth_check.php';

$pageTitle = "Dashboard";
$conn = get_db_connection();

function get_count($conn, $sql)
{
    $result = sqlsrv_query($conn, $sql);
    if ($result) {
        $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
        sqlsrv_free_stmt($result);
        return ($row && $row[0] !== null) ? (int) $row[0] : 0;
    }
    return 0;
}

$totalPatients = get_count($conn, "SELECT COUNT(*) FROM dbo.PATIENT");
$totalDoctors = get_count($conn, "SELECT COUNT(*) FROM dbo.DOCTOR");
$totalNurses = get_count($conn, "SELECT COUNT(*) FROM dbo.NURSE");
$totalWards = get_count($conn, "SELECT COUNT(*) FROM dbo.WARD");
$totalCareUnits = get_count($conn, "SELECT COUNT(*) FROM dbo.CARE_UNIT");
$activeTreatments = get_count($conn, "SELECT COUNT(*) FROM dbo.MEDICAL_HISTORY WHERE DateEnded IS NULL");
$totalBeds = get_count($conn, "SELECT COUNT(*) FROM dbo.BED");
$completedTreatments = get_count($conn, "SELECT COUNT(*) FROM dbo.MEDICAL_HISTORY WHERE DateEnded IS NOT NULL");
$totalConsultants = get_count($conn, "SELECT COUNT(*) FROM dbo.CONSULTANT");
$juniorDocs = get_count($conn, "SELECT COUNT(*) FROM dbo.DOCTOR WHERE Position = 'Junior Houseman'");

$occupiedBeds = $totalPatients;
$availableBeds = max(0, $totalBeds - $occupiedBeds);
$occupancyPct = $totalBeds > 0 ? round($occupiedBeds / $totalBeds * 100) : 0;
$totalTreatments = $activeTreatments + $completedTreatments;
$activePct = $totalTreatments > 0 ? round($activeTreatments / $totalTreatments * 100) : 0;
$donePct = $totalTreatments > 0 ? round($completedTreatments / $totalTreatments * 100) : 0;

include 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     HERO BANNER
     ═══════════════════════════════════════════════════════════ -->
<div class="dash-hero">
    <div class="dash-hero-inner">

        <div class="dash-hero-left">
            <div class="dash-live-badge">
                <span class="dash-live-dot"></span> Live Dashboard
            </div>
            <h1 class="dash-hero-title">IVOR Paine Memorial Hospital</h1>
            <p class="dash-hero-sub">
                &#x1F464;&nbsp;<?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
                &emsp;&#x1F4C5;&nbsp;<?php echo date('l, F j, Y'); ?>
                &emsp;&#x1F550;&nbsp;<span id="liveClock">--:--:--</span>
            </p>
        </div>

        <div class="dash-hero-kpis">
            <div class="dash-kpi">
                <div class="dash-kpi-num"><?php echo $totalPatients; ?></div>
                <div class="dash-kpi-lbl">Patients</div>
            </div>
            <div class="dash-kpi-sep"></div>
            <div class="dash-kpi">
                <div class="dash-kpi-num"><?php echo $totalDoctors + $totalNurses; ?></div>
                <div class="dash-kpi-lbl">Total Staff</div>
            </div>
            <div class="dash-kpi-sep"></div>
            <div class="dash-kpi">
                <div class="dash-kpi-num dash-kpi-highlight"><?php echo $activeTreatments; ?></div>
                <div class="dash-kpi-lbl">Active Treatments</div>
            </div>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     STAT CARDS
     ═══════════════════════════════════════════════════════════ -->
<div class="dashboard-cards">

    <div class="card card-blue">
        <div class="card-icon">&#x1F6CF;</div>
        <div class="card-info">
            <h3><?php echo $totalPatients; ?></h3>
            <p>Total Patients</p>
        </div>
    </div>

    <div class="card card-green">
        <div class="card-icon">&#x1F3E5;</div>
        <div class="card-info">
            <h3><?php echo $totalDoctors; ?></h3>
            <p>Total Doctors</p>
        </div>
    </div>

    <div class="card card-teal">
        <div class="card-icon">&#x1F469;&#x200D;&#x2695;&#xFE0F;</div>
        <div class="card-info">
            <h3><?php echo $totalNurses; ?></h3>
            <p>Total Nurses</p>
        </div>
    </div>

    <div class="card card-navy">
        <div class="card-icon">&#x1F3D7;</div>
        <div class="card-info">
            <h3><?php echo $totalWards; ?></h3>
            <p>Total Wards</p>
        </div>
    </div>

    <div class="card card-purple">
        <div class="card-icon">&#x1F4CC;</div>
        <div class="card-info">
            <h3><?php echo $totalCareUnits; ?></h3>
            <p>Care Units</p>
        </div>
    </div>

    <div class="card card-orange">
        <div class="card-icon">&#x1F489;</div>
        <div class="card-info">
            <h3><?php echo $activeTreatments; ?></h3>
            <p>Active Treatments</p>
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     MIDDLE ROW: Occupancy · Treatment Status · Staff Overview
     ═══════════════════════════════════════════════════════════ -->
<div class="dash-mid-grid">

    <!-- Bed Occupancy -->
    <div class="dash-panel">
        <div class="dash-panel-hd">
            <span class="dash-panel-icon">&#x1F6CF;</span>
            <span>Bed Occupancy</span>
        </div>
        <div class="dash-donut-wrap">
            <div class="dash-donut" style="--occ:<?php echo $occupancyPct; ?>%;">
                <div class="dash-donut-hole">
                    <span class="dash-donut-pct"><?php echo $occupancyPct; ?>%</span>
                    <span class="dash-donut-sublbl">in use</span>
                </div>
            </div>
        </div>
        <div class="dash-legend">
            <div class="dash-legend-row">
                <span class="dash-legend-dot" style="background:#1a6ea8;"></span>
                <span><?php echo $occupiedBeds; ?> occupied</span>
            </div>
            <div class="dash-legend-row">
                <span class="dash-legend-dot" style="background:#c8ddf0;"></span>
                <span><?php echo $availableBeds; ?> available</span>
            </div>
            <div class="dash-legend-row" style="margin-top:6px;font-weight:700;color:#0d2740;">
                <?php echo $totalBeds; ?> beds total
            </div>
        </div>
    </div>

    <!-- Treatment Status -->
    <div class="dash-panel">
        <div class="dash-panel-hd">
            <span class="dash-panel-icon">&#x1F489;</span>
            <span>Treatment Status</span>
        </div>

        <div class="dash-bar-group">
            <div class="dash-bar-row">
                <span class="dash-bar-label">Active</span>
                <div class="dash-bar-track">
                    <div class="dash-bar-fill"
                        style="width:<?php echo $activePct; ?>%;background:linear-gradient(90deg,#1daa7d,#50d4a8);">
                    </div>
                </div>
                <span class="dash-bar-count badge-green-sm"><?php echo $activeTreatments; ?></span>
            </div>
            <div class="dash-bar-row">
                <span class="dash-bar-label">Completed</span>
                <div class="dash-bar-track">
                    <div class="dash-bar-fill"
                        style="width:<?php echo $donePct; ?>%;background:linear-gradient(90deg,#7090a8,#a0b8cc);"></div>
                </div>
                <span class="dash-bar-count badge-gray-sm"><?php echo $completedTreatments; ?></span>
            </div>
        </div>

        <div class="dash-total-note">
            <?php echo $totalTreatments; ?> total treatment records
        </div>

        <!-- Mini active indicator -->
        <?php if ($activeTreatments > 0): ?>
            <div class="dash-active-indicator">
                <span class="dash-pulse-dot"></span>
                <?php echo $activeTreatments; ?> treatment<?php echo $activeTreatments !== 1 ? 's' : ''; ?> ongoing
            </div>
        <?php endif; ?>
    </div>

    <!-- Staff Overview -->
    <div class="dash-panel">
        <div class="dash-panel-hd">
            <span class="dash-panel-icon">&#x1F465;</span>
            <span>Staff Overview</span>
        </div>
        <div class="dash-staff-list">
            <div class="dash-staff-row">
                <span class="dash-staff-label">Consultants</span>
                <span class="dash-staff-bar-wrap">
                    <span class="dash-staff-bar"
                        style="width:<?php echo ($totalDoctors > 0 ? round($totalConsultants / $totalDoctors * 100) : 0); ?>%;background:#6a3a9e;"></span>
                </span>
                <span class="dash-staff-num"><?php echo $totalConsultants; ?></span>
            </div>
            <div class="dash-staff-row">
                <span class="dash-staff-label">Junior Housemen</span>
                <span class="dash-staff-bar-wrap">
                    <span class="dash-staff-bar"
                        style="width:<?php echo ($totalDoctors > 0 ? round($juniorDocs / $totalDoctors * 100) : 0); ?>%;background:#1a6ea8;"></span>
                </span>
                <span class="dash-staff-num"><?php echo $juniorDocs; ?></span>
            </div>
            <div class="dash-staff-row">
                <span class="dash-staff-label">Other Doctors</span>
                <span class="dash-staff-bar-wrap">
                    <span class="dash-staff-bar" style="width:<?php
                    $other = max(0, $totalDoctors - $totalConsultants - $juniorDocs);
                    echo ($totalDoctors > 0 ? round($other / $totalDoctors * 100) : 0);
                    ?>%;background:#1daa7d;"></span>
                </span>
                <span
                    class="dash-staff-num"><?php echo max(0, $totalDoctors - $totalConsultants - $juniorDocs); ?></span>
            </div>
            <div class="dash-staff-row">
                <span class="dash-staff-label">Nurses</span>
                <span class="dash-staff-bar-wrap">
                    <span class="dash-staff-bar" style="width:100%;background:#d4750a;"></span>
                </span>
                <span class="dash-staff-num"><?php echo $totalNurses; ?></span>
            </div>
        </div>
        <div class="dash-total-note" style="margin-top:14px;">
            <?php echo $totalDoctors + $totalNurses; ?> staff members total
        </div>
    </div>

</div>

<!-- ═══════════════════════════════════════════════════════════
     QUICK ACTIONS
     ═══════════════════════════════════════════════════════════ -->
<div class="dash-actions-wrap">

    <div class="dash-section-hd">
        <span>&#x26A1;</span> Quick Actions
    </div>

    <div class="dash-action-grid">

        <a href="<?php echo BASE_URL; ?>/forms/admit_patient.php" class="dac dac-blue">
            <div class="dac-icon">&#x2795;</div>
            <div class="dac-body">
                <div class="dac-title">Admit Patient</div>
                <div class="dac-sub">Register new admission</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/forms/add_patient_complaint.php" class="dac dac-blue">
            <div class="dac-icon">&#x1F4CB;</div>
            <div class="dac-body">
                <div class="dac-title">Add Complaint</div>
                <div class="dac-sub">Assign diagnosis to patient</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/forms/assign_treatment.php" class="dac dac-blue">
            <div class="dac-icon">&#x1F489;</div>
            <div class="dac-body">
                <div class="dac-title">Assign Treatment</div>
                <div class="dac-sub">Link treatment to complaint</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/manual_records/patient_record.php" class="dac dac-teal">
            <div class="dac-icon">&#x1F4C4;</div>
            <div class="dac-body">
                <div class="dac-title">Patient Record</div>
                <div class="dac-sub">View full patient file</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/manual_records/ward_record.php" class="dac dac-teal">
            <div class="dac-icon">&#x1F3D7;</div>
            <div class="dac-body">
                <div class="dac-title">Ward Record</div>
                <div class="dac-sub">Ward staff &amp; patient list</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/manual_records/consultant_team_record.php" class="dac dac-teal">
            <div class="dac-icon">&#x1F468;&#x200D;&#x2695;</div>
            <div class="dac-body">
                <div class="dac-title">Consultant Team</div>
                <div class="dac-sub">Team &amp; grade history</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/forms/add_doctor_experience.php" class="dac dac-green">
            <div class="dac-icon">&#x1F4BC;</div>
            <div class="dac-body">
                <div class="dac-title">Doctor Experience</div>
                <div class="dac-sub">Add prior employment</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/forms/add_performance_grade.php" class="dac dac-green">
            <div class="dac-icon">&#x2B50;</div>
            <div class="dac-body">
                <div class="dac-title">Performance Grade</div>
                <div class="dac-sub">Issue grading to doctor</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/reports/reports_home.php" class="dac dac-purple">
            <div class="dac-icon">&#x1F4CA;</div>
            <div class="dac-body">
                <div class="dac-title">All Reports</div>
                <div class="dac-sub">Browse all 12 reports</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/reports/report_10_patient_full_details.php" class="dac dac-purple">
            <div class="dac-icon">&#x1F50D;</div>
            <div class="dac-body">
                <div class="dac-title">Patient Details</div>
                <div class="dac-sub">Full patient report</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/views/patients.php" class="dac dac-orange">
            <div class="dac-icon">&#x1F465;</div>
            <div class="dac-body">
                <div class="dac-title">View Patients</div>
                <div class="dac-sub">Browse all patients</div>
            </div>
        </a>

        <a href="<?php echo BASE_URL; ?>/views/doctors.php" class="dac dac-orange">
            <div class="dac-icon">&#x1F3E5;</div>
            <div class="dac-body">
                <div class="dac-title">View Doctors</div>
                <div class="dac-sub">Browse medical staff</div>
            </div>
        </a>

    </div>
</div>

<script>
    (function () {
        function tick() {
            var n = new Date();
            var pad = function (x) { return String(x).padStart(2, '0'); };
            var el = document.getElementById('liveClock');
            if (el) el.textContent = pad(n.getHours()) + ':' + pad(n.getMinutes()) + ':' + pad(n.getSeconds());
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>

<?php
sqlsrv_close($conn);
include 'includes/footer.php';
?>