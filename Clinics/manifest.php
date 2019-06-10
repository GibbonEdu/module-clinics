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
$moduleTables[] = "CREATE TABLE `clinicsBlock` (
  `clinicsBlockID` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `gibbonSchoolYearID` int(3) unsigned zerofill NOT NULL,
  `sequenceNumber` int(5) NOT NULL,
  `name` varchar(20) NOT NULL,
  `nameShort` varchar(4) NOT NULL,
  `firstDay` date NOT NULL,
  `lastDay` date NOT NULL,
  PRIMARY KEY (`clinicsBlockID`),
  UNIQUE KEY `sequenceNumber` (`sequenceNumber`,`gibbonSchoolYearID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";



// Action rows
$actionRows[] = [
    'name'                      => 'Manage Blocks',
    'precedence'                => '0',
    'category'                  => 'Admin',
    'description'               => '',
    'URLList'                   => 'clinicsBlock_manage.php,clinicsBlock_manage_add.php,clinicsBlock_manage_edit.php,clinicsBlock_manage_delete.php',
    'entryURL'                  => 'clinicsBlock_manage.php',
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
