<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();
        $files = $user->getFiles();
        
        return $this->render('main/index.html.twig', [
            'files'=>$files,
        ]);
    }
}
