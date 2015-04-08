<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 01/03/2015
 * Time: 15:55
 */

namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Handler\UserHandler;

class PostRecordListener
{

	private $userHandler;

	function __construct(UserHandler $userHandler)
	{
		$this->userHandler = $userHandler;
	}


	public function updateUser(BaseEvent $event)
	{
		$this->userHandler->postCreate($event->getUser());
	}
} 