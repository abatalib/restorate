<?php

namespace App\Controller\SpaceClient;

use App\Entity\Restaurant;
use App\Repository\ReviewRepository;
use App\Service\RestaurantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/client")
 */
class ClientController extends AbstractController
{

    private RestaurantService $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    /**
     * @Route("/", name="client_page_profil")
     */
    public function index(): Response
    {
        return $this->render('client/index.html.twig', [
            "restaurants" => $this->restaurantService->getRestaurantsFromReviews(),
            "src" => "client"
        ]);
    }
}
