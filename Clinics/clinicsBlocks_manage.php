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

use Gibbon\Tables\DataTable;
use Gibbon\Services\Format;
use Gibbon\Domain\School\SchoolYearGateway;
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinicsBlocks_manage.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $session->get('gibbonSchoolYearID');

    $page->breadcrumbs
        ->add(__m('Manage Blocks'));

    // School Year Picker
    if (!empty($gibbonSchoolYearID)) {
        $page->navigator->addSchoolYearNavigation($gibbonSchoolYearID);
    }

    //Query blocks
    $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

    $criteria = $clinicsBlocksGateway->newQueryCriteria()
        ->sortBy(['clinicsBlock.sequenceNumber'])
        ->fromPOST();

    $blocks = $clinicsBlocksGateway->queryBlocksBySchoolYear($criteria, $gibbonSchoolYearID);

    //Render table
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
