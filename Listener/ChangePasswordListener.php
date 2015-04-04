<?php

namespace Rudak\UserBundle\Listener;


use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Handler\UserHandler;

class ChangePasswordListener
{

	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}

	public function onSuccess(BaseEvent $event)
	{
		$this->userHandler->changePasswordSuccessfull($event->getUser());
	}

	public function onError(BaseEvent $event)
	{
		$this->userHandler->changePasswordError($event->getUser());
	}

	public function reinitSuccess(BaseEvent $event)
	{
		$this->userHandler->reinitPasswordSuccess($event->getUser());
	}

	public function lostRequest(BaseEvent $event)
	{
		$this->userHandler->reinitPasswordRequest($event->getUser());
	}

} 