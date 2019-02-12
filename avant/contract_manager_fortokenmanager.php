<?php

namespace AppBundle\Services;

use \Doctrine\ORM\EntityManager;
use AppBundle\AppBundleEvents;

use SageBundle\Entity\Employee;
use SageBundle\Entity\Company\Establishment;
use SageBundle\Entity\Reference\ContractType;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class ContractManager
{
    private $manager;

    public function __construct(EntityManager $manager,
                                $dispatcher)
    {
        $this->manager = $manager;
        $this->dispatcher = $dispatcher;
    }

    public function transformCddToCdi($contract)
    {
        $contract->setType(
            $this->manager->getRepository('SageBundle:Reference\ContractType')
                ->findOneByCode('CDI')
        );
        $contract->setOldCddEndDate($contract->getEndDate());
        $contract->setOldCddTrialPeriodEndDate($contract->getTrialPeriodEndDate());
        $contract->setEndDate(null);
        $contract->setTrialPeriodEndDate(null);
        $contract->setReasonForRelease(null);

        return $contract;
    }

    public function normalizeContract($contract)
    {
        $objectNormalizer = new GetSetMethodNormalizer();
        $objectNormalizer->setIgnoredAttributes(array('id','company', 'employee', 'file', 'createdAt', 'updatedAt', 'updatedBy'));
        $objectNormalizer->setCircularReferenceHandler(function ($object) {
            return get_class($object) . '_' . $object->getId();
        });
        $objectNormalizer->setCallbacks(array(
            'codeInsee' => function($e) {
                return $e ? $e->getCode() : null;
            },
            'type' => function($e) {
                return $e ? $e->getCode() : null;
            },
            'establishment' => function($e) {
                return $e ? $e->getSiret() : null;
            }
        ));
        $serializer = new serializer([new DateTimeNormalizer(), $objectNormalizer], []);

        return $serializer->normalize($contract, null, array('enable_max_depth' => true, DateTimeNormalizer::FORMAT_KEY => 'd/m/Y'));
    }
}
