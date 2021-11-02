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

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $schoolYear = $schoolYearGateway->getSchoolYearByID($gibbonSchoolYearID);
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';
    $yearName = $schoolYear['name'];
    $clinicsClinicID = $_GET['clinicsClinicID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Edit Clinic'));

    if ($clinicsBlockID != '') {
        echo "<div class='linkTop'>";
        echo "<a href='".$session->get('absoluteURL')."/index.php?q=/modules/Clinics/clinics_manage.php&gibbonSchoolYearID=".$gibbonSchoolYearID."&clinicsBlockID=".$clinicsBlockID."'>".('Back to Search Results')."</a>";
        echo "</div>";
    }

    if (empty($clinicsClinicID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(ClinicsGateway::class)->getByID($clinicsClinicID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('clinic', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/clinics_manage_editProcess.php?clinicsBlockID='.$clinicsBlockID);
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('clinicsClinicID', $clinicsClinicID);

    $row = $form->addRow();
        $row->addLabel('yearName', __('School Year'));
        $row->addTextField('yearName')->readonly()->setValue($yearName)->required();

    $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
    $sql = "SELECT clinicsBlockID as value, name FROM clinicsBlock WHERE gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY sequenceNumber";
    $row = $form->addRow();
        $row->addLabel('clinicsBlockID', __('Block'));
        $row->addSelect('clinicsBlockID')->fromQuery($pdo, $sql, $data)->placeholder()->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Name'));
        $row->addTextField('name')->required()->maxLength(40);

    $row = $form->addRow();
        $row->addLabel('description', __('Description'));
        $row->addTextArea('description')->setRows(4);

    $sql = "SELECT gibbonDepartmentID as value, name FROM gibbonDepartment WHERE type='Learning Area' ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('gibbonDepartmentID', __('Learning Area'));
        $row->addSelect('gibbonDepartmentID')->fromQuery($pdo, $sql)->placeholder();

    $row = $form->addRow();
        $row->addLabel('gibbonYearGroupIDList', __('Year Groups'));
        $row->addCheckboxYearGroup('gibbonYearGroupIDList')->addCheckAllNone()->loadFromCSV($values);

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
		$row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('lockEnrolment', __('Lock Enrolment'))->description(__m('Should students be prevented from enroling?'));
		$row->addYesNo('lockEnrolment')->required();

    $row = $form->addRow();
        $row->addLabel('maxParticipants', __('Max Participants'));
		$row->addNumber('maxParticipants')->required()->maxLength(3)->setValue('0');

    $sql = "SELECT gibbonSpaceID as value, name FROM gibbonSpace ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('gibbonSpaceID', __('Location'));
        $row->addSelect('gibbonSpaceID')->fromQuery($pdo, $sql)->placeholder();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
