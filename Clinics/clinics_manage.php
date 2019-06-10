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

    if (isset($_GET['return'])) {
        returnProcess($guid, $_GET['return'], null, null);
    }

    // School Year Picker
    if (!empty($gibbonSchoolYearID)) {
        $schoolYearGateway = $container->get(SchoolYearGateway::class);
        $yearName = $schoolYearGateway->getSchoolYearByID($gibbonSchoolYearID);

        echo '<h2>';
        echo $yearName['name'];
        echo '</h2>';

        echo "<div class='linkTop'>";
            if ($prevSchoolYear = $schoolYearGateway->getPreviousSchoolYearByID($gibbonSchoolYearID)) {
                echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q'].'&gibbonSchoolYearID='.$prevSchoolYear['gibbonSchoolYearID']."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
			echo ' | ';
			if ($nextSchoolYear = $schoolYearGateway->getNextSchoolYearByID($gibbonSchoolYearID)) {
				echo "<a href='".$_SESSION[$guid]['absoluteURL'].'/index.php?q='.$_GET['q'].'&gibbonSchoolYearID='.$nextSchoolYear['gibbonSchoolYearID']."'>".__('Next Year').'</a> ';
			} else {
				echo __('Next Year').' ';
			}
        echo '</div>';
    }

    $clinicsGateway = $container->get(ClinicsGateway::class);

    // QUERY
    $criteria = $clinicsGateway->newQueryCriteria()
        ->sortBy(['sequenceNumber','clinicsClinic.name'])
        ->fromPOST();

    $clinics = $clinicsGateway->queryClinicsBySchoolYear($criteria, $gibbonSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('clinics', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->setURL('/modules/Clinics/clinics_manage_add.php')
        ->displayLabel();

    $table->modifyRows(function ($clinic, $row) {
        if ($clinic['active'] == 'N') $row->addClass('error');
        return $row;
    });

    $table->addColumn('blockName', __('Block'))
        ->sortable(['sequenceNumber', 'clinicsClinic.name']);

    $table->addColumn('name', __('Name'))
        ->sortable(['sequenceNumber','clinicsClinic.name']);

    $table->addColumn('department', __('Department'))
        ->sortable(['department']);

    $table->addColumn('space', __('Facility'))
        ->sortable(['space']);

    // ACTIONS
    $table->addActionColumn()
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsClinicID')
        ->format(function ($clinic, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Clinics/clinics_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Clinics/clinics_manage_delete.php');
        });

    echo $table->render($clinics);
}
