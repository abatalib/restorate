<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Review;
use App\Form\ReviewResponseType;
use App\Form\ReviewType;
use App\Repository\MediaRepository;
use App\Repository\ReviewRepository;
use App\Service\CommentService;
use App\Service\RestaurantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{

    private CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * @Route ("/add/{restaurant}", name="comment_page_add")
     */
    public function addComment(?Restaurant $restaurant,
                                RestaurantService $restaurantService,
                                Request $request,
                                EntityManagerInterface $manager): Response
    {

        //vérifier les conditions avant de continuer
        if(!$restaurant)
            return $this->redirectToRoute('home_page');

        $resp = $this->commentService->verifyBeforeSaveComment($restaurant,$this->getUser());

        if($resp!="ok"):
            $this->addFlash("error",$resp);
            return $this->redirectToRoute('restaurant_page_detail_restaurant', ['restaurant_id'=>$restaurant->getId()]);
        endif;


        //instancier review
        $review ?? $review = new Review();

        //création du formtype
        $form = $this->createForm(ReviewType::class, $review);

        $form->handleRequest($request);
        //récupérer les données
        $message=$form->get("message")->getData();
        $note=(int)$form->get("note")->getData() ?? 0;

        //si le formulaire est envoyé ainsi que
        // le message est passé autant que le resto
        if ($form->isSubmitted() &&
            $message!="") {

            $review->setMessage($message)
                    ->setNote($note)
                    ->setUser($this->getUser())//user connecté
                    ->setRestaurant($restaurant);

            $manager->persist($review);
            $manager->flush();
            $this->addFlash("success","Opération effectuée avec succès");
            return $this->redirectToRoute('restaurant_page_detail_restaurant', ['restaurant_id'=>$restaurant->getId()]);
        }
        //préparer les données à afficher dans le twig
        $recap = $restaurantService->getNotesAndMsg($restaurant) ?? "";

        return $this->render('comment/add-comment.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $recap,
            'src'=>'add'
        ]);
    }

    /**
     * @Route("/reply/{review}", name="comment_page_reply")
     */
    public function reply(?Review $review,
                          Request $request,
                          RestaurantService $restaurantService,
                          EntityManagerInterface $manager): Response
    {
        //vérifier les conditions avant de continuer
        if(!$review)
            return $this->redirectToRoute('home_page');

        $resp = $this->commentService->verifyBeforeRespComment($review,$this->getUser());

        if($resp!="ok"):
            $this->addFlash("error",$resp);
            return $this->redirectToRoute('restaurant_page_detail_restaurant', ['restaurant_id'=>$review->getRestaurant()->getId()]);
        endif;

        //création du formtype
        $form = $this->createForm(ReviewResponseType::class, $review);

        $form->handleRequest($request);
        //récupérer la réponse
        $resp=$form->get("resp")->getData();

        //si le formulaire est envoyé ainsi que
        // la réponse est passée
        if ($form->isSubmitted() &&
            $resp!="") {

            $review->setResp($resp);

            $manager->persist($review);
            $manager->flush();
            $this->addFlash("success","Opération effectuée avec succès");
            return $this->redirectToRoute('restaurant_page_detail_restaurant', ['restaurant_id'=>$review->getRestaurant()->getId()]);
        }

        //préparer les données à afficher dans le twig
        $recap = $restaurantService->getNotesAndMsg($review->getRestaurant()) ?? "";


        return $this->render('comment/add-comment.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $recap,
            'src'=>'reply'
        ]);
    }
}
