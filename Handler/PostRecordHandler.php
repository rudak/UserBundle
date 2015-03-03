<?php
namespace Rudak\UserBundle\Handler;

use Rudak\UserBundle\Entity\User;

class PostRecordHandler
{
    private $mailer;
    private $user;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }


    public function sendMail()
    {
        if (null === $this->user) {
            return;
        }
        $message = \Swift_Message::newInstance()
            ->setSubject("Enregistrement utilisateur")
            ->setFrom('admin@votresite.com')
            ->setTo('postmaster@kadur-arnaud.fr')
            ->setBody("L'utilisateur surveillé '" . $this->user->getUsername() . "' s'est enregistré.");

        $this->mailer->send($message);
    }

    public function setHash()
    {
        if (null === $this->user) {
            return;
        }
        $this->user->setHash(md5(uniqid(rand(), TRUE)));
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
    public function setUser($user)
    {
        $this->user = $user;
    }


} 