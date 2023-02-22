<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Restaurant;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\CityRepository;
use App\Repository\RestaurantRepository;
use App\Service\CommunService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    /**
     * @var CommunService
     */
    private $communService;
    private UserPasswordHasherInterface $hasher;

    public function __construct(RestaurantRepository $restaurantRepository,
                                CityRepository $cityRepository,
                                CommunService $communService,
                                UserPasswordHasherInterface $hasher)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->cityRepository = $cityRepository;
        $this->communService = $communService;
        $this->hasher = $hasher;
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
        $manager->flush();

        //ajout distinctement les rôles restaurateur et client
        $roleR = "ROLE_RESTAURATEUR";
        $roleC = "ROLE_CLIENT";

        //création de 30 users (client/restaurateur)
        for($i=0;$i<30;$i++):
             $user = new User();
            //random role soit client ou restaurateur
            $role = ($i % 3==0) ? $roleR : $roleC;

             $user->setFirstname($faker->firstName())
                 ->setLastname($faker->lastName())
                 ->setUsername($faker->userName())
                 ->setEmail($faker->email())
                 ->setCreatedAt($this->communService->generateDate())
                 ->setPassword($this->hasher->hashPassword($user,"123456"))
                 ->setRoles([$role]);

            $manager->persist($user);

            //création de restaurants pour les restaurateurs seulement
            if($role=="ROLE_RESTAURATEUR"):
                //prendre au hasard un city de la BD
                $rndId=random_int(1,50);
                $city = $this->cityRepository->findOneBy(['id'=>$rndId]);

                $rest = new Restaurant();
                $rest->setName($faker->company())
                    ->setUser($user)
                    ->setCity($city)
                    ->setCreatedAt($this->communService->generateDate());

                $manager->persist($rest);

            endif;
        endfor;

        $manager->flush();


        //affecter les cities aux restaurants
//        $restaurants = $this->restaurantRepository->findAll();
//        foreach($restaurants as $rest):
//            //prendre au hasard un city de la BD
//            $rndId=random_int(1,50);
//            $city = $this->cityRepository->findOneBy(['id'=>$rndId]);
//            $rest->setCity($city);
//            $manager->persist($rest);
//        endforeach;


        $manager->flush();
    }

}
