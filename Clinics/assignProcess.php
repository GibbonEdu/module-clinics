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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/


use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

include '../../gibbon.php';

$gibbonYearGroupID = $_POST['gibbonYearGroupID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/'.getModuleName($_POST['address']).'/assign.php&gibbonYearGroupID='.$gibbonYearGroupID;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/assign.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    //Fetch blocks
    $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

    $criteria = $clinicsBlocksGateway->newQueryCriteria()
        ->sortBy(['clinicsBlock.sequenceNumber'])
        ->fromPOST();

    $blocks = $clinicsBlocksGateway->queryBlocksBySchoolYear($criteria, $gibbon->session->get('gibbonSchoolYearID'));

    foreach ($blocks as $block) {
        $clinics = $_POST['clinics'.$block['clinicsBlockID']] ?? '';
        $gibbonPersonIDs = $_POST['gibbonPersonIDs'.$block['clinicsBlockID']] ?? '';

        $count = 0;
        $partialFail = false;
        foreach ($gibbonPersonIDs as $gibbonPersonID) {
            $innerFail = false ;
            try {
                $dataStudent = array('clinicsBlockID' => $block['clinicsBlockID'], 'gibbonPersonID' => $gibbonPersonID);
                $sqlStudent = 'SELECT *
                    FROM clinicsClinicStudent
                    WHERE clinicsBlockID=:clinicsBlockID
                        AND gibbonPersonID=:gibbonPersonID';
                $resultStudent = $connection2->prepare($sqlStudent);
                $resultStudent->execute($dataStudent);
            } catch (PDOException $e) {
                $innerFail = $partialFail = true;
            }

            if (!$innerFail) {
                $clinicsClinicID = ($clinics[$count] != '') ? $clinics[$count] : NULL ;
                $status = (!is_null($clinicsClinicID)) ? 'Assigned' : NULL;

                if ($resultStudent->rowCount() == 1) { //Exists, so update
                    $rowStudent = $resultStudent->fetch();
                    try {
                        $data = array('clinicsClinicID' => $clinicsClinicID, 'status' => $status, 'clinicsClinicStudentID' => $rowStudent['clinicsClinicStudentID']);
                        $sql = 'UPDATE clinicsClinicStudent SET clinicsClinicID=:clinicsClinicID, status=:status WHERE clinicsClinicStudentID=:clinicsClinicStudentID';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                    }
                }
                else if ($resultStudent->rowCount() == 0) { //Does not exist, so insert
                    try {
                        $data = array('clinicsClinicID' => $clinicsClinicID, 'status' => $status, 'clinicsBlockID' => $block['clinicsBlockID'], 'gibbonPersonID' => $gibbonPersonID);
                        $sql = 'INSERT INTO  clinicsClinicStudent SET clinicsClinicID=:clinicsClinicID, status=:status, clinicsBlockID=:clinicsBlockID, gibbonPersonID=:gibbonPersonID';
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
