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

if (isActionAccessible($guid, $connection2, '/modules/Clinics/identify.php') == false) {
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
        $page->breadcrumbs->add(__('Identify Priorities  '));

        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null);
        }

        echo '<h2>';
        echo __('Filter');
        echo '</h2>';

        $gibbonYearGroupID = $_GET['gibbonYearGroupID'] ?? '';
        $gibbonDepartmentID = $_GET['gibbonDepartmentID'] ?? '';

        $form = Form::create('filter', $gibbon->session->get('absoluteURL').'/index.php', 'get');
        $form->setFactory(DatabaseFormFactory::create($pdo));
        $form->setClass('noIntBorder fullWidth');

        $form->addHiddenValue('q', '/modules/'.$gibbon->session->get('module').'/identify.php');

        $row = $form->addRow();
            $row->addLabel('gibbonYearGroupID', __('Year Group'));
            $row->addSelectYearGroup('gibbonYearGroupID')->selected($gibbonYearGroupID)->placeholder()->required();


        if ($highestAction == "Identify Priorities_all") {
            $dataDepartments = array();
            $sqlDepartments = "SELECT gibbonDepartmentID as value, name, nameShort FROM gibbonDepartment WHERE type='Learning Area' ORDER BY name";
        }
        else {
            $dataDepartments = array('gibbonPersonID' => $gibbon->session->get('gibbonPersonID'));
            $sqlDepartments = "SELECT gibbonDepartment.gibbonDepartmentID as value, name, nameShort
                    FROM gibbonDepartment
                        JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID)
                        JOIN gibbonPerson ON (gibbonDepartmentStaff.gibbonPersonID=gibbonPerson.gibbonPersonID)
                    WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                        AND gibbonDepartmentStaff.role='Coordinator'
                    ORDER BY name";
        }
        $row = $form->addRow();
            $row->addLabel('gibbonDepartmentID', __('Learning Areas'));
            $row->addSelect('gibbonDepartmentID')->fromQuery($pdo, $sqlDepartments, $dataDepartments)->selected($gibbonDepartmentID)->placeholder();

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

            //Check departmental access
            $departmentalAccess = true;
            if ($highestAction != "Identify Priorities_all") {
                try {
                    $dataDepartments = array('gibbonPersonID' => $gibbon->session->get('gibbonPersonID'), 'gibbonDepartmentID' => $gibbonDepartmentID);
                    $sqlDepartments = "SELECT gibbonDepartment.gibbonDepartmentID as value, name, nameShort
                            FROM gibbonDepartment
                                JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID)
                                JOIN gibbonPerson ON (gibbonDepartmentStaff.gibbonPersonID=gibbonPerson.gibbonPersonID)
                            WHERE gibbonPerson.gibbonPersonID=:gibbonPersonID
                                AND gibbonDepartmentStaff.role='Coordinator'
                                AND gibbonDepartment.gibbonDepartmentID=:gibbonDepartmentID
                            ORDER BY name";
                    $resultDepartments = $connection2->prepare($sqlDepartments);
                    $resultDepartments->execute($dataDepartments);
                } catch (PDOException $e) {
                    $departmentalAccess = false;
                }
                if ($resultDepartments->rowCount() != 1) {
                    $departmentalAccess = false;
                }
            }

            if (!$departmentalAccess) {
                //Acess denied
                echo "<div class='error'>";
                echo __('You do not have access to this action.');
                echo '</div>';
            } else {


                $form = Form::create('identify', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/identifyProcess.php');
                $form->setTitle('Priorities');
                $form->setClass('w-full blank');
                $form->addHiddenValue('address', $gibbon->session->get('address'));
                $form->addHiddenValue('gibbonYearGroupID', $gibbonYearGroupID);
                $form->addHiddenValue('gibbonDepartmentID', $gibbonDepartmentID);

                $table = $form->addRow()->addTable()->setClass('mini fullWidth');

                //Fetch students
                try {
                    $dataStudents = array('gibbonYearGroupID' => $gibbonYearGroupID, 'today' => date('Y-m-d'));
                    $sqlStudents = "SELECT gibbonPerson.gibbonPersonID, gibbonStudentEnrolmentID, gibbonStudentEnrolment.gibbonSchoolYearID, gibbonPerson.title, gibbonPerson.preferredName, gibbonPerson.surname
                        FROM gibbonPerson
                            INNER JOIN gibbonStudentEnrolment ON (gibbonPerson.gibbonPersonID=gibbonStudentEnrolment.gibbonPersonID)
                            INNER JOIN gibbonYearGroup ON (gibbonStudentEnrolment.gibbonYearGroupID=gibbonYearGroup.gibbonYearGroupID)
                        WHERE
                            gibbonStudentEnrolment.gibbonYearGroupID=:gibbonYearGroupID
                            AND (gibbonPerson.dateStart IS NULL OR gibbonPerson.dateStart <= :today)
                            AND (gibbonPerson.dateEnd IS NULL OR gibbonPerson.dateEnd >= :today)
                            AND status='Full'
                        ORDER BY surname, preferredName";
                    $resultStudents = $connection2->prepare($sqlStudents);
                    $resultStudents->execute($dataStudents);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
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

                $priorityListing = array(
                    '1' => __('Low'),
                    '2' => __('Medium'),
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

                    $count = 1;
                    while ($rowStudents = $resultStudents->fetch()) {
                        $row = $table->addRow();

                        $row->addContent($count);

                        $row->addWebLink(Format::name('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true, true))
                            ->setURL('index.php?q=/modules/Students/student_view_details.php')
                            ->addParam('gibbonPersonID', $rowStudents['gibbonPersonID'])
                            ->setTarget('_blank');

                        foreach ($departmentsArray as $department) {
                            $priority = $priorities[$rowStudents['gibbonPersonID']][$department['value']] ?? '';
                            if ($department['value'] == $gibbonDepartmentID) {
                                $row->addSelect('priorities[]')->fromArray($priorityListing)->selected($priority)->placeholder()->setClass('float-none w-24');
                                $form->addHiddenValue('gibbonPersonIDs[]', $rowStudents['gibbonPersonID']);
                            }
                            else {
                                $color = '';
                                if ($priority == 1) {
                                    $color = 'bg-orange-500';
                                }
                                if ($priority == 2) {
                                    $color = 'bg-red-500';
                                }
                                if ($priority == 3) {
                                    $color = 'bg-purple-500';
                                }
                                $row->addContent($priorityListing[$priority] ?? '')->setClass("$color text-white font-bold text-center");
                            }
                        }

                        $count++;
                    }
                }


                $row = $form->addRow();
                if (!empty($gibbonDepartmentID)) {
                    $row->addSubmit();
                }

                echo $form->getOutput();
            }
        }
    }
}
