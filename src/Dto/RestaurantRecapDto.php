<?php

namespace App\Dto;

class RestaurantRecapDto
{
    private $resto;
    private $countReviews;
    private $avgNotes;

    /**
     * @return mixed
     */
    public function getAvgNotes()
    {
        return $this->avgNotes;
    }

    /**
     * @param mixed $avgNotes
     */
    public function setAvgNotes($avgNotes): void
    {
        $this->avgNotes = $avgNotes;
    }


    /**
     * @return mixed
     */
    public function getResto()
    {
        return $this->resto;
    }

    /**
     * @param mixed $resto
     */
    public function setResto($resto): void
    {
        $this->resto = $resto;
    }

    /**
     * @return mixed
     */
    public function getCountReviews()
    {
        return $this->countReviews;
    }

    /**
     * @param mixed $countReviews
     */
    public function setCountReviews($countReviews): void
    {
        $this->countReviews = $countReviews;
    }
}