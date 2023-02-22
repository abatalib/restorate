<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $msg = '<div style="padding: 10px; background:#CE4444">
                    <h2 style="color:white">Vous n\'êtes pas autorisé</h2>
                </div>

                <a class="btn btn-primary" href="/login">Se connecter</a> | <a class="btn btn-primary" href="/">Liste des restaurants</a>';

        return new Response($msg, 403);
    }
}