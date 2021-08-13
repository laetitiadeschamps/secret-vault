<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileUpload
{
    private $targetDirectory;
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
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
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        //TODO encrypt file
        try {
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