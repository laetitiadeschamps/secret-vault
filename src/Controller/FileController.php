<?php

namespace App\Controller;

use App\Entity\File;
use App\Form\FileType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    /**
     * Route allowing to upload a file through a form and processing it
     * @Route("/file", name="file-upload", methods={"GET", "POST"})
     */
    public function upload(Request $request, EntityManagerInterface $em): Response
    {
        $file = new File();
        $form = $this->createForm(FileType::class, $file);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
         
            // /** @var UploadedFile $pictureFile */
            // $picture = $form->get('picture')->getData();
            // $avatar = $form->get('avatar')->getData();
            
            // if ($picture) {         
            //     $pictureFileName = $fileUpload->upload($picture, $this->getParameter('images_directory'));  
            //     $user->setPicture($pictureFileName);
            // }
            // else if($avatar){   
            //     $user->setPicture($avatar);
            // }
            // $em->flush();
            // $this->addFlash(
            //     'info',
            //     'Le profil a été mis à jour'
            // );
            // return $this->redirectToRoute('user-profile');
        }
        return $this->render('file/form.html.twig', [
            'form'=> $form->createView()
        ]);
    }
        
}
