<?php
namespace Rudak\UserBundle\Handler;


use Rudak\UserBundle\Entity\User;

class EmailValidationHandler
{

    private $user;

    public function emailValidationSuccess()
    {
        // on vire le hash qui sert plus a rien
        $this->user->setHash(null);
        // on met la date a jour
        $this->user->setEmailValidation(new \Datetime('NOW'));
        // on defini l'utilisateur comme actif
        $this->user->setIsActive(true);
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}