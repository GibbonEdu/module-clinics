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

class ClinicsStudentsGateway extends QueryableGateway
{
    use TableAware;

    private static $tableName = 'clinicsClinicStudent';
    private static $primaryKey = 'clinicsClinicStudentID';
    private static $searchableColumns = [''];

    /**
     * @param QueryCriteria $criteria
     * @param Int $gibbonSchoolYearID
     * @param String $gibbonYearGroupID //Converts int to string for database query
     * @return DataSet
     */
    public function queryStudentEnrolmentBySchoolYear(QueryCriteria $criteria, Int $gibbonSchoolYearID, String $gibbonYearGroupID = null)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['clinicsClinicStudentID', 'clinicsClinicStudent.clinicsBlockID', 'gibbonPersonID', 'clinicsClinicStudent.clinicsClinicID', 'status'])
            ->innerJoin('clinicsBlock', 'clinicsClinicStudent.clinicsBlockID=clinicsBlock.clinicsBlockID')
            ->innerJoin('clinicsClinic', 'clinicsClinicStudent.clinicsClinicID=clinicsClinic.clinicsClinicID')
            ->where('clinicsBlock.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);

        if (!is_null($gibbonYearGroupID)) {
            $query
                ->where('FIND_IN_SET(:gibbonYearGroupID, clinicsClinic.gibbonYearGroupIDList)')
                ->bindValue('gibbonYearGroupID', $gibbonYearGroupID);
        }

        return $this->runQuery($query, $criteria);
    }
}
