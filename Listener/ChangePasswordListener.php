<?php

namespace Rudak\UserBundle\Listener;


use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Event\ChangePasswordEvent;
use Rudak\UserBundle\Handler\UserHandler;

class ChangePasswordListener
{

	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}

	public function onSuccess(ChangePasswordEvent $event)
	{
		$this->userHandler->changePasswordSuccessfull($event->getUser());
	}

	public function onError(ChangePasswordEvent $event)
	{
		$this->userHandler->changePasswordError($event->getUser());
	}

	public function reinitSuccess(ChangePasswordEvent $event)
	{
		$this->userHandler->reinitPasswordSuccess($event->getUser());
	}

	public function lostRequest(BaseEvent $event)
	{
		$options = $event->getOptions();
		if (true === $options['reinit_autogen_pwd']) {
			$this->userHandler->autoGenPasswordRequest($event->getUser());
		} else {
			$this->userHandler->reinitPasswordRequest($event->getUser());
		}
	}

} 