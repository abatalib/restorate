<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Restaurant;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\RestaurantRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    /**
     * @var RestaurantRepository
     */
    private $restaurantRepository;
    /**
     * @var CityRepository
     */
    private $cityRepository;

    public function __construct(RestaurantRepository $restaurantRepository, CityRepository $cityRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->cityRepository = $cityRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create("en_US");

        //création de 50 cities
        for($i=0;$i<50;$i++):
            $city = new City();
            $city->setName($faker->city());

            $manager->persist($city);
        endfor;

        //ajout distinctement les rôles restaurateur et client
        $roleR = new Role();
        $roleC = new Role();
        $roleR->setName("restaurateur");
        $manager->persist($roleR);
        $roleC->setName("client");
        $manager->persist($roleC);

        //création de 30 users
        for($i=0;$i<30;$i++):
             $user = new User();
            //random role
            $role = ($i % 3==0) ? $roleR : $roleC;

             $user->setFirstname($faker->firstName())
                 ->setLastname($faker->lastName())
                 ->setUsername($faker->userName())
                 ->setEmail($faker->email())
                 ->setCreatedAt($this->generateDate())
                 ->setPassword("123456")
                 ->addRole($role);

            $manager->persist($user);

            //création de restaurants pour les restaurateurs seulement
            if($role->getName()=="restaurateur"):
                $rest = new Restaurant();
                $rest->setName($faker->company())
                    ->setUser($user)
                    ->setCity($city)//provisoirement la dernière valeur $city ci-haut
                    ->setCreatedAt($this->generateDate());

                $manager->persist($rest);
            endif;
        endfor;

        $manager->flush();


        //affecter les cities aux restaurants
        $restaurants = $this->restaurantRepository->findAll();
        foreach($restaurants as $rest):
            //prendre au hasard un city de la BD
            $rndId=random_int(1,50);
            $city = $this->cityRepository->findOneBy(['id'=>$rndId]);
            $rest->setCity($city);
            $manager->persist($rest);
        endforeach;


        $manager->flush();
    }

    private function generateDate(): \DateTimeImmutable
    {
            $dt=random_int(1,100);
            $date=new \DateTimeImmutable("2010-01-01T00:00:00", new \DateTimeZone('Europe/London'));
            $int = "P".$dt."M".($dt*2)."DT".$dt."H30M40S";
            $date = $date->add(new \DateInterval($int));

            return $date;
    }
}
