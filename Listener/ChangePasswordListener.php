<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 01/03/2015
 * Time: 15:55
 */

namespace Rudak\UserBundle\Listener;


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
		$user = $event->getUser();
		$this->userHandler->changeSuccessfull($user, $event->getChangePassword());
	}

} 