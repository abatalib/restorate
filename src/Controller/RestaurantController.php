<?php

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Restaurant;
use App\Entity\Review;
use App\Form\RestaurantType;
use App\Repository\CityRepository;
use App\Repository\MediaRepository;
use App\Repository\RestaurantRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use App\Service\CommunService;
use App\Service\RestaurantService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\TextUI\XmlConfiguration\LogToReportMigration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/restaurant")
 */
class RestaurantController extends AbstractController
{
    /**
     * @var RestaurantService
     */
    private RestaurantService $restaurantService;

    public function __construct(RestaurantService $restaurantService)
    {
        $this->restaurantService = $restaurantService;
    }

    /**
     * @Route("/detail/{restaurant_id}", name="restaurant_page_detail_restaurant", methods={"GET"})
     */
    public function detailResto(?Restaurant $restaurant_id,
                                MediaRepository $mediaRepository,
                                ReviewRepository $reviewRepository): Response
    {
        if($restaurant_id!=null):
            $medias = $mediaRepository->findBy(['restaurant' => $restaurant_id]) ?? "";
            $reviews = $reviewRepository->findBy(['restaurant' => $restaurant_id]) ?? "";
            $recap = $this->restaurantService->getNotesAndMsg($restaurant_id) ?? "";
        endif;

        return $this->render('restaurant/restaurant-detail.html.twig', [
            'restaurant' => $recap,
            'medias' => $medias,
            'reviews' => $reviews
        ]);
    }
}
