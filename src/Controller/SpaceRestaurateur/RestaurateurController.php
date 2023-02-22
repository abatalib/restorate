<?php

namespace App\Controller\SpaceRestaurateur;

use App\Entity\Media;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Form\RestaurantType;
use App\Repository\MediaRepository;
use App\Repository\RestaurantRepository;
use App\Service\RestaurantService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/restaurateur")
 */
class RestaurateurController extends AbstractController
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
     * @Route("/", name="restaurateur_page_profil")
     */
    public function profil(RestaurantRepository $restaurantRepository): Response
    {
        //récupérer les restaurants de l'user connecté
        $restaurants = $restaurantRepository->findBy(["user" => $this->getUser()]) ?? [];
        $newResult = $this->restaurantService->getRestaurants($restaurants) ?? [];

        return $this->render('restaurateur/index.html.twig', [
            "restaurants" => $newResult,
            "src" => "restaurateur"
        ]);
    }

    /**
     * @Route("/restaurant/edit/{restaurant?0}", name="restaurateur_page_edit_resto", methods={"GET","POST"})
     * @Route("/restaurant/add", name="restaurateur_page_add_resto", methods={"GET","POST"})
     */
    public function add(?Restaurant            $restaurant,
                        Request                $request,
                        EntityManagerInterface $em): Response
    {
        //en cas d'erreur de vérification de fichier
        // (extension/prob de transfert, pas la peine de continuer
        $errorExist = false;
        //instancier restaurant s'il s'agit d'un new sinon
        //le restaurant passé pour edit
            $restaurant ?? $restaurant = new Restaurant();

        //création du formtype
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        //message à afficher soit new soit edit
        $msg = '';

        if ($form->isSubmitted() && $form->isValid()) {
            //récupérer l'user en cours
            $user = $this->getUser();

            //récupérer les images
            $image = $form->get('images')->getData();

            //si on a passé image
            if ($image) {
                //vérifier l'extension du fichier, vérif l'existence du dossier de stockage
                $resp = $this->restaurantService->verifyFile($image, $user->getId());
                //l'extension du fichier passée ne conforme pas
                if ($resp == 'EXT_NOT_ADMITTED') {
                    $this->addFlash('error', "Extension de photo ne conforme pas!");
                    $errorExist = true;
                } else if ($resp != 'OK') {
                    $this->addFlash('error', "Problème lors de transfert du fichier!");
                    $errorExist = true;
                }
            }

            //donc pas de problème de vérif de fichier
            if ($errorExist === false) {
                //si aucun id n'existe, alors c un ajout
                if (!$restaurant->getid()) {
                    $restaurant->setUser($user);
                    $restaurant->setCreatedAt(new \DateTimeImmutable());
                    $msg = 'nv';
                }
            }
            $em->persist($restaurant);
            $em->flush();

            //là nous avons l'ID du resto, on peut donc générer le nom du fichier
            //le fichier sera stocké dans d'un dossier qui porte l'id du user 1,2,3,etc
            //le fichier portera le nom : user-id_rest-id_datetime
            if ($image):
                $fileNameToDB = $this->restaurantService->transferFile($image,
                    [
                        "userId" => $user->getId(),
                        "restoId" => $restaurant->getId()
                    ]
                );
                //problème de transfert
                //sinon ajout url images dans table media
                if ($fileNameToDB[0] == 'err') {
                    $this->addFlash('error', "Restaurant ajouté avec succès. Echec lors du transfert d'image " .
                        $fileNameToDB[1]);
                } else {
                    //enregistrer l'image dans la table media
                    for ($p = 0; $p < sizeof($fileNameToDB); $p++) {
                        $media = new Media();
                        $media->setRestaurant($restaurant)
                            ->setUrl($fileNameToDB[$p])
                            ->setAltText("Image-" . $restaurant->getName());
                        $em->persist($media);
                    }
                }

                $em->flush();

            endif;

            if ($msg == 'nv') {
                $this->addFlash('success', 'Création effectuée avec succès');
            } else {
                $this->addFlash('success', 'Mise à jour effectuée avec succès');
            }

            return $this->redirectToRoute('restaurateur_page_profil');
        }

        $media=$restaurant->getMedias();
        return $this->render('restaurateur/crud-page.html.twig', [
            'form' => $form->createView(),
            'medias'=>$media
        ]);
    }

    /**
     * @Route("/restaurant/delete/{restaurant}", name="restaurateur_page_delete_resto", methods={"POST"})
     */
    public function deleteResto(?Restaurant            $restaurant,
                             Request                $request,
                             EntityManagerInterface $manager): Response
    {
        try {
            if (!$restaurant) {
                $restaurant = new Restaurant();
            }

            //récupérer le token pour s'assurer de la source
            $submittedToken = $request->request->get('_token');

            if ($this->isCsrfTokenValid('delete' . $restaurant->getId(), $submittedToken)) {
                $manager->remove($restaurant);
                $manager->flush();

                //appel service pour supprimer les photos
                if(!empty($restaurant->getMedias()))
                    $this->restaurantService->deletePhotos($restaurant->getMedias());

                $this->addFlash('success', 'Opération effectuée avec succès');
            }
            return $this->redirectToRoute('restaurateur_page_profil');

        } catch (\Exception $e) {
            return $this->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * @Route("/photos/{user}/{restaurant}",name="restaurateur_page_get_photos")
     */
    public function getPhotos(?User $user, ?Restaurant $restaurant, MediaRepository $mediaRepository)
    {
        //s'assurer qu'il s'agit du user en cours et que le restaurant existe bel et bien
        if($restaurant && $user===$this->getUser())
            $photos = $mediaRepository->findBy(["restaurant"=>$restaurant]);
        else
            $photos="";

        return $this->render('restaurateur/restaurant-photos.html.twig',[
            "medias"=>$photos
        ]);
    }

    /**
     * @Route("/restaurant/photos/delete/{media}",name="restaurateur_page_delete_photo")
     */
    public function deletePhotos(?Media $media,
                                 Request $request,
                                 EntityManagerInterface $manager): Response
    {
         //récupérer le token pour s'assurer de la source
        $submittedToken = $request->request->get('_token');

        if ($media &&
            $this->isCsrfTokenValid('delete.photo'.$this->getUser()->getPassword(), $submittedToken)) {

            $manager->remove($media);
            $manager->flush();
            //appel service pour supprimer les photos
            $this->restaurantService->deletePhotos(new ArrayCollection([$media]));
        }

        return $this->redirectToRoute("restaurateur_page_edit_resto",[
            'user'=>$this->getUser()->getId(),
            'restaurant'=>$media->getRestaurant()->getId()
        ]);

    }
}
