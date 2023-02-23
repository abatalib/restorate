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

    /**
     * @Route("/private", name="home_page_private", methods={"GET"})
     */
    public function privateSpace(): Response
    {
        $user=$this->getUser();

        if(!$user):
            return $this->redirectToRoute("app_login");
        elseif($user->getRoles()[0]=="ROLE_CLIENT"):
            return $this->redirectToRoute("client_page_profil");
        elseif($user->getRoles()[0]=="ROLE_RESTAURATEUR"):
            return $this->redirectToRoute("restaurateur_page_profil");
        endif;
        return new Response("Error");
    }
}
