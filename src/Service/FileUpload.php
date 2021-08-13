<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FileUpload
{
    private $targetDirectory;
    private $slugger;
    private $security;

    public function __construct(SluggerInterface $slugger, Security $security)
    {
        $this->slugger = $slugger;
        $this->security = $security;
    }

    /** Method to upload a file
    * @param UploadedFile
    * @param string $targetDirectory
    * @return string or null
    */
    public function upload(UploadedFile $file, string $targetDirectory =null) : ?string 
    {
        /** @var User $user */
        $user = $this->security->getUser();
        //If a target directory is given, we move the file there, else, we use a default directory specified in .env file
        $this->targetDirectory=$targetDirectory ?? $_ENV['UPLOAD_FOLDER'];
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
        //TODO encrypt file
        $text = file_get_contents($file);

        $cipher = "AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        dump($iv);
        $encryptedText = openssl_encrypt($text, $cipher, $user->getPassword(), 0, $iv);
        $encryptedText = base64_encode($iv.$encryptedText);
      
        file_put_contents($file, $encryptedText);
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