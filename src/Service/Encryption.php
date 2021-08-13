<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Encryption
{
    private $targetDirectory;
    private $slugger;
    private $security;

    public function __construct(SluggerInterface $slugger, Security $security)
    {
        $this->slugger = $slugger;
        $this->security = $security;
    }

    public function encrypt(UploadedFile $file) {
        /** @var User $user */
        $user = $this->security->getUser();

        $text = file_get_contents($file);
        $cipher = "AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encryptedText = openssl_encrypt($text, $cipher, $user->getPassword(), 0, $iv);
        $encryptedText = base64_encode($iv.$encryptedText);
      
        file_put_contents($file, $encryptedText);
    }



}