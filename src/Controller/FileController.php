<?php

namespace App\Controller;

use App\Entity\File;
use App\Entity\User;
use App\Form\FileType;
use App\Form\FileUploadType;
use App\Service\Encryption;
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
     * @param HttpFoundationRequest $request
     * @param EntityManagerInterface $em
     * @param FileUpload $fileUpload
     * @param Security $security
     * @return Response
     */
    public function upload(HttpFoundationRequest $request, EntityManagerInterface $em, FileUpload $fileUpload, Security $security): Response
    { 
        /** @var User $user */
        $user = $security->getUser();
        $file = new File();
        $form = $this->createForm(FileUploadType::class, $file);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
        // When the form is valid and submitted, we process the file : we upload it through the service, that also encrypts it, and we update our database
           /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('path')->getData();
            $fileName = $fileUpload->upload($uploadedFile, $this->getParameter('files_directory'));  
           
            $file->setPath($fileName);
            $file->setAuthor($user);
            
            $em->persist($file);
            $em->flush();
            //adding a flash message for UX
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
      * @param File $file
      * @param Encryption $encryption
      * @return Response
      */
    public function download(File $file, Encryption $encryption): Response
    {
        //Allowing access only if we are the one who uploaded the file or an admin (not implemented yet)
        $this->denyAccessUnlessGranted('download', $file);
        // From file path, we retrieve the proper encrypted file, and use the appropriate service to decrypt it
        $filePath =  $this->getParameter('files_directory') . '/' . $file->getPath();
        $ext = pathinfo($filePath)['extension'];
        $temp = $encryption->decrypt($filePath);
        // We launch the file's download, using the name given by the user upon uploading and the proper extension
        return $this->file($temp, $file->getAlias(). '.' . $ext);
        
       
    }
    /** 
      * Route allowing to delete a file
      * @Route("/{id}/delete", name="delete")
      * @param File $file
      * @return Response
      */
      public function delete(File $file, EntityManagerInterface $em, Filesystem $filesystem): Response
      {
        //Allowing access only if we are the one who uploaded the file or an admin (not implememnted yet)
        $this->denyAccessUnlessGranted('delete', $file);
        // We get the file path and remove it from the server
        $filePath =  $this->getParameter('files_directory') . '/' . $file->getPath();
        $filesystem->remove($filePath);
        // We remove the file entry on the database
        $em->remove($file);
        $em->flush();
         //adding a flash message for UX
         $this->addFlash(
            'info',
            'Le fichier a bien été supprimé'
       );

        return $this->redirectToRoute('main');
          
         
      }
        
}
