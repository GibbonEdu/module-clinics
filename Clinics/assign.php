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
use Gibbon\Services\Format;
use Gibbon\Tables\Prefab\ReportTable;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;
use Gibbon\Module\Clinics\Domain\ClinicsStudentsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/assign.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('You do not have access to this action.');
    echo '</div>';
} else {
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        //Proceed!
        $page->breadcrumbs->add(__('Assign Clinics'));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null);
        }

        echo '<h2>';
        echo __('Filter');
        echo '</h2>';

        $gibbonYearGroupID = $_GET['gibbonYearGroupID'] ?? '';

        $form = Form::create('filter', $gibbon->session->get('absoluteURL').'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$gibbon->session->get('module').'/assign.php');

        $row = $form->addRow();
            $row->addLabel('gibbonYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('gibbonYearGroupID')->selected($gibbonYearGroupID)->placeholder()->required();

        $row = $form->addRow();
            $row->addSearchSubmit($gibbon->session, __('Clear Filters'));

        echo $form->getOutput();

        if (!empty($gibbonYearGroupID)) {
            //Get departments
            try {
                $dataDepartments = array();
                $sqlDepartments = "SELECT gibbonDepartmentID as value, name, nameShort FROM gibbonDepartment WHERE type='Learning Area' ORDER BY name";
                $resultDepartments = $connection2->prepare($sqlDepartments);
                $resultDepartments->execute($dataDepartments);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            $departmentsArray = ($resultDepartments->rowCount() > 0)? $resultDepartments->fetchAll() : array();

            $form = Form::create('assign', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/assignProcess.php');
            $form->setTitle('Priorities');
            $form->setClass('w-full blank');
            $form->addHiddenValue('address', $gibbon->session->get('address'));
            $form->addHiddenValue('gibbonYearGroupID', $gibbonYearGroupID);

            $table = $form->addRow()->addTable()->setClass('mini fullWidth');

            //Fetch students
            try {
                $dataStudents = array('gibbonSchoolYearID' => $gibbon->session->get('gibbonSchoolYearID'), 'gibbonYearGroupID' => $gibbonYearGroupID, 'today' => date('Y-m-d'));
                $sqlStudents = "SELECT gibbonPerson.gibbonPersonID, gibbonStudentEnrolmentID, gibbonStudentEnrolment.gibbonSchoolYearID, gibbonPerson.title, gibbonPerson.preferredName, gibbonPerson.surname
                    FROM gibbonPerson
                        INNER JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID)
                        INNER JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID)
                    WHERE
                        gibbonStudentEnrolment.gibbonYearGroupID=:gibbonYearGroupID
                        AND gibbonStudentEnrolment.gibbonSchoolYearID=:gibbonSchoolYearID
                        AND (gibbonPerson.dateStart IS NULL OR gibbonPerson.dateStart <= :today)
                        AND (gibbonPerson.dateEnd IS NULL OR gibbonPerson.dateEnd >= :today)
                        AND status='Full'
                    ORDER BY surname, preferredName";
                $resultStudents = $connection2->prepare($sqlStudents);
                $resultStudents->execute($dataStudents);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }

            //Fetch student enrolments
            $studentEnrolmentsArray = array();

            $clinicsStudentsGateway = $container->get(ClinicsStudentsGateway::class);

            $criteria = $clinicsStudentsGateway->newQueryCriteria()
                ->fromPOST();

            $studentEnrolments = $clinicsStudentsGateway->queryStudentEnrolmentBySchoolYear($criteria, $gibbon->session->get('gibbonSchoolYearID'), $gibbonYearGroupID);

            foreach ($studentEnrolments as $studentEnrolment) {
                $studentEnrolmentsArray[$studentEnrolment['gibbonPersonID']][$studentEnrolment['clinicsBlockID']]['id'] = $studentEnrolment['clinicsClinicID'];
                $studentEnrolmentsArray[$studentEnrolment['gibbonPersonID']][$studentEnrolment['clinicsBlockID']]['status'] = $studentEnrolment['status'];
                $studentEnrolmentsArray[$studentEnrolment['gibbonPersonID']][$studentEnrolment['clinicsBlockID']]['name'] = $studentEnrolment['name'];
            }

            //Fetch priorities
            $priorities = array() ;
            try {
                $dataPriorities = array('gibbonYearGroupID' => $gibbonYearGroupID, 'gibbonSchoolYearID' => $gibbon->session->get('gibbonSchoolYearID'));
                $sqlPriorities = 'SELECT *
                    FROM clinicsPriority
                        INNER JOIN gibbonStudentEnrolment ON (gibbonStudentEnrolment.gibbonPersonID=clinicsPriority.gibbonPersonID)
                    WHERE
                        gibbonStudentEnrolment.gibbonYearGroupID=:gibbonYearGroupID
                        AND clinicsPriority.gibbonSchoolYearID=:gibbonSchoolYearID';
                $resultPriorities = $connection2->prepare($sqlPriorities);
                $resultPriorities->execute($dataPriorities);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            while ($rowPriorities = $resultPriorities->fetch()) {
                $priorities[$rowPriorities['gibbonPersonID']][$rowPriorities['gibbonDepartmentID']] = $rowPriorities['priority'];
            }

            //Fetch blocks
            $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

            $criteria = $clinicsBlocksGateway->newQueryCriteria()
                ->sortBy(['clinicsBlock.sequenceNumber'])
                ->fromPOST();

            $blocks = $clinicsBlocksGateway->queryBlocksBySchoolYear($criteria, $gibbon->session->get('gibbonSchoolYearID'));

            //Assemble clinic select array
            $clinicsArray = array();

            $clinicsGateway = $container->get(ClinicsGateway::class);

            $criteria = $clinicsGateway->newQueryCriteria()
                ->sortBy(['sequenceNumber','clinicsClinic.name'])
                ->fromPOST();

            $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $gibbon->session->get('gibbonSchoolYearID'), $gibbonYearGroupID);

            foreach ($clinics AS $clinic) {
                $clinicsArray[$clinic['clinicsBlockID']][$clinic['clinicsClinicID']] = $clinic['name'] ;
            }

            //List priorities
            $priorityListing = array(
                '1' => __('Low'),
                '2' => __('Mid'),
                '3' => __('High')
            );

            if ($resultStudents->rowCount() <= 0) {
                $row = $table->addHeaderRow();
            }
            else {
                $row = $table->addHeaderRow();
                $row->addContent(__('Count'))->addClass('w-12');
                $row->addContent(__('Student'))->addClass('w-48');

                // Add headings for each department
                foreach ($departmentsArray as $department) {
                    $row->addContent(__($department['nameShort']))->addClass('w-24');
                }

                //Add headings for each blocks
                foreach ($blocks as $block) {
                    $row->addContent(__($block['name']))->addClass('w-24');
                }

                $count = 1;
                while ($rowStudents = $resultStudents->fetch()) {
                    $row = $table->addRow();

                    $row->addContent($count);

                    $row->addWebLink(Format::name('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true, true))
                        ->setURL('index.php?q=/modules/Students/student_view_details.php')
                        ->addParam('gibbonPersonID', $rowStudents['gibbonPersonID'])
                        ->setTarget('_blank');

                    //Add student entry for each department
                    foreach ($departmentsArray as $department) {
                        $priority = $priorities[$rowStudents['gibbonPersonID']][$department['value']] ?? '';
                        $color = '';
                        if ($priority == 1) {
                            $color = 'bg-orange-500 text-white font-bold';
                        }
                        else if ($priority == 2) {
                            $color = 'bg-red-500 text-white font-bold';
                        }
                        else if ($priority == 3) {
                            $color = 'bg-purple-500 text-white font-bold';
                        }
                        else {
                            $color = 'bg-gray-300 text-gray-400';
                        }
                        $row->addContent($priorityListing[$priority] ?? 'N/A')->setClass("$color text-center");
                    }


                    //Add student entry for each block
                    $blockCount = 0;
                    foreach ($blocks as $block) {
                        if (!empty($clinicsArray[$block['clinicsBlockID']])) {
                            $selected = (!empty($studentEnrolmentsArray[$rowStudents['gibbonPersonID']][$block['clinicsBlockID']])) ? $studentEnrolmentsArray[$rowStudents['gibbonPersonID']][$block['clinicsBlockID']] : NULL;
                            if ($selected['status'] == 'Enroled') {
                                $row->addContent($selected['name'])->setClass('float-none w-24 text-center');
                            }
                            else {
                                $row
                                    ->addSelect('clinics'.$block['clinicsBlockID'].'[]')
                                    ->fromArray($clinicsArray[$block['clinicsBlockID']])
                                    ->selected($selected['id'])
                                    ->placeholder()
                                    ->setClass('float-none w-24');
                                $form->addHiddenValue('gibbonPersonIDs'.$block['clinicsBlockID'].'[]', $rowStudents['gibbonPersonID']);
                            }
                        }
                        else {
                            $row->addContent('')->setClass('float-none w-24');
                        }
                        $blockCount++;
                    }

                    $count++;
                }
            }

            $row = $form->addRow();
            $row->addSubmit();

            echo $form->getOutput();
        }
    }
}
