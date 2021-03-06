<?php

namespace SageBundle\Repository\Company;

use AppBundle\Services\ClosureManager;

/**
 * ClosureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ClosureRepository extends \Doctrine\ORM\EntityRepository
{
    public function getCurrentByCompany($company)
    {
        return $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->andWhere('c.status = :status')
            ->setParameter('company', $company)
            ->setParameter('status', ClosureManager::CURRENT)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getOneByCompanyAndDate($company, $date)
    {
        return $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->andWhere('c.periodEnd >= :date')
            ->setParameter('company', $company)
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastValidatedByCompany($company)
    {
        return $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->andWhere('c.status = :status')
            ->orderBy('c.periodStart', 'DESC')
            ->setParameter('company', $company)
            ->setParameter('status', ClosureManager::VALIDATED)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLockedInterval($company)
    {
        $interval = $this->createQueryBuilder('c')
            ->select('min(c.periodStart) as start_date, max(c.periodEnd) as end_date')
            ->where('c.company = :company')
            ->andWhere('c.status in (:status)')
            ->setParameter('company', $company)
            ->setParameter('status', array(ClosureManager::VALIDATED, ClosureManager::PAYSLIP, ClosureManager::COMPLETED))
            ->getQuery()
            ->getOneOrNullResult();

        $lockedInterval = array('start_date' => null, 'end_date' => null);

        if ($interval) {
            $lockedInterval['start_date'] = \DateTime::createFromFormat('Y-m-d H:i:s', $interval['start_date']);
            $lockedInterval['end_date'] = \DateTime::createFromFormat('Y-m-d H:i:s', $interval['end_date']);
        }

        return $lockedInterval;
    }

    public function findOverlap($company, $start, $end)
    {
        return $this->createQueryBuilder('c')
            ->select('c.id')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->andWhere('c.periodStart <= :end')
            ->andWhere('c.periodEnd >= :start')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getOneByCompanyAndPeriodStart($company, $date)
    {
        return $this->createQueryBuilder('c')
            ->where('c.company = :company')
            ->andWhere('c.periodStart = :date')
            ->setParameter('company', $company)
            ->setParameter('date', $date)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getClosureStatus($company, $start, $end){
        return $this->createQueryBuilder('c')
            ->select('c.status')
            ->where('c.company = :company')
            ->setParameter('company', $company)
            ->andWhere('c.periodStart = :start')
            ->andWhere('c.periodEnd = :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getClosureByCompanyAndDates($start, $end){
        return $this->createQueryBuilder('c')
            ->select('c.id, c.validatedAt, c.existingFile, c.status, cc.name as companyname, cc.id as companyid, cc.setSageDbName as sagedbname')
            ->innerJoin('c.company','cc')
            ->andWhere('c.periodStart = :start')
            ->andWhere('c.periodEnd = :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

}
