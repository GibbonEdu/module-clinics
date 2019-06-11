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
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinicsBlocks_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');

    $page->breadcrumbs
        ->add(__m('Manage Blocks'));

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
                echo "<a href='".$gibbon->session->get('absoluteURL').'/index.php?q='.$_GET['q'].'&gibbonSchoolYearID='.$prevSchoolYear['gibbonSchoolYearID']."'>".__('Previous Year').'</a> ';
            } else {
                echo __('Previous Year').' ';
            }
			echo ' | ';
			if ($nextSchoolYear = $schoolYearGateway->getNextSchoolYearByID($gibbonSchoolYearID)) {
				echo "<a href='".$gibbon->session->get('absoluteURL').'/index.php?q='.$_GET['q'].'&gibbonSchoolYearID='.$nextSchoolYear['gibbonSchoolYearID']."'>".__('Next Year').'</a> ';
			} else {
				echo __('Next Year').' ';
			}
        echo '</div>';
    }

    $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

    // QUERY
    $criteria = $clinicsBlocksGateway->newQueryCriteria()
        ->sortBy(['clinicsBlock.sequenceNumber'])
        ->fromPOST();

    $blocks = $clinicsBlocksGateway->queryBlocksBySchoolYear($criteria, $gibbonSchoolYearID);

    // DATA TABLE
    $table = DataTable::createPaginated('blocks', $criteria);

    $table->addHeaderAction('add', __('Add'))
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->setURL('/modules/Clinics/clinicsBlocks_manage_add.php')
        ->displayLabel();

    $table->addColumn('sequenceNumber', __('Sequence Number'))
        ->sortable(['clinicsBlock.sequenceNumber']);

    $table->addColumn('name', __('Name'))
        ->sortable(['clinicsBlock.name']);

    $table->addColumn('dates', __('Dates'))
        ->format(function ($block) {
            return Format::dateRange($block['firstDay'], $block['lastDay']);
        })
        ->sortable(['firstDay']);;

    // ACTIONS
    $table->addActionColumn()
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsBlockID')
        ->format(function ($block, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Clinics/clinicsBlocks_manage_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Clinics/clinicsBlocks_manage_delete.php');
        });

    echo $table->render($blocks);
}
