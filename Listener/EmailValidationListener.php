<?php
namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Handler\UserHandler;

class EmailValidationListener
{

	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}

	public function validationSuccess(BaseEvent $event)
	{
		$this->userHandler->emailValidationSuccess($event->getUser());
	}
} 