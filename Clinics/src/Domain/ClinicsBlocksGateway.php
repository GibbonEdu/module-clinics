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
}
