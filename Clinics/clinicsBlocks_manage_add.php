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

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinicsBlocks_manage_add.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $schoolYear = $schoolYearGateway->getSchoolYearByID($gibbonSchoolYearID);
    $yearName = $schoolYear['name'];

    $page->breadcrumbs
        ->add(__m('Manage Blocks'), 'clinicsBlocks_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Add Block'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $form = Form::create('block', $gibbon->session->get('absoluteURL').'/modules/'.$gibbon->session->get('module').'/clinicsBlocks_manage_addProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $gibbon->session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);

    $row = $form->addRow();
        $row->addLabel('yearName', __('School Year'));
        $row->addTextField('yearName')->readonly()->setValue($yearName)->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique within school year.'));
        $row->addTextField('name')->required()->maxLength(20);

    $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
    $sql = "SELECT sequenceNumber FROM clinicsBlock WHERE gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY sequenceNumber DESC LIMIT 0, 1";
    $results = $pdo->executeQuery($data, $sql);
    $sequenceNumber = ($results && $results->rowCount() > 0)? ($results->fetchColumn(0)+1) : 1;
    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique within school year. Controls chronological ordering.'));
        $row->addSequenceNumber('sequenceNumber', 'clinicsBlock', $sequenceNumber)->required()->maxLength(3);

    $row = $form->addRow();
        $row->addLabel('firstDay', __('First Day'))->description($gibbon->session->get("i18n")["dateFormat"])->prepend(__('Format:'));
        $row->addDate('firstDay')->required();

    $row = $form->addRow();
        $row->addLabel('lastDay', __('Last Day'))->description($gibbon->session->get("i18n")["dateFormat"])->prepend(__('Format:'));
        $row->addDate('lastDay')->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    echo $form->getOutput();
}
