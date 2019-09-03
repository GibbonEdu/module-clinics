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

$gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
$clinicsClinicID = $_POST['clinicsClinicID'] ?? '';
$clinicsClinicStudentID = $_POST['clinicsClinicStudentID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Clinics/clinics_manage_enrolment_edit.php&gibbonSchoolYearID='.$gibbonSchoolYearID.'&clinicsClinicID='.$clinicsClinicID.'&clinicsClinicStudentID='.$clinicsClinicStudentID;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_enrolment_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {
    // Proceed!
    $clinicsGateway = $container->get(ClinicsGateway::class);
    $clinicsStudentsGateway = $container->get(ClinicsStudentsGateway::class);

    $data = [
        'status' => $_POST['status'] ?? '',
    ];

    // Validate the required values are present
    if (empty($clinicsClinicID) || empty($clinicsClinicStudentID) || empty($data['status'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if (!$clinicsGateway->exists($clinicsClinicID) || !$clinicsStudentsGateway->exists($clinicsClinicStudentID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    //Get clinicBlockID from clinic
    $data['clinicsBlockID'] = $clinicsGateway->getByID($clinicsClinicID)['clinicsBlockID'];

    // Update the record
    $updated = $clinicsStudentsGateway->update($clinicsClinicStudentID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
