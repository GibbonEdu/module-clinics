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
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinicsBlocks_manage_edit.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID');
    $schoolYearGateway = $container->get(SchoolYearGateway::class);
    $schoolYear = $schoolYearGateway->getSchoolYearByID($gibbonSchoolYearID);
    $yearName = $schoolYear['name'];
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Blocks'), 'clinicsBlocks_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Edit Block'));

    if (empty($clinicsBlockID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $values = $container->get(ClinicsBlocksGateway::class)->getByID($clinicsBlockID);

    if (empty($values)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    $form = Form::create('block', $session->get('absoluteURL').'/modules/'.$session->get('module').'/clinicsBlocks_manage_editProcess.php');
    $form->setFactory(DatabaseFormFactory::create($pdo));

    $form->addHiddenValue('address', $session->get('address'));
    $form->addHiddenValue('gibbonSchoolYearID', $gibbonSchoolYearID);
    $form->addHiddenValue('clinicsBlockID', $clinicsBlockID);

    $row = $form->addRow();
        $row->addLabel('yearName', __('School Year'));
        $row->addTextField('yearName')->readonly()->setValue($yearName)->required();

    $row = $form->addRow();
        $row->addLabel('name', __('Name'))->description(__('Must be unique within school year.'));
        $row->addTextField('name')->required()->maxLength(20);

    $row = $form->addRow();
        $row->addLabel('sequenceNumber', __('Sequence Number'))->description(__('Must be unique within school year. Controls chronological ordering.'));
        $row->addSequenceNumber('sequenceNumber', 'clinicsBlock', $values['sequenceNumber'])->required()->maxLength(3);

    $row = $form->addRow();
        $row->addLabel('firstDay', __('First Day'))->description($session->get("i18n")["dateFormat"])->prepend(__('Format:'));
        $row->addDate('firstDay')->required();

    $row = $form->addRow();
        $row->addLabel('lastDay', __('Last Day'))->description($session->get("i18n")["dateFormat"])->prepend(__('Format:'));
        $row->addDate('lastDay')->required();

    $row = $form->addRow();
        $row->addFooter();
        $row->addSubmit();

    $form->loadAllValuesFrom($values);

    echo $form->getOutput();
}
