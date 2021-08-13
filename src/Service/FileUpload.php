<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUpload
{
    private $targetDirectory;
    private $slugger;
    private $security;
    private $encryption;

    public function __construct(SluggerInterface $slugger, Security $security, Encryption $encryption)
    {
        $this->slugger = $slugger;
        $this->security = $security;
        $this->encryption = $encryption;
    }
    /** Method to upload a file
    * @param UploadedFile
    * @param string $targetDirectory
    * @return string or null
    */
    public function upload(UploadedFile $file, string $targetDirectory =null) : ?string 
    {
        //If a target directory is given, we move the file there, else, we use a default directory specified in .env file
        $this->targetDirectory=$targetDirectory ?? $_ENV['UPLOAD_FOLDER'];
        // We use the sluggerInterface to give a unique and secure name to our file
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        // We update its content with an encrypted version
        $this->encryption->encrypt($file);
        
        try {
            //We put the file in the proper directory
            $file->move($this->getTargetDirectory(), $fileName);
            return $fileName;
        } catch (FileException $e) {
            return $e;
        }
        return null;
        
    }

    public function getTargetDirectory() : ?string
    {
        return $this->targetDirectory;
    }
}