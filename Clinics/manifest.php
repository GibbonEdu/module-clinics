<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Basic variables
$name = "Clinics";
$description = "Run academic clinics, with some students assigned to clinics based on departmental needs, and others signing up themselves.";
$entryURL = "clinics.php";
$type = "Additional";
$category = "Learn";
$version = "1.0.00";
$author = "Ross Parker";
$url = "http://rossparker.org";

// Module tables
$moduleTables[] = "CREATE TABLE `clinicsClinic` (
    `clinicsClinicID` int(7) unsigned zerofill NOT NULL AUTO_INCREMENT,
    `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,
    `clinicsBlockID` int(5) unsigned zerofill NOT NULL,
    `name` varchar(20) NOT NULL,
    `description` text NOT NULL,
    `gibbonDepartmentID` int(4) unsigned zerofill NULL DEFAULT NULL,
    `gibbonYearGroupIDList` varchar(255),
    `active` enum('Y','N') DEFAULT 'Y',
    `maxParticipants` int(3) NOT NULL,
    `gibbonSpaceID` int(10) unsigned zerofill NULL DEFAULT NULL,
  PRIMARY KEY (`clinicsClinicID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `clinicsBlock` (
  `clinicsBlockID` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,
  `sequenceNumber` int(5) NOT NULL,
  `name` varchar(20) NOT NULL,
  `firstDay` date NOT NULL,
  `lastDay` date NOT NULL,
  PRIMARY KEY (`clinicsBlockID`),
  UNIQUE KEY `sequenceNumber` (`sequenceNumber`,`gibbonSchoolYearID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$moduleTables[] = "CREATE TABLE `clinicsPriority` (
  `clinicsPriorityID` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,
  `gibbonDepartmentID` int(4) unsigned zerofill NOT NULL,
  `gibbonPersonID` int(10) unsigned zerofill NOT NULL,
  `priority` enum('1','2','3') NULL DEFAULT NULL,
  PRIMARY KEY (`clinicsPriorityID`),
  UNIQUE KEY `gibbonPersonID` (`gibbonPersonID`,`gibbonSchoolYearID`,`gibbonDepartmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Action rows
$actionRows[] = [
    'name'                      => 'Manage Clinics',
    'precedence'                => '0',
    'category'                  => 'Admin',
    'description'               => 'Manage the clinics that are available in a school year.',
    'URLList'                   => 'clinics_manage.php,clinics_manage_add.php,clinics_manage_edit.php,clinics_manage_delete.php',
    'entryURL'                  => 'clinics_manage.php',
    'entrySidebar'              => 'Y',
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N',
];

$actionRows[] = [
    'name'                      => 'Manage Blocks',
    'precedence'                => '0',
    'category'                  => 'Admin',
    'description'               => 'Manage the blocks that are used to structure clinics.',
    'URLList'                   => 'clinicsBlocks_manage.php,clinicsBlocks_manage_add.php,clinicsBlocks_manage_edit.php,clinicsBlocks_manage_delete.php',
    'entryURL'                  => 'clinicsBlocks_manage.php',
    'entrySidebar'              => 'Y',
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N',
];

$actionRows[] = [
    'name'                      => 'Identify Priorities_all',
    'precedence'                => '1',
    'category'                  => 'Students',
    'description'               => 'Identify, across departments, which students would benefit from support.',
    'URLList'                   => 'identify.php',
    'entryURL'                  => 'identify.php',
    'entrySidebar'              => 'N',
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N',
];

$actionRows[] = [
    'name'                      => 'Identify Priorities_department',
    'precedence'                => '0',
    'category'                  => 'Students',
    'description'               => 'Identify, in departments which the user is a Coordinator, which students would benefit from support.',
    'URLList'                   => 'identify.php',
    'entryURL'                  => 'identify.php',
    'entrySidebar'              => 'N',
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'N',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N',
    'defaultPermissionParent'   => 'N',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N',
];
