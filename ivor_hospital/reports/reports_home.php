<?php
require_once '../db_config.php';
require_once '../includes/auth_check.php';

$pageTitle = "Reports";
include '../includes/header.php';
?>

<div style="margin-bottom:18px;">
    <h2 style="font-size:17px;color:#0d2740;font-weight:700;margin-bottom:6px;">&#x1F4CA; Available Reports</h2>
    <p style="font-size:13px;color:#7090a8;">Select a report to view results. Reports marked with &#x1F50D; require a filter input.</p>
</div>

<div class="reports-grid">

    <div class="report-card">
        <div class="report-num">Report 1</div>
        <h3>Consultant Teams</h3>
        <p>All consultants with their team members, positions, and join dates.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_01_consultant_teams.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 2</div>
        <h3>Ward Staff</h3>
        <p>Wards with their sisters, care units, and staff nurses in charge of each care unit.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_02_ward_staff.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 3</div>
        <h3>Patient Treatments</h3>
        <p>Patients with all their complaints, assigned treatments, and treatment dates.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_03_patient_treatments.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 4</div>
        <h3>Junior Housemen &amp; Patients</h3>
        <p>Junior housemen, the patients under their care, and the staff nurse for each patient's care unit.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_04_junior_housemen.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 5</div>
        <h3>Unique Specialties</h3>
        <p>Consultants whose medical specialty is not shared by any other consultant.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_05_unique_speciality_consultants.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 6</div>
        <h3>Complaint Treatment Experience</h3>
        <p>Complaints, the treatments given for each, and the prior experience of the treating doctor.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_06_complaint_treatment_experience.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 7</div>
        <h3>Multiple Complaints</h3>
        <p>Patients who have more than one complaint, with all their treatments listed.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_07_patients_multiple_complaints.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 8</div>
        <h3>Patients by Treatment</h3>
        <p>Patients grouped by treatment within each complaint.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_08_patients_grouped_by_treatment.php">View Report &rarr;</a>
    </div>

    <div class="report-card" style="border-left-color:#1daa7d;">
        <div class="report-num" style="color:#1daa7d;">Report 9 &nbsp;&#x1F50D;</div>
        <h3>Doctor Performance History</h3>
        <p>Full performance grade history for a selected doctor.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_09_doctor_performance.php">View Report &rarr;</a>
    </div>

    <div class="report-card" style="border-left-color:#1daa7d;">
        <div class="report-num" style="color:#1daa7d;">Report 10 &nbsp;&#x1F50D;</div>
        <h3>Full Patient Details</h3>
        <p>Complete medical details for a selected patient including complaints, treatments, and dates.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_10_patient_full_details.php">View Report &rarr;</a>
    </div>

    <div class="report-card" style="border-left-color:#1daa7d;">
        <div class="report-num" style="color:#1daa7d;">Report 11 &nbsp;&#x1F50D;</div>
        <h3>Treatments Between Dates</h3>
        <p>Treatments given for a selected complaint between two dates, ordered by treatment code.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_11_treatments_between_dates.php">View Report &rarr;</a>
    </div>

    <div class="report-card">
        <div class="report-num">Report 12</div>
        <h3>Staff Positions Count</h3>
        <p>All positions held by doctors and nurses, with a count of staff in each position.</p>
        <a href="<?php echo BASE_URL; ?>/reports/report_12_staff_positions_count.php">View Report &rarr;</a>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
