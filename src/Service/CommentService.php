<?php

namespace App\Service;

use App\Entity\Restaurant;
use App\Entity\Review;
use App\Repository\ReviewRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentService
{
    private ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function verifyBeforeSaveComment(Restaurant $restaurant, UserInterface $currentUser): string
    {
        // récupérer le restaurateur propriétaire du resto
        $owner = $restaurant->getUser()->getId();
        // si le restaurateur et l'user connecté sont similaires => pas d'ajout
        if($currentUser->getId()===$owner)
            return "Le propriétaire ne peut commenter son restaurant, mais il peut répondre à un commentaire";

        // tester si l'user connecté a déjà commenté le même resto
        $alreadyCommented=$this->reviewRepository->findBy(['restaurant'=>$restaurant, 'user'=>$currentUser->getId()]);
        if($alreadyCommented)
            return "Vous avez déjà commenté ce restaurant.";

        return "ok";
    }

    public function verifyBeforeRespComment(Review $review, UserInterface $currentUser): string
    {
        //test s'il s'agit du restaurateur propriétaire
        $owner=$review->getRestaurant()->getUser()->getId();
        if($owner!=$currentUser->getId())
            return "Seul le propriétaire du restaurant pourrait répondre";

        //test si on a déjà répondu à ce commentaire
        if($review->getResp())
            return "Propriétaire a déjà répondu";

        return "ok";
    }
}