<?php
// ============================================================
// IVOR PAINE MEMORIAL HOSPITAL — Database Configuration
// ============================================================

define('BASE_URL', '/ivor_hospital');

function get_db_connection() {

    $serverName = "db,1433";

    $connectionOptions = array(
        "Database"               => getenv('DB_NAME') ?: 'IvorHospital',
        "Uid"                    => getenv('DB_USER') ?: 'sa',
        "PWD"                    => getenv('SA_PASSWORD'),
        "TrustServerCertificate" => true,
        "CharacterSet"           => "UTF-8"
    );

    sqlsrv_configure("WarningsReturnAsErrors", 0);

    $conn = @sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        if (!empty($errors)) {
            die("Connection failed:\n" . print_r($errors, true));
        }
    }

    return $conn;
}
?>