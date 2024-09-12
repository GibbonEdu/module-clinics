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

use Gibbon\Http\Url;
use Gibbon\Services\Format;
use Gibbon\Tables\DataTable;
use Gibbon\Module\Clinics\Domain\ClinicsGateway;
use Gibbon\Module\Clinics\Domain\ClinicsStudentsGateway;

if (isActionAccessible($guid, $connection2, '/modules/Clinics/clinics_manage_enrolment.php') == false) {
    // Access denied
    $page->addError(__('You do not have access to this action.'));
} else {
    // Proceed!
    $gibbonSchoolYearID = $_REQUEST['gibbonSchoolYearID'] ?? $gibbon->session->get('gibbonSchoolYearID');
    $clinicsBlockID = $_GET['clinicsBlockID'] ?? '';

    $page->breadcrumbs
        ->add(__m('Manage Clinics'), 'clinics_manage.php', ['gibbonSchoolYearID' => $gibbonSchoolYearID])
        ->add(__m('Manage Enrolment'));

    if ($clinicsBlockID != '') {
        $params = [
            "gibbonSchoolYearID" => $gibbonSchoolYearID,
            "clinicsBlockID" => $clinicsBlockID
        ];
        $page->navigator->addSearchResultsAction(Url::fromModuleRoute('Clinics', 'clinics_manage.php')->withQueryParams($params));
    }

    $clinicsGateway = $container->get(ClinicsGateway::class);
    $clinicsStudentsGateway = $container->get(ClinicsStudentsGateway::class);

    $clinicsClinicID = $_GET['clinicsClinicID'] ?? '';

    if (empty($clinicsClinicID)) {
        $page->addError(__('You have not specified one or more required parameters.'));
        return;
    }

    $clinic = $container->get(ClinicsGateway::class)->getByID($clinicsClinicID);
    if (empty($clinic)) {
        $page->addError(__('The specified record cannot be found.'));
        return;
    }

    // QUERY
    $criteria = $clinicsGateway->newQueryCriteria()
        ->sortBy(['gibbonPerson.surname', 'gibbonPerson.preferredName'])
        ->fromPOST();

    $enrolment = $clinicsStudentsGateway->queryStudentEnrolmentByClinic($criteria, $clinicsClinicID);

    // DATA TABLE
    $table = DataTable::createPaginated('clinics', $criteria);
    $table->setTitle($clinic['name']);
    $table->addMetaData('blankSlate', __m('There are currently no members in this clinic.'));

    $table->addHeaderAction('add', __('Add'))
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsBlockID', $clinicsBlockID)
        ->addParam('clinicsClinicID', $clinicsClinicID)
        ->setURL('/modules/Clinics/clinics_manage_enrolment_add.php')
        ->displayLabel();

    $table->addColumn('fullName', __('Name'))
        ->sortable(['gibbonPerson.surname', 'gibbonPerson.preferredName'])
        ->format(function ($person) {
            return Format::name('', $person['preferredName'], $person['surname'], 'Staff', true, true);
        });

    $table->addColumn('status', __('Status'));

    // ACTIONS
    $table->addActionColumn()
        ->addParam('gibbonSchoolYearID', $gibbonSchoolYearID)
        ->addParam('clinicsBlockID', $clinicsBlockID)
        ->addParam('clinicsClinicID', $clinicsClinicID)
        ->addParam('clinicsClinicStudentID')
        ->format(function ($person, $actions) {
            $actions->addAction('edit', __('Edit'))
                    ->setURL('/modules/Clinics/clinics_manage_enrolment_edit.php');

            $actions->addAction('delete', __('Delete'))
                    ->setURL('/modules/Clinics/clinics_manage_enrolment_delete.php');
        });

    echo $table->render($enrolment);
}
