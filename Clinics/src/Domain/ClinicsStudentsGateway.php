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
            ->cols(['clinicsClinicStudentID', 'clinicsClinicStudent.clinicsBlockID', 'clinicsClinicStudent.gibbonPersonID', 'clinicsClinicStudent.clinicsClinicID', 'clinicsClinicStudent.status', 'clinicsClinic.name'])
            ->innerJoin('clinicsBlock', 'clinicsClinicStudent.clinicsBlockID=clinicsBlock.clinicsBlockID')
            ->innerJoin('clinicsClinic', 'clinicsClinicStudent.clinicsClinicID=clinicsClinic.clinicsClinicID')
            ->innerJoin('gibbonPerson','clinicsClinicStudent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('clinicsBlock.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID)
            ->where('gibbonPerson.status=\'Full\'');

        if (!is_null($gibbonYearGroupID)) {
            $query
                ->where('FIND_IN_SET(:gibbonYearGroupID, clinicsClinic.gibbonYearGroupIDList)')
                ->bindValue('gibbonYearGroupID', $gibbonYearGroupID);
        }

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentEnrolmentByClinic(QueryCriteria $criteria, Int $clinicsClinicID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['clinicsClinicStudentID', 'clinicsClinicID', 'clinicsClinicStudent.gibbonPersonID', 'clinicsClinicStudent.status', 'surname', 'preferredName'])
            ->innerJoin('gibbonPerson','clinicsClinicStudent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('clinicsClinicID=:clinicsClinicID')
            ->bindValue('clinicsClinicID', $clinicsClinicID)
            ->where('gibbonPerson.status=\'Full\'');

        return $this->runQuery($query, $criteria);
    }

    public function queryStudentEnrolmentByStudent(QueryCriteria $criteria, Int $gibbonPersonID, Int $gibbonSchoolYearID)
    {
        $query = $this
            ->newQuery()
            ->from($this->getTableName())
            ->cols(['clinicsClinicStudentID', 'clinicsClinic.clinicsClinicID', 'clinicsClinic.name', 'clinicsBlock.name AS block', 'clinicsClinicStudent.status'])
            ->innerJoin('clinicsClinic','clinicsClinicStudent.clinicsClinicID=clinicsClinic.clinicsClinicID')
            ->innerJoin('clinicsBlock','clinicsClinic.clinicsBlockID=clinicsBlock.clinicsBlockID')
            ->innerJoin('gibbonPerson','clinicsClinicStudent.gibbonPersonID=gibbonPerson.gibbonPersonID')
            ->where('clinicsClinicStudent.gibbonPersonID=:gibbonPersonID AND clinicsClinic.gibbonSchoolYearID=:gibbonSchoolYearID')
            ->bindValue('gibbonPersonID', $gibbonPersonID)
            ->bindValue('gibbonSchoolYearID', $gibbonSchoolYearID);

        return $this->runQuery($query, $criteria);
    }

    public function deleteStudentEnrolmentByClinic($clinicsClinicID)
    {
        $data = ['clinicsClinicID' => $clinicsClinicID];
        $sql = "DELETE FROM clinicsClinicStudent
                WHERE clinicsClinicID = :clinicsClinicID";

        return $this->db()->delete($sql, $data);
    }
}
