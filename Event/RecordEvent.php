<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 01/03/2015
 * Time: 15:14
 */

namespace Rudak\UserBundle\Event;

use Rudak\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\KernelEvent;


class RecordEvent extends KernelEvent
{

	protected $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

} 