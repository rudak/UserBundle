<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 21/03/2015
 * Time: 23:35
 */

namespace Rudak\UserBundle\Listener;


use Rudak\UserBundle\Event\RecordEvent;
use Rudak\UserBundle\Handler\UserHandler;

class ChangeEmailListener
{
	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}

	public function emailChangeRequest(RecordEvent $event)
	{
		$this->userHandler->changeEmailRequest($event->getUser());
	}

	public function emailChangeSuccess(RecordEvent $event)
	{
		$this->userHandler->changeEmailSuccess($event->getUser());
	}
}