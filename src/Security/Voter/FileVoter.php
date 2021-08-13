<?php

namespace App\Security\Voter;

use App\Entity\File;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class FileVoter extends Voter
{
    // these strings are just invented: you can use anything
    const UPLOAD = 'upload';
    const DOWNLOAD = 'download';
    const DELETE = 'delete';
    private $security;
 
    public function __construct(Security $security)
    {
         $this->security = $security;
    }
    protected function supports(string $attribute, $subject): bool
    {
         return in_array($attribute, [self::UPLOAD, self::DOWNLOAD, self::DELETE])
             && $subject instanceof \App\Entity\File;
    }
 
     protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
     {
         /** @var User $user */
         $user = $token->getUser();
         // if the user is anonymous, do not grant access
         if (!$user instanceof UserInterface) {
             return false;
         }
    
         /** @var File $file */
         $file = $subject;
 
         switch ($attribute) {
             case self::UPLOAD:
                 return $this->canUpload($file, $user);
             case self::DOWNLOAD:
                 return $this->canDownload($file, $user);
             case self::DELETE:
                 return $this->canDelete($file, $user);
         }
 
         throw new \LogicException('l\'action n\'est pas autorisée!');
     }
    private function canUpload(File $file, User $user): bool
    {
        // if they can download, they can upload
        if ($this->canDownload($file, $user)) {
            return true;
        }
        return false;
    }
    private function canDelete(File $file, User $user): bool
    {
        // if they can download, they can upload
        if ($this->canDownload($file, $user)) {
            return true;
        }
        return false;
    }
 
     private function canDownload(File $file, User $user): bool
     {
        
        if($file->getAuthor() == $user || $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }
        return false;
    }
}
