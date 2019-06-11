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

use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;

if (isModuleAccessible($guid, $connection2) == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');

    $page->breadcrumbs
        ->add(__m('View Clinics'));

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    $clinicsGateway = $container->get(ClinicsGateway::class);

    // QUERY
    $criteria = $clinicsGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber','clinicsClinic.name'])
        ->fromPOST();

    $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $gibbonSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('clinics', $criteria);

    $table->setTitle(__m('Clinics'));

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

    echo $table->render($clinics);
}
