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

use Gibbon\Forms\Form;
use Gibbon\Forms\DatabaseFormFactory;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_enrolment_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $clinicsClinicID = $_GET['clinicsClinicID'] ?? '';
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID');
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Manage Enrolment'), 'clinics_manage_enrolment.php', ['clinicsClinicID' => $clinicsClinicID, 'gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Enrol'));

    if (empty($clinicsClinicID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $form = Form::create('clinicsManage', $session->get('absoluteURL').'/modules/Clinics/clinics_manage_enrolment_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);
    $form->addHiddenValue('clinicsClinicID', $clinicsClinicID);

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
        $row->addLabel('gibbonPersonID', __('Person'))->description(__('Must be unique.'));
        $row->addSelectStudent('gibbonPersonID', $gibbonSchoolYearID)->placeholder()->required();

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

    echo $form->getOutput();
}
