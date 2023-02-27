<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CurrentUserController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function __invoke(): UserInterface
    {
        return $this->security->getUser();
    }
}