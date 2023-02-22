<?php

namespace App\Service;

use App\Dto\RestaurantRecapDto;
use App\Repository\RestaurantRepository;
use App\Repository\ReviewRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RestaurantService
{

    /**
     * @var string
     */
    private $photo_uploaded_directory;
    /**
     * @var ReviewRepository
     */
    private $reviewRepository;
    private RestaurantRepository $restaurantRepository;

    public function __construct(string $photo_uploaded_directory, ReviewRepository $reviewRepository, RestaurantRepository $restaurantRepository)
    {
        $this->photo_uploaded_directory = $photo_uploaded_directory;
        $this->reviewRepository = $reviewRepository;
        $this->restaurantRepository = $restaurantRepository;
    }

//    vérifier l'extension des photos
    public function verifyFile($file, $userId): string
    {
        for($i=0;$i<sizeof($file);$i++){

            $extension = $file[$i]->getClientOriginalExtension();

            if ($extension != 'jpg' && $extension != 'gif'
                && $extension != 'jpeg' && $extension != 'png'):
                return 'EXT_NOT_ADMITTED';
            endif;
        }
        //si la vérif de l'extension est ok
        //on vérifi si le dossier des photos pour l'user encours
        //est là sinon on le crée
        return $this->createFolderIfNotExist($userId);
    }

    public function transferFile($file,$iDs): array
    {
        try {
            //tableau comportera la liste des url des photos
            //pour les ajouter dans la bd
            $tab=[];
            for($i=0;$i<sizeof($file);$i++) {
                $to = $this->photo_uploaded_directory . DIRECTORY_SEPARATOR . $iDs['userId'];
                $filename = $iDs['userId'] . "_" .
                    $iDs['restoId'] . "_" .
                    date('d_m_y_h_i_s') . "_" .
                    $i . "." .
                    $file[$i]->getClientOriginalExtension();
                $file[$i]->move($to, $filename);
                sleep(1);
                $tab[]=$iDs['userId'] . DIRECTORY_SEPARATOR . $filename;
            }
            return $tab;
        }catch(FileException $e){
            return ["err",$e->getMessage()];
        }
//        return $iDs['userId'] . DIRECTORY_SEPARATOR . $filename;
    }

    private function createFolderIfNotExist($userID): string
    {
        $pathToFolder = $this->photo_uploaded_directory;
        $path = $pathToFolder. DIRECTORY_SEPARATOR . $userID;
        //si le dossier existe on retourne true
        //sinon on procéde à sa création, la création mkdir retourne true (success) ou false (failure)
        if(is_dir($path)) {
            return "OK";
        }else {
            mkdir($pathToFolder . DIRECTORY_SEPARATOR . $userID)
                ?
            $result = "OK"
                :
            $result = "ERROR_TO_CREATE_FOLDER";

            return $result;
        }
    }

    public function getRestaurants(?array $restaurants): array
    {
        $newResult=[];

        //alimenter le DTO pour se disposer d'un resto avec la moyenne de note et nbre d'avis
        foreach ($restaurants as $rs):
            $newResult[]=$this->getNotesAndMsg($rs);
        endforeach;

        return $newResult;
    }

    public function getNotesAndMsg($rs): RestaurantRecapDto
    {
        $id=is_array($rs) ? $rs['id'] : $rs->getId();

        //liste des reviews pour en sortir le nombre de messages
        $countReviews = count($this->reviewRepository->findBy(['restaurant'=>$id]));
        //la moyenne des notes
        $avg = $this->reviewRepository->moyenneNotes($id);

        $dto = new RestaurantRecapDto();
        $dto->setResto($rs);
        $dto->setCountReviews($countReviews);
        $dto->setAvgNotes($avg);
        return $dto;
    }

    public function deletePhotos(Collection $medias): void
    {
        $filesystem = new Filesystem();

        try {
            foreach ($medias as $m):
                $filesystem->remove($this->photo_uploaded_directory . DIRECTORY_SEPARATOR . $m->getUrl());
            endforeach;
        } catch (IOExceptionInterface $exception) {
        }
    }
}