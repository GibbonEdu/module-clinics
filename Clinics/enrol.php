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
use Gibbon\Tables\DataTable;
use Gibbon\Tables\View\GridView;
use Gibbon\Module\Clinics\Domain\ClinicsBlocksGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/enrol.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $gibbon->session->get('gibbonSchoolYearID');

    $page->breadcrumbs
        ->add(__m('Enrol'));

    $enrolmentActive = getSettingByScope($connection2, 'Clinics', 'enrolmentActive');
    if ($enrolmentActive != "Y") {
        $page->addMessage(__m('Enrolment is not currently open.'));
    }
    else {
        $returns = array();
        $returns['error3'] = __m('The selected clinic is full: please select another clinic.');
        if (isset($_GET['return'])) {
            returnProcess($guid, $_GET['return'], null, $returns);
        }

        //Query blocks in the current year
        $clinicsBlocksGateway = $container->get(ClinicsBlocksGateway::class);

        $criteria = $clinicsBlocksGateway->newQueryCriteria()
            ->sortBy(['clinicsBlock.sequenceNumber'])
            ->fromPOST();

        $blocks = $clinicsBlocksGateway->queryBlockEnrolmentByStudent($criteria, $gibbon->session->get('gibbonPersonID'), $gibbon->session->get('gibbonSchoolYearID'));

        // Data Table
        $gridRenderer = new GridView($container->get('twig'));
        $table = $container->get(DataTable::class)->setRenderer($gridRenderer);
        $table->setTitle(__('Enrol'));

        $table->addMetaData('gridClass', 'rounded-sm bg-gray-100 border py-2');
        $table->addMetaData('gridItemClass', 'w-1/2 sm:w-1/3 p-4 text-center');

        $table->addColumn('name')
            ->setClass('text-md text-purple-600 mt-1');

        $table->addColumn('dateRange')
            ->setClass('text-xs font-light italic mb-4')
            ->format(function ($block) {
                return Format::dateRange($block['firstDay'], $block['lastDay']);
            });

        $table->addColumn('clinicName')
            ->setClass('text-lg font-bold mb-1')
            ->format(function ($block) use ($gibbon) {
                if ($block['clinicName'] != '') {
                    return $block['clinicName'];
                }
                else {
                    return "<a href='".$gibbon->session->get('absoluteURL')."/index.php?q=/modules/Clinics/enrol_add.php&clinicsBlockID=".$block['clinicsBlockID']."'><img src='./themes/".$gibbon->session->get('gibbonThemeName')."/img/page_new.png'/></a>";
                }
            });

        $table->addColumn('extra')
            ->setClass('text-xs font-light italic mb-4')
            ->format(function ($block) {
                if ($block['location'] != '') {
                    return $block['status']." . ".$block['location'];
                }
                else {
                    return $block['status'];
                }
            });

        //Pass blocks into table
        echo $table->render($blocks);
    }
}
