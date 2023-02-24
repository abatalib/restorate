<?php

namespace App\Controller;

use App\Entity\Recherche;
use App\Form\RechercheType;
use App\Repository\RestaurantRepository;
use App\Service\RestaurantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct()
    {
    }

    /**
     * @Route("/", name="home_page_racine", methods={"GET","POST"})
     * @Route("/page/{page}", name="home_page", methods={"GET","POST"})
     *
     */
    public function index(RestaurantService $restaurantService,
                          RestaurantRepository $restaurantRepository,
                          Request $request,
                          ?int $page=0): Response
    {
        $criteria=[];
        //////////////si l'utilisateur a lancé une recherche/////////
        $recherche ?? $recherche = new Recherche();
        //création du formtype
        $form = $this->createForm(RechercheType::class, $recherche);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $restoName = $form->get("name")->getData() ?? [];
            $cityName = $form->get("city")->getData() ?? [];
                if($restoName!=null){
                    $restoName=["name"=>$restoName];
                }
                if($cityName!=null){
                    $cityName = ["city" => $cityName];
                }
                $criteria=array_merge($restoName,$cityName);
        }
        //////////////////////////////////////////////////////////////
        $nbrPerPage=10;
        if($page>0) $page--;

        $result=$restaurantRepository->getRestaurants($criteria,[$nbrPerPage, $page*$nbrPerPage]);
//            findBy($criteria,['created_at'=>'DESC'], $nbrPerPage, $page*$nbrPerPage) ?? [];
        $newResult=$restaurantService->getRestaurants($result) ?? [];

        //params à passer pour la pagination
        //nombre total des restaurants
        empty($criteria) ?
            $totalRestaurants = $restaurantRepository->count([])
        :
            $totalRestaurants = count($result);

        //nbre de pages (boutons) affichant les restaurants 10 par 10
        $totalPages = (int)ceil($totalRestaurants/$nbrPerPage);

        return $this->render('home/index.html.twig', [
            'restaurants' => $newResult,
            'src'=>'home',
            'totalRestaurants'=>$totalRestaurants,
            'totalPages'=>$totalPages,
            'page'=>$page,
            'form'=>$form->createView()
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
