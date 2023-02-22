<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class CommunService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function generateDate(): \DateTimeImmutable
    {
        $dt=random_int(1,100);
        $date=new \DateTimeImmutable("2010-01-01T00:00:00", new \DateTimeZone('Europe/London'));
        $int = "P".$dt."M".($dt*2)."DT".$dt."H30M40S";
        $date = $date->add(new \DateInterval($int));

        return $date;
    }

    public function executeSQL($sql): array
    {
        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        return $stmt->executeQuery()->fetchAllAssociative();
    }
}