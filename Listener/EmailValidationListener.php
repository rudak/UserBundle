<?php
namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Event\EmailValidationEvent;
use Rudak\UserBundle\Handler\EmailValidationHandler;

class EmailValidationListener
{

    private $emailValidationHandler;

    function __construct(EmailValidationHandler $emailValidationHandler)
    {
        $this->emailValidationHandler = $emailValidationHandler;
    }

    public function validationSuccess(EmailValidationEvent $event)
    {
        $this->emailValidationHandler->setUser($event->getUser());
        $this->emailValidationHandler->emailValidationSuccess();
        $event->setUser($this->emailValidationHandler->getUser());
    }
} 