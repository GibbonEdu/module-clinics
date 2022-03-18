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
use Gibbon\Module\Clinics\Domain\ClinicsGateway;
use Gibbon\Module\Clinics\Domain\ClinicsStudentsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_enrolment_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';
    $clinicsClinicID = $_GET['clinicsClinicID'] ?? '';
    $clinicsClinicStudentID = $_GET['clinicsClinicStudentID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Manage Enrolment'), 'clinics_manage_enrolment.php', ['clinicsClinicID' => $clinicsClinicID, 'gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Edit Enrolment'));

    if (empty($clinicsClinicID) || empty($clinicsClinicStudentID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(ClinicsStudentsGateway::class)->getByID($clinicsClinicStudentID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('clinicsManage', $gibbon->session->get('absoluteURL').'/modules/Clinics/clinics_manage_enrolment_editProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);
    $form->addHiddenValue('clinicsClinicID', $clinicsClinicID);
    $form->addHiddenValue('clinicsClinicStudentID', $clinicsClinicStudentID);

    if (!empty($clinicsBlockID)) {
        $params = [
            "gibbonSchoolYearID" => $gibbonSchoolYearID,
            "clinicsBlockID" => $clinicsBlockID,
            "clinicsClinicID" => $clinicsClinicID
        ];
        $form->addHeaderAction('back', __('Back'))
            ->setURL('/modules/Clinics/clinics_manage_enrolment.php')
            ->addParams($params);
    }

    $row = $form->addRow();
        $row->addLabel('gibbonPersonID', __('Person'));
        $row->addSelectStudent('gibbonPersonID', $gibbon->session->get('gibbonSchoolYearID'))->readonly();

    $statuses = array(
        'Enroled' => __m('Enroled'),
        'Assigned' => __m('Assigned')
    );
    $row = $form->addRow();
        $row->addLabel('status', __('Status'))->description(__('Must be unique.'));
        $row->addSelect('status')->fromArray($statuses)->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
