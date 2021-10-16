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

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_enrolment_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $clinicsClinicID = $_GET['clinicsClinicID'] ?? '';
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Manage Enrolment'), 'clinics_manage_enrolment.php', ['clinicsClinicID' => $clinicsClinicID, 'gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Enrol'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    if (!empty($clinicsBlockID)) {
        echo "<div class='linkTop'>";
        echo "<a href='".$session->get('absoluteURL').'/index.php?q=/modules/Clinics/clinics_manage_enrolment.php&gibbonSchoolYearID='.$gibbonSchoolYearID."&clinicsBlockID=".$clinicsBlockID."&clinicsClinicID=$clinicsClinicID'>".__('Back').'</a>';
        echo '</div>';
    }

    if (empty($clinicsClinicID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $form = Form::create('clinicsManage', $gibbon->session->get('absoluteURL').'/modules/Clinics/clinics_manage_enrolment_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);
    $form->addHiddenValue('clinicsClinicID', $clinicsClinicID);

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
