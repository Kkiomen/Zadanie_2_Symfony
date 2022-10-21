<?php

namespace App\Service;

use App\Entity\City;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

class CityService
{

    private ManagerRegistry $doctrine;
    private ObjectRepository $repository;
    private NotificationService $notificationService;
    private ObjectManager $entityManager;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine, NotificationService $notificationService)
    {
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository(City::class);
        $this->entityManager = $this->doctrine->getManager();
        $this->notificationService = $notificationService;
    }


    public function saveWithoutZipCode($cityName): City
    {
        $city = new City();
        $city->setName($cityName);

        $this->entityManager->persist($city);
        $this->entityManager->flush();

        return $city;
    }

    public function getAllCities()
    {
        $cities = $this->repository->findBy([], ['name' => 'ASC']);
        return $cities;
    }

    public function getCitiesWhereLike($value)
    {
        return $this->repository
            ->createQueryBuilder('c')
            ->where('c.zipCode LIKE :value')
            ->setParameter('value', '%' . $value . '%')
            ->getQuery()
            ->getResult();
    }

    public function deleteCity($id): bool
    {
        $city = $this->repository->find($id);

        if (!$city) {
            $this->notificationService->addDanger('Takie miasto nie istnieje');
            return false;
        }

        $this->entityManager->remove($city);
        $this->entityManager->flush();
        $this->notificationService->addSuccess('Poprawnie usunięto miasto');
        return true;
    }

    public function updateZipCode(City $city, $zipCode): bool
    {
        $cityWhichHasTheSameNameAndZipCode = $this->repository->findOneBy([
            'name' => $city->getName(),
            'zipCode' => $zipCode
        ]);

        if ($cityWhichHasTheSameNameAndZipCode) {
            $this->notificationService->addDanger('Już istnieje miasto z tym kodem pocztowym');
            return false;
        }

        $city->setZipCode($zipCode);
        $this->entityManager->persist($city);
        $this->entityManager->flush();
        return true;
    }

}