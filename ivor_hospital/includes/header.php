<?php
// $pageTitle must be set by the including page before this include
if (!isset($pageTitle)) { $pageTitle = "IVOR Hospital"; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> &mdash; IVOR Paine Memorial Hospital</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<div class="wrapper">

    <!-- ======= SIDEBAR ======= -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <div class="logo-icon">&#x2695;</div>
            <div>
                <h2>IVOR PAINE</h2>
                <p>Memorial Hospital</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>

                <!-- Dashboard -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/dashboard.php" class="nav-link">
                        <span class="icon">&#x1F3E0;</span>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- View Tables -->
                <li class="has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span class="icon">&#x1F4CB;</span>
                        <span>View Tables</span>
                        <span class="arrow">&#x25BA;</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?php echo BASE_URL; ?>/views/wards.php">Wards</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/beds.php">Beds</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/care_units.php">Care Units</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/patients.php">Patients</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/doctors.php">Doctors</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/consultants.php">Consultants</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/nurses.php">Nurses</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/complaints.php">Complaints</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/treatments.php">Treatments</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/medical_history.php">Medical History</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/previous_experience.php">Prev. Experience</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/views/performance_grades.php">Perf. Grades</a></li>
                    </ul>
                </li>

                <!-- Forms -->
                <li class="has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span class="icon">&#x1F4DD;</span>
                        <span>Forms</span>
                        <span class="arrow">&#x25BA;</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?php echo BASE_URL; ?>/forms/admit_patient.php">Admit Patient</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/forms/add_patient_complaint.php">Add Complaint</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/forms/assign_treatment.php">Assign Treatment</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/forms/add_doctor_experience.php">Doctor Experience</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/forms/add_performance_grade.php">Performance Grade</a></li>
                    </ul>
                </li>

                <!-- Manual Records -->
                <li class="has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span class="icon">&#x1F4C4;</span>
                        <span>Manual Records</span>
                        <span class="arrow">&#x25BA;</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?php echo BASE_URL; ?>/manual_records/patient_record.php">Patient Record</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/manual_records/ward_record.php">Ward Record</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/manual_records/consultant_team_record.php">Consultant Team</a></li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="has-submenu">
                    <a href="#" class="nav-link submenu-toggle">
                        <span class="icon">&#x1F4CA;</span>
                        <span>Reports</span>
                        <span class="arrow">&#x25BA;</span>
                    </a>
                    <ul class="submenu">
                        <li><a href="<?php echo BASE_URL; ?>/reports/reports_home.php">All Reports</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_01_consultant_teams.php">1. Consultant Teams</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_02_ward_staff.php">2. Ward Staff</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_03_patient_treatments.php">3. Patient Treatments</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_04_junior_housemen.php">4. Junior Housemen</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_05_unique_speciality_consultants.php">5. Unique Specialties</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_06_complaint_treatment_experience.php">6. Complaint Exp.</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_07_patients_multiple_complaints.php">7. Multi-Complaints</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_08_patients_grouped_by_treatment.php">8. By Treatment</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_09_doctor_performance.php">9. Doctor Perf.</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_10_patient_full_details.php">10. Patient Details</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_11_treatments_between_dates.php">11. Treatments by Date</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/reports/report_12_staff_positions_count.php">12. Staff Positions</a></li>
                    </ul>
                </li>

                <!-- Logout -->
                <li>
                    <a href="<?php echo BASE_URL; ?>/logout.php" class="nav-link nav-logout">
                        <span class="icon">&#x1F6AA;</span>
                        <span>Logout</span>
                    </a>
                </li>

            </ul>
        </nav>
    </aside>
    <!-- ======= END SIDEBAR ======= -->

    <!-- ======= MAIN CONTENT ======= -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Menu">&#9776;</button>
            <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
            <div class="topbar-user">
                <span>&#x1F464;&nbsp;<?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
