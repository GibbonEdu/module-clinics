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

use Gibbon\Services\Format;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

require_once '../../gibbon.php';

$gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
$clinicsBlockID = $_GET['clinicsBlockID'] ?? '';
$clinicsClinicID = $_POST['clinicsClinicID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Clinics/clinics_manage_edit.php&gibbonSchoolYearID='.$gibbonSchoolYearID.'&clinicsClinicID='.$clinicsClinicID.'&clinicsBlockID='.$clinicsBlockID;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {

    // Proceed!
    $clinicsGateway = $container->get(ClinicsGateway::class);

    $data = [
        'gibbonSchoolYearID'    => $_POST['gibbonSchoolYearID'] ?? '',
        'clinicsBlockID'        => $_POST['clinicsBlockID'] ?? '',
        'name'                  => $_POST['name'] ?? '',
        'description'           => $_POST['description'] ?? '',
        'gibbonDepartmentID'    => $_POST['gibbonDepartmentID'] ?? '',
        'gibbonYearGroupIDList' => !empty($_POST['gibbonYearGroupIDList']) ? implode(",", $_POST['gibbonYearGroupIDList']) : '',
        'active'                => $_POST['active'] ?? '',
        'lockEnrolment'         => $_POST['lockEnrolment'] ?? '',
        'maxParticipants'       => $_POST['maxParticipants'] ?? '',
        'gibbonSpaceID'         => $_POST['gibbonSpaceID'] ?? '',
    ];

    // Validate the required values are present
    if (empty($data['gibbonSchoolYearID']) || empty($data['clinicsBlockID']) || empty($data['name']) || empty($data['active']) || empty($data['lockEnrolment']) || !is_numeric($data['maxParticipants'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if (!$clinicsGateway->exists($clinicsClinicID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Update the record
    $updated = $clinicsGateway->update($clinicsClinicID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
