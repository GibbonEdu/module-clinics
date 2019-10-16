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
use Gibbon\Forms\Form;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/enrolmentByStudent_student_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    $page->breadcrumbs
        ->add(__m('Enrolment by Student'), 'enrolmentByStudent.php');

    $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');
    $gibbonPersonID = $_GET['gibbonPersonID'] ?? '';
    $search = $_GET['search'] ?? '';
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    if (!empty($gibbonPersonID) && !empty($clinicsBlockID)) {
        $data = array('gibbonPersonID' => $gibbonPersonID, 'gibbonSchoolYearID' => $gibbonSchoolYearID);
        $sql = "SELECT surname, preferredName, gibbonPerson.gibbonPersonID, gibbonYearGroupID
            FROM gibbonPerson
                JOIN gibbonStudentEnrolment ON (gibbonStudentEnrolment.gibbonPersonID=gibbonPerson.gibbonPersonID)
            WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                AND gibbonSchoolYearID=:gibbonSchoolYearID";
        $result = $pdo->executeQuery($data, $sql);

        $person = ($result->rowCount() == 1)? $result->fetch() : '';
    }

    if (empty($gibbonPersonID) || empty($person)) {
        $page->breadcrumbs->add('Student Enrolment');

        echo '<div class="error">';
        echo __('The specified record does not exist.');
        echo '</div>';
    } else {
        $page->breadcrumbs->add(Format::name('', $person['preferredName'], $person['surname'], 'Student', true, true), 'enrolmentByStudent_student.php', ['gibbonPersonID' => $gibbonPersonID, 'search' => $search]);

        $gibbonYearGroupID = $person['gibbonYearGroupID'];

        $page->breadcrumbs
            ->add(__m('Add Clinic'));

        $enrolmentActive = getSettingByScope($connection2, 'Clinics', 'enrolmentActive');
        if ($enrolmentActive != "Y") {
            $page->addMessage(__m('Enrolment is not currently open.'));
        }
        else {

            if (isset($_GET['return'])) {
                returnProcess($guid, $_GET['return'], null, null);
            }

            //Assemble clinic select array
            $clinicsArray = array();

            $clinicsGateway = $container->get(ClinicsGateway::class);

            $criteria = $clinicsGateway->newQueryCriteria()
                ->sortBy(['sequenceNumber','clinicsClinic.name'])
                ->fromPOST()
                ->pageSize(0);

            $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $gibbon->session->get('gibbonSchoolYearID'), $gibbonYearGroupID);

            foreach ($clinics AS $clinic) {
                $clinicsArray[$clinic['clinicsBlockID']][$clinic['clinicsClinicID']] = $clinic['name'] ;
            }

            //Form
            $form = Form::create('clinic', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/enrolmentByStudent_student_addProcess.php');

            $form->addHiddenValue('address', $gibbon->session->get('address'));
            $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);
            $form->addHiddenValue('gibbonPersonID', $gibbonPersonID);
            $form->addHiddenValue('search', $search);

            $row = $form->addRow();
                $row->addLabel('clinicsClinicID', __('Clinic'));
                $row->addSelect('clinicsClinicID')
                    ->fromArray($clinicsArray[$clinicsBlockID])
                    ->placeholder()
                    ->required();

            $statuses = array("Enroled" => __m("Enroled"), "Assigned" => __m("Assigned"));
            $row = $form->addRow();
                $row->addLabel('status', __('Status'));
                $row->addSelect('status')
                    ->fromArray($statuses)
                    ->required();

            $row = $form->addRow();
                $row->addFooter();
                $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
