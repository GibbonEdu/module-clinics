<?php
//USE ;end TO SEPARATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00
$sql[$count][0] = "1.0.00";
$sql[$count][1] = "-- First version, nothing to update";
$count++;

//v1.0.01
$sql[$count][0] = "1.0.01";
$sql[$count][1] = "";
$count++;

//v1.1.00
$sql[$count][0] = "1.1.00";
$sql[$count][1] = "
UPDATE gibbonAction SET URLList='clinics_manage.php,clinics_manage_add.php,clinics_manage_edit.php, clinics_manage_edit_enrolment_add.php,clinics_manage_delete.php,clinics_manage_enrolment.php,clinics_manage_enrolment_add.php,clinics_manage_enrolment_edit.php,clinics_manage_enrolment_delete.php' WHERE name='Manage Clinics' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Clinics');end
";
$count++;
