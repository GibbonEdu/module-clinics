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

use Gibbon\Services\Format;
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

require_once '../../gibbon.php';

$gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
$clinicsBlockID = $_POST['clinicsBlockID'] ?? '';

$URL = $gibbon->session->get('absoluteURL').'/index.php?q=/modules/Clinics/clinicsBlocks_manage_edit.php&gibbonSchoolYearID='.$gibbonSchoolYearID.'&clinicsBlockID='.$clinicsBlockID;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinicsBlocks_manage_edit.php') == false) {
    $URL .= '&return=error0';
    header("Location: {$URL}");
    exit;
} else {

    // Proceed!
    $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

    $data = [
        'gibbonSchoolYearID'    => $_POST['gibbonSchoolYearID'] ?? '',
        'sequenceNumber'        => $_POST['sequenceNumber'] ?? '',
        'name'                  => $_POST['name'] ?? '',
        'firstDay'              => !empty($_POST['firstDay']) ? Format::dateConvert($_POST['firstDay']) : '',
        'lastDay'               => !empty($_POST['lastDay']) ? Format::dateConvert($_POST['lastDay']) : '',
    ];

    // Validate the required values are present
    if (empty($clinicsBlockID) || empty($data['gibbonSchoolYearID']) || empty($data['sequenceNumber']) || empty($data['name']) || empty($data['firstDay']) || empty($data['lastDay'])) {
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit;
    }

    // Validate the database relationships exist
    if (!$clinicsBlocksGateway->exists($clinicsBlockID)) {
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit;
    }

    // Update the record
    $updated = $clinicsBlocksGateway->update($clinicsBlockID, $data);

    $URL .= !$updated
        ? "&return=error2"
        : "&return=success0";

    header("Location: {$URL}");
}
