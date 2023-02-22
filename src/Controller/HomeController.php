<?php

namespace App\Controller;

use App\Repository\RestaurantRepository;
use App\Service\RestaurantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct()
    {
    }

    /**
     * @Route("/", name="home_page", methods={"GET"})
     */
    public function index(RestaurantService $restaurantService,
                            RestaurantRepository $restaurantRepository): Response
    {
        $result=$restaurantRepository->findBy([],['created_at'=>'DESC'],10) ?? [];
        $newResult=$restaurantService->getRestaurants($result) ?? [];

        return $this->render('home/index.html.twig', [
            'restaurants' => $newResult,
            'src'=>'home'
        ]);
    }
}
