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

use Gibbon\Http\Url;
use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Domain\School\SchoolYearGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $schoolYear = $schoolYearGateway->getSchoolYearByID($gibbonSchoolYearID);
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';
    $yearName = $schoolYear['name'];

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Add Clinic'));

    if ($clinicsBlockID != '') {
        $params = [
            "gibbonSchoolYearID" => $gibbonSchoolYearID,
            "clinicsBlockID" => $clinicsBlockID
        ];
        $page->navigator->addSearchResultsAction(Url::fromModuleRoute('Clinics', 'clinics_manage.php')->withQueryParams($params));
    }

    $form = Form::create('clinic', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/clinics_manage_addProcess.php?clinicsBlockID='.$clinicsBlockID);
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);

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
        $row->addCheckboxYearGroup('gibbonYearGroupIDList')->checkAll()->addCheckAllNone();

    $row = $form->addRow();
        $row->addLabel('active', __('Active'));
		$row->addYesNo('active')->required();

    $row = $form->addRow();
        $row->addLabel('lockEnrolment', __('Lock Enrolment'))->description(__m('Should students be prevented from enroling?'));
		$row->addYesNo('lockEnrolment')->selected('N')->required();

    $row = $form->addRow();
        $row->addLabel('maxParticipants', __('Max Participants'));
		$row->addNumber('maxParticipants')->required()->maxLength(3)->setValue('20');

    $sql = "SELECT gibbonSpaceID as value, name FROM gibbonSpace ORDER BY name";
    $row = $form->addRow();
        $row->addLabel('gibbonSpaceID', __('Location'));
        $row->addSelect('gibbonSpaceID')->fromQuery($pdo, $sql)->placeholder();


    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
