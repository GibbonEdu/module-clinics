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

namespace Gibbon\Module\Clinics\Domain;

use Gibbon\Domain\Traits\TableAware;
use Gibbon\Domain\QueryCriteria;
use Gibbon\Domain\QueryableGateway;

class ClinicsBlocksGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'clinicsBlock';
    private static $primaryKey = 'clinicsBlockID';
    private static $searchableColumns = [''];

    /**
     * @param QueryCriteria $criteria
     * @param Int $gibbonSchoolYearID
     * @return DataSet
     */
    public function queryBlocksBySchoolYear(QueryCriteria $criteria, Int $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['clinicsBlockID', 'gibbonSchoolYear.name AS schoolYear', 'clinicsBlock.sequenceNumber', 'clinicsBlock.name', 'clinicsBlock.firstDay', 'clinicsBlock.lastDay'])
            ->innerJoin('gibbonSchoolYear', 'clinicsBlock.gibbonSchoolYearID=gibbonSchoolYear.gibbonSchoolYearID')
            ->where('clinicsBlock.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    /**
     * @param $pdo
     * @param $gibbonSchoolYearID
     * @param $sequenceNumber
     * @param $name
     * @return bool
     */
    public function unique($pdo, $gibbonSchoolYearID, $sequenceNumber, $name)
    {
        $data = array('gibbonSchoolYearID' => $gibbonSchoolYearID, 'sequenceNumber' => $sequenceNumber, 'name' => $name);
        $sql = "SELECT clinicsBlockID FROM clinicsBlock WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND (sequenceNumber=:sequenceNumber OR name=:name)";
        return ($pdo->select($sql, $data)->rowCount() == 0 ) ? true : false ;
    }

    /**
     * @param QueryCriteria $criteria
     * @param Int $gibbonPersonID
     * @param Int $gibbonSchoolYearID
     * @return DataSet
     */
    public function queryBlockEnrolmentByStudent(QueryCriteria $criteria, Int $gibbonPersonID, Int $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['clinicsBlock.clinicsBlockID', 'clinicsBlock.sequenceNumber', 'clinicsBlock.name', 'clinicsBlock.firstDay', 'clinicsBlock.lastDay', 'clinicsClinic.name AS clinicName', 'clinicsClinicStudentID', 'gibbonPersonID', 'clinicsClinicStudent.clinicsClinicID', 'status', 'gibbonSpace.name AS location'])
            ->leftJoin('clinicsClinicStudent', 'clinicsClinicStudent.clinicsBlockID=clinicsBlock.clinicsBlockID AND clinicsClinicStudent.gibbonPersonID=:gibbonPersonID')
            ->leftJoin('clinicsClinic', 'clinicsClinicStudent.clinicsClinicID=clinicsClinic.clinicsClinicID')
            ->leftJoin('gibbonSpace', 'clinicsClinic.gibbonSpaceID=gibbonSpace.gibbonSpaceID')
            ->where('clinicsBlock.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->bindValue('gibbonPersonID', $gibbonPersonID);

        return $this->runQuery($query, $criteria);
    }
}
