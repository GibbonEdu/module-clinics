<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include '../../gibbon.php';

$gibbonYearGroupID = $_POST['gibbonYearGroupID'] ?? '';
$gibbonDepartmentID = $_POST['gibbonDepartmentID'] ?? '';

$URL = $session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/identify.php&gibbonYearGroupID='.$gibbonYearGroupID.'&gibbonDepartmentID='.$gibbonDepartmentID;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/identify.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    $highestAction = getHighestGroupedAction($guid, $_POST['address'], $connection2);
    if ($highestAction == false) {
        $URL .= "&return=error0$params";
        header("Location: {$URL}");
    }
    else {
        //Check departmental access
        $departmentalAccess = true;
        if ($highestAction != "Identify Priorities_all") {
            try {
                $dataDepartments = array('gibbonPersonID' => $session->get('gibbonPersonID'), 'gibbonDepartmentID' => $gibbonDepartmentID);
                $sqlDepartments = "SELECT gibbonDepartment.gibbonDepartmentID as value, name, nameShort
                        FROM gibbonDepartment
                            JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID)
                            JOIN gibbonPerson ON (gibbonDepartmentStaff.gibbonPersonID=gibbonPerson.gibbonPersonID)
                        WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                            AND gibbonDepartmentStaff.role='Coordinator'
                            AND gibbonDepartment.gibbonDepartmentID=:gibbonDepartmentID
                        ORDER BY name";
                $resultDepartments = $connection2->prepare($sqlDepartments);
                $resultDepartments->execute($dataDepartments);
            } catch (PDOException $e) {
                $departmentalAccess = false;
            }
            if ($resultDepartments->rowCount() != 1) {
                $departmentalAccess = false;
            }
        }

        if (!$departmentalAccess) {
            $URL .= "&return=error0$params";
            header("Location: {$URL}");
        }
        else {

            $priorities = $_POST['priorities'] ?? '';
            $gibbonPersonIDs = $_POST['gibbonPersonIDs'] ?? '';

            if (empty($priorities) || empty($gibbonPersonIDs)) {
                $URL .= '&return=error1';
                header("Location: {$URL}");
                exit;
            } else {
                //Scan through priorities checking for records
                $count = 0;
                $partialFail = false;
                foreach ($priorities as $priority) {
                    $innerFail = false ;
                    try {
                        $dataPriority = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'), 'gibbonDepartmentID' => $gibbonDepartmentID, 'gibbonPersonID' => $gibbonPersonIDs[$count]);
                        $sqlPriority = 'SELECT *
                            FROM clinicsPriority
                            WHERE gibbonSchoolYearID=:gibbonSchoolYearID
                                AND gibbonDepartmentID=:gibbonDepartmentID
                                AND gibbonPersonID=:gibbonPersonID';
                        $resultPriority = $connection2->prepare($sqlPriority);
                        $resultPriority->execute($dataPriority);
                    } catch (PDOException $e) {
                        $innerFail = $partialFail = true;
                    }

                    if (!$innerFail) {
                        if ($resultPriority->rowCount() == 1) { //Exists, so update
                            $rowPriority = $resultPriority->fetch();
                            try {
                                $data = array('priority' => $priority, 'clinicsPriorityID' => $rowPriority['clinicsPriorityID']);
                                $sql = 'UPDATE clinicsPriority SET priority=:priority WHERE clinicsPriorityID=:clinicsPriorityID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                        else if ($resultPriority->rowCount() == 0) { //Does not exist, so insert
                            try {
                                $data = array('priority' => $priority, 'gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'), 'gibbonDepartmentID' => $gibbonDepartmentID, 'gibbonPersonID' => $gibbonPersonIDs[$count]);
                                $sql = 'INSERT INTO  clinicsPriority SET priority=:priority, gibbonSchoolYearID=:gibbonSchoolYearID, gibbonDepartmentID=:gibbonDepartmentID, gibbonPersonID=:gibbonPersonID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                        else {
                            $partialFail = true;
                        }
                    }
                    $count++;
                }

                if ($partialFail == true) {
                    $URL .= '&return=warning1';
                    header("Location: {$URL}");
                    exit;
                } else {
                    //Success0
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                    exit;
                }
            }
        }
    }
}
