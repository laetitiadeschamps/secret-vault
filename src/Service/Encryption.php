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
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Method allowing to encrypt files and update content
     *
     * @param UploadedFile $file
     * @return void
     */
    public function encrypt(UploadedFile $file) :void
    { 
        /** @var User $user */
        $user = $this->security->getUser();

        // We retrieve the file content, and encrypt it to a base 64, using the user's password along with the IV at the beginning of the file, and update the content
        $text = file_get_contents($file);
        $cipher = "AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encryptedText = openssl_encrypt($text, $cipher, $user->getPassword(), 0, $iv);
        $encryptedText = base64_encode($iv.$encryptedText);
        file_put_contents($file, $encryptedText);
    }

    /**
     * Method to decrypt a file before downnloading it 
     *
     * @param string $filePath
     * @return string|false $temp
     */
    public function decrypt(string $filePath) : ?string
    { 
        /** @var User $user */
        $user = $this->security->getUser();

        // We retrieve the encrypted content and decode it using the IV embedded in the crypted text, and the user password as salt.
        $encrytedText = file_get_contents($filePath); 
        $encrytedText = base64_decode($encrytedText);
        $cipher="AES-128-CBC";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($encrytedText,0,$ivlen);
        $decryptedText = openssl_decrypt(substr($encrytedText, $ivlen), $cipher, $user->getPassword(), 0, $iv);
    
        // Then we create a temp file and insert the decrypted content 
        $temp = tempnam(sys_get_temp_dir(), 'App');
        file_put_contents($temp, $decryptedText);
        return $temp;
    }



}