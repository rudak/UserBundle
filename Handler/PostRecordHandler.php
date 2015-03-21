<?php
namespace Rudak\UserBundle\Handler;

use Rudak\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Templating\EngineInterface;

class PostRecordHandler
{
    private $mailer;
    private $user;
    private $templating;
    private $router;

    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, Router $router)
    {
        $this->mailer     = $mailer;
        $this->templating = $templating;
        $this->router     = $router;
    }

    public function justRecorded()
    {
        if (null === $this->user) {
            return;
        }
        // Création du hash de vérification
        $this->user->setRecoveryHash(sha1(uniqid(rand(), TRUE)));
        // creation du mail
        $message = \Swift_Message::newInstance()
            ->setSubject("Validation de votre adresse email")
            ->setFrom('admin@votresite.com')
            ->setContentType("text/html")
            ->setTo($this->user->getEmail())
            ->setBody($this->getEmailHtml());
        // envoi
        $this->mailer->send($message);
    }

    private function getEmailHtml()
    {
        return $this->templating->render('RudakUserBundle:Email:post-record.html.twig', array(
            'user' => $this->user,
            'site' => $this->router->generate('record_validing_email', array(
                'hash' => $this->user->getRecoveryHash(),
            ), true)
        ));
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