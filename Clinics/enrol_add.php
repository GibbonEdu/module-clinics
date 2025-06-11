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
use Gibbon\Domain\System\SettingGateway;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/enrol_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $session->get('gibbonSchoolYearID');
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    try {
        $dataStudent = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'gibbonPersonID' => $session->get('gibbonPersonID'));
        $sqlStudent = 'SELECT gibbonYearGroupID FROM gibbonStudentEnrolment WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonPersonID=:gibbonPersonID';
        $resultStudent = $connection2->prepare($sqlStudent);
        $resultStudent->execute($dataStudent);
    } catch (PDOException $e) { }

    if ($resultStudent->rowCount() != 1) {
        // Access denied
        $page->addError(__('You do not have access to this action.'));
    } else {
        $rowStudent = $resultStudent->fetch();
        $gibbonYearGroupID = $rowStudent['gibbonYearGroupID'];

        $page->breadcrumbs
            ->add(__m('Enrol'), 'enrol.php')
            ->add(__m('Add Clinic'));

        $enrolmentActive = $container->get(SettingGateway::class)->getSettingByScope('Clinics', 'enrolmentActive');
        if ($enrolmentActive != "Y") {
            $page->addMessage(__m('Enrolment is not currently open.'));
        }
        else {
            //Assemble clinic select array
            $clinicsArray = array();

            $clinicsGateway = $container->get(ClinicsGateway::class);

            $criteria = $clinicsGateway->newQueryCriteria()
                ->sortBy(['sequenceNumber','clinicsClinic.name'])
                ->fromPOST()
                ->pageSize(0);

            $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $session->get('gibbonSchoolYearID'), $gibbonYearGroupID, true);

            foreach ($clinics AS $clinic) {
                $clinicsArray[$clinic['clinicsBlockID']][$clinic['clinicsClinicID']] = $clinic['name'] ;
            }

            //Form
            $form = Form::create('clinic', $session->get('absoluteURL').'/modules/'.$session->get('module').'/enrol_addProcess.php');

            $form->addHiddenValue('address', $session->get('address'));
            $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);

            $row = $form->addRow();
                $row->addLabel('clinicsClinicID', __('Clinic'));
                $row->addSelect('clinicsClinicID')
                    ->fromArray($clinicsArray[$clinicsBlockID])
                    ->placeholder()
                    ->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
