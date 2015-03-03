<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 01/03/2015
 * Time: 15:55
 */

namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Handler\PostRecordHandler;
use Rudak\UserBundle\Event\RecordEvent;

class PostRecordListener
{

	public function updateUser(RecordEvent $event)
	{
		$user = $event->getUser();

		$PostRecordHandler = new Rudak\UserBundle\Handler\PostRecordHandler("gg", $user);
		$PostRecordHandler->setHash();
		$PostRecordHandler->sendMail();
		$event->setUser($PostRecordHandler->getUser());
		//return $user;
	}
} 