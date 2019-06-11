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

use Gibbon\Module\Clinics\Domain\ClinicsGateway;
use Gibbon\Module\Clinics\Domain\ClinicsStudentsGateway;

require_once '../../gibbon.php';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Clinics/enrol.php';

if (isActionAccessible($guid, $connection2, '/modules/Clinics/enrol_add.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    $enrolmentActive = getSettingByScope($connection2, 'Clinics', 'enrolmentActive');
    if ($enrolmentActive != "Y") {
        $URL .= '&return=error0';
        header("Location: {$URL}");
        exit;
    }
    else {
        // Proceed!
        $clinicsGateway = $container->get(ClinicsGateway::class);
        $clinicsStudentsGateway = $container->get(ClinicsStudentsGateway::class);

        $data = [
            'clinicsBlockID'        => $_POST['clinicsBlockID'] ?? '',
            'gibbonPersonID'        => $gibbon->session->get('gibbonPersonID') ?? '',
            'clinicsClinicID'       => $_POST['clinicsClinicID'] ?? '',
            'status'                => 'Enroled'
        ];

        // Validate the required values are present
        if (empty($data['clinicsBlockID']) || empty($data['gibbonPersonID']) || empty($data['clinicsClinicID'])) {
            $URL .= '&return=error1';
            header("Location: {$URL}");
            exit;
        }

        //Lock Tables
        try {
            $sql = 'LOCK TABLES clinicsClinic WRITE, clinicsBlock WRITE, clinicsClinicStudent WRITE';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit();
        }

        //Check clinic exists, get maxParticipants
        $values = $clinicsGateway->getByID($data['clinicsClinicID']);
        if (!$values) {
            $URL .= '&return=error2';
            header("Location: {$URL}");
            exit;
        }
        else {
            $maxParticipants = $values['maxParticipants'];

            //Get all enrolments for this activity
            $criteria = $clinicsStudentsGateway->newQueryCriteria();

            $enrolments = $clinicsStudentsGateway->queryStudentEnrolmentByClinic($criteria, $data['clinicsClinicID']);

            //Check enrolment number against maxParticipants
            if ($enrolments->getResultCount() >= $maxParticipants) {
                $URL .= '&return=error3';
                header("Location: {$URL}");
                exit;
            }
            else {
                $enroled = false;
                //Check for existing enrolment in this clinic
                foreach ($enrolments AS $enrolment) {
                    if ($enrolment['gibbonPersonID'] == $gibbon->session->get('gibbonPersonID')) {
                        $enroled = true;
                    }
                }

                if ($enroled) {
                    $URL .= '&return=error2';
                    header("Location: {$URL}");
                    exit;
                }
                else {
                    //Check for existing record in this block (e.g. assigned clinic enrolment)
                    try {
                        $dataStudent = array('clinicsBlockID' => $data['clinicsBlockID'], 'gibbonPersonID' => $data['gibbonPersonID']);
                        $sqlStudent = 'SELECT *
                            FROM clinicsClinicStudent
                            WHERE clinicsBlockID=:clinicsBlockID
                                AND gibbonPersonID=:gibbonPersonID';
                        $resultStudent = $connection2->prepare($sqlStudent);
                        $resultStudent->execute($dataStudent);
                    } catch (PDOException $e) {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit;
                    }

                    if ($resultStudent->rowCount() == 0) { //If no record, create one
                        $clinicsClinicStudentID = $clinicsStudentsGateway->insert($data);
                    }
                    else if ($resultStudent->rowCount() == 1) {
                        $rowStudents = $resultStudent->fetch();
                        if ($rowStudents['status'] != NULL ) { //If record exists and clinicsClinicID not null, return error3
                            $URL .= '&return=error3';
                            header("Location: {$URL}");
                            exit;
                        }
                        else { //Else update record
                            $clinicsClinicStudentID = $clinicsStudentsGateway->update($rowStudents['clinicsClinicStudentID'], $data);
                        }
                    }
                    else {
                        $URL .= '&return=error2';
                        header("Location: {$URL}");
                        exit;
                    }


                    //Unlock locked database tables
                    try {
                        $sql = 'UNLOCK TABLES';
                        $result = $connection2->query($sql);
                    } catch (PDOException $e) {}

                    $URL .= !$clinicsClinicStudentID
                        ? "&return=error2"
                        : "&return=success0";

                    header("Location: {$URL}");
                }
            }
        }
    }
}
