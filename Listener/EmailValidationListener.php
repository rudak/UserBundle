<?php
namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Event\EmailValidationEvent;
use Rudak\UserBundle\Handler\UserHandler;

class EmailValidationListener
{

	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}

	public function validationSuccess(EmailValidationEvent $event)
	{
		$this->userHandler->emailValidationSuccess($event->getUser());
	}
} 