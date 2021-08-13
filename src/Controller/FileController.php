<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use App\Form\FileType;
use App\Form\FileUploadType;
use App\Service\FileUpload;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FileController extends AbstractController
{
    /**
     * Route allowing to upload a file through a form and processing it
     * @Route("/file", name="file-upload", methods={"GET", "POST"})
     */
    public function upload(HttpFoundationRequest $request, EntityManagerInterface $em, FileUpload $fileUpload, Security $security): Response
    {
        
        /** @var User $user */
        $user = $security->getUser();
        $file = new File();
        $form = $this->createForm(FileUploadType::class, $file);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
        
           /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('path')->getData();
            $fileName = $fileUpload->upload($uploadedFile, $this->getParameter('files_directory'));  
           
            $file->setPath($fileName);
            $file->setAuthor($user);
            
    
            $em->flush();
            $this->addFlash(
                 'info',
                 'Le fichier a bien été chargé'
            );
             return $this->redirectToRoute('main');
        }
        return $this->render('file/form.html.twig', [
            'form'=> $form->createView()
        ]);
    }
        
}
