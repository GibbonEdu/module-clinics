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
use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');

    $page->breadcrumbs
        ->add(__m('Manage Clinics'));

    // School Year Picker
    if (!empty($gibbonSchoolYearID)) {
        $page->navigator->addSchoolYearNavigation($gibbonSchoolYearID);
    }

    //Filter
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    $form = Form::create('search', $session->get('absoluteURL').'/index.php', 'get');
    $form->setTitle(__('Filter'));
    $form->setClass('noIntBorder w-full');

    $form->addHiddenValue('q', '/modules/'.$session->get('module').'/clinics_manage.php');

    $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID);
    $sql = "SELECT clinicsBlockID as value, name FROM clinicsBlock WHERE gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY sequenceNumber";
    $row = $form->addRow();
        $row->addLabel('clinicsBlockID', __('Block'));
        $row->addSelect('clinicsBlockID')->fromQuery($pdo, $sql, $data)->placeholder()->selected($clinicsBlockID);

    $row = $form->addRow();
        $row->addSearchSubmit($gibbon->session, __('Clear Search'));

    echo $form->getOutput();

    $clinicsGateway = $container->get(ClinicsGateway::class);

    // QUERY
    $criteria = $clinicsGateway->newQueryCriteria()
        ->searchBy($clinicsGateway->getSearchableColumns(), $clinicsBlockID)
        ->sortBy(['sequenceNumber','clinicsClinic.name'])
        ->fromPOST();

    $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $gibbonSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('clinics', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsBlockID', $clinicsBlockID)
        ->setURL('/modules/Clinics/clinics_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($clinic, $row) {
        if ($clinic['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addExpandableColumn('description');

    $table->addColumn('blockName', __('Block'))
        ->sortable(['sequenceNumber', 'clinicsClinic.name']);

    $table->addColumn('name', __('Name'))
        ->sortable(['sequenceNumber','clinicsClinic.name']);

    $table->addColumn('department', __('Department'))
        ->sortable(['department']);

    $table->addColumn('space', __('Location'))
        ->sortable(['space']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsClinicID')
        ->addParam('clinicsBlockID', $clinicsBlockID)
        ->format(function ($clinic, $actions) use ($gibbon) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Clinics/clinics_manage_edit.php');

            $actions->addAction('enrolment', __('Enrolment'))
                    ->setURL('/modules/Clinics/clinics_manage_enrolment.php')
                    ->setIcon('attendance');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Clinics/clinics_manage_delete.php');
        });

    echo $table->render($clinics);
}
