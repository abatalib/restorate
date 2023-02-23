<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Entity\Review;
use App\Form\CommentType;
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
                               MediaRepository $mediaRepository,
                                ReviewRepository $reviewRepository,
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


        //instancier review s'il s'agit d'un new sinon
        //le review passé pour edit
        $review ?? $review = new Review();

        //création du formtype
        $form = $this->createForm(CommentType::class, $review);

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
        if($restaurant!=null):
            $medias = $mediaRepository->findBy(['restaurant' => $restaurant]) ?? "";
            $reviews = $reviewRepository->findBy(['restaurant' => $restaurant]) ?? "";
            $recap = $restaurantService->getNotesAndMsg($restaurant) ?? "";
        else:
            $medias = ""; $reviews = ""; $recap = "";
        endif;
        return $this->render('comment/add-comment.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $recap,
            'medias' => $medias,
            'reviews' => $reviews
        ]);
    }

    /**
     * @Route("/reply/{review}", name="comment_page_reply")
     */
    public function reply(?Review $review,
                            Request $request,
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
        $form = $this->createForm(CommentType::class, $review);

        $form->handleRequest($request);
        //récupérer la réponse
        $resp=$form->get("resp")->getData();

        //si le formulaire est envoyé ainsi que
        // la réponse est passée
        if ($form->isSubmitted() &&
            $resp!="") {

            $review->setMessage($resp);

            $manager->persist($review);
            $manager->flush();
            $this->addFlash("success","Opération effectuée avec succès");
            return $this->redirectToRoute('restaurant_page_detail_restaurant', ['restaurant_id'=>$review->getRestaurant()->getId()]);
        }

        //préparer les données à afficher dans le twig
        if($restaurant!=null):
            $medias = $mediaRepository->findBy(['restaurant' => $restaurant]) ?? "";
            $reviews = $reviewRepository->findBy(['restaurant' => $restaurant]) ?? "";
            $recap = $restaurantService->getNotesAndMsg($restaurant) ?? "";
        else:
            $medias = ""; $reviews = ""; $recap = "";
        endif;

        return $this->render('comment/add-response.html.twig.html.twig', [
            'form' => $form->createView(),
            'restaurant' => $recap,
            'medias' => $medias,
            'reviews' => $reviews
        ]);
    }
}
