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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
* @Route("/file", name="file-", requirements={"id":"\d+"})
*/
class FileController extends AbstractController
{
    /**
     * Route allowing to upload a file through a form and processing it
     * @Route("/", name="upload", methods={"GET", "POST"})
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
            
            $em->persist($file);
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
    /**
     * Route allowing to decrypt and download a file
     * @Route("/{id}", name="download", methods={"GET", "POST"})
     */
    public function download(File $file, HttpFoundationRequest $request, EntityManagerInterface $em, FileUpload $fileUpload, Security $security, Filesystem $filesystem): Response
    {
        
        /** @var User $user */
        $user = $security->getUser();

        $this->denyAccessUnlessGranted('download', $file);

        $filePath =  $this->getParameter('files_directory') . '/' . $file->getPath();
        $encrytedText = file_get_contents($filePath); 
       $encrytedText = base64_decode($encrytedText);
        $cipher="AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        
        $iv = substr($encrytedText,0,$ivlen);
        $decryptedText = openssl_decrypt(substr($encrytedText, $ivlen), $cipher, $user->getPassword(), 0, $iv);
    
        $temp = tempnam(sys_get_temp_dir(), 'myAppNamespace');
      
        file_put_contents($temp, $decryptedText);
        $ext = pathinfo($filePath)['extension'];
        
        return $this->file($temp, $file->getAlias(). '.' . $ext);
      


       // TODO download

        return $this->redirectToRoute('main');
        
       
    }
        
}
