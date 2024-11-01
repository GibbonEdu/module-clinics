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

//v1.1.01
$sql[$count][0] = "1.1.01";
$sql[$count][1] = "";
$count++;

//v1.1.02
$sql[$count][0] = "1.1.02";
$sql[$count][1] = "";
$count++;

//v1.1.03
$sql[$count][0] = "1.1.03";
$sql[$count][1] = "";
$count++;

//v1.2.00
$sql[$count][0] = "1.2.00";
$sql[$count][1] = "";
$count++;

//v1.2.01
$sql[$count][0] = "1.2.01";
$sql[$count][1] = "";
$count++;

//v1.2.02
$sql[$count][0] = "1.2.02";
$sql[$count][1] = "";
$count++;

//v1.2.03
$sql[$count][0] = "1.2.03";
$sql[$count][1] = "";
$count++;


//v1.3.00
$sql[$count][0] = "1.3.00";
$sql[$count][1] = "
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Clinics'), 'Enrolment by Student', 0, 'Clinics', 'Manage clinic enrolment for individual students.', 'enrolmentByStudent.php,enrolmentByStudent_student.php,enrolmentByStudent_student_add.php,enrolmentByStudent_student_delete.php', 'enrolmentByStudent.php', 'Y', 'Y', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
INSERT INTO `gibbonPermission` (`gibbonRoleID` ,`gibbonActionID`) VALUES ('001', (SELECT gibbonActionID FROM gibbonAction JOIN gibbonModule ON (gibbonAction.gibbonModuleID=gibbonModule.gibbonModuleID) WHERE gibbonModule.name='Clinics' AND gibbonAction.name='Enrolment by Student'));end
";
$count++;


//v1.4.00
$sql[$count][0] = "1.4.00";
$sql[$count][1] = "
UPDATE gibbonAction SET precedence=2 WHERE name='Identify Priorities_all' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Clinics');end
UPDATE gibbonAction SET precedence=1 WHERE name='Identify Priorities_department' AND gibbonModuleID=(SELECT gibbonModuleID FROM gibbonModule WHERE name='Clinics');end
INSERT INTO `gibbonAction` (`gibbonModuleID`, `name`, `precedence`, `category`, `description`, `URLList`, `entryURL`, `defaultPermissionAdmin`, `defaultPermissionTeacher`, `defaultPermissionStudent`, `defaultPermissionParent`, `defaultPermissionSupport`, `categoryPermissionStaff`, `categoryPermissionStudent`, `categoryPermissionParent`, `categoryPermissionOther`) VALUES ((SELECT gibbonModuleID FROM gibbonModule WHERE name='Clinics'), 'Identify Priorities_viewOnly', 0, 'Individual Needs', 'View identified priorities across all departments.', 'identify.php', 'identify.php', 'N', 'N', 'N', 'N', 'N', 'Y', 'N', 'N', 'N');end
";
$count++;

//v1.4.01
$sql[$count][0] = "1.4.01";
$sql[$count][1] = "";
$count++;

//v1.4.02
$sql[$count][0] = "1.4.02";
$sql[$count][1] = "";
$count++;

//v1.5.00
$sql[$count][0] = "1.5.00";
$sql[$count][1] = "
ALTER TABLE `clinicsClinic` ADD `lockEnrolment` ENUM('N','Y') NOT NULL DEFAULT 'N' AFTER `active`;end
";
$count++;

//v1.6.00
$sql[$count][0] = "1.6.00";
$sql[$count][1] = "";
$count++;

//v1.6.01
$sql[$count][0] = "1.6.01";
$sql[$count][1] = "";
$count++;

//v1.7.00
$sql[$count][0] = "1.7.00";
$sql[$count][1] = "";
$count++;

//v1.7.01
$sql[$count][0] = "1.7.01";
$sql[$count][1] = "ALTER TABLE `clinicsClinicStudent` ADD UNIQUE KEY `student` (`clinicsBlockID`,`gibbonPersonID`);end";
$count++;

//v1.7.02
$sql[$count][0] = "1.7.02";
$sql[$count][1] = "";
$count++;

//v1.7.03
$sql[$count][0] = "1.7.03";
$sql[$count][1] = "";
$count++;

//v1.8.00
$sql[$count][0] = "1.8.00";
$sql[$count][1] = "";
$count++;

//v1.8.02
$sql[$count][0] = "1.8.02";
$sql[$count][1] = "";
$count++;

//v1.9.00
$sql[$count][0] = "1.9.00";
$sql[$count][1] = "";
$count++;

//v1.9.01
$sql[$count][0] = "1.9.01";
$sql[$count][1] = "";
$count++;

//v1.10.00
++$count;
$sql[$count][0] = '1.10.00';
$sql[$count][1] = "
UPDATE gibbonModule SET author='Gibbon Foundation', url='https://gibbonedu.org' WHERE name='Clinics';end
";

//v1.10.01
++$count;
$sql[$count][0] = '1.10.01';
$sql[$count][1] = "";

//v1.10.02
++$count;
$sql[$count][0] = '1.10.02';
$sql[$count][1] = "";