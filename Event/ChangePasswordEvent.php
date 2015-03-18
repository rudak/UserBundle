<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 18/03/2015
 * Time: 19:24
 */

namespace Rudak\UserBundle\Event;


use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Form\Model\ChangePassword;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class ChangePasswordEvent extends KernelEvent
{
	protected $user;
	protected $changePassword;

	public function __construct(User $user, ChangePassword $changePassword)
	{
		$this->user           = $user;
		$this->changePassword = $changePassword;
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

	/**
	 * @return ChangePassword
	 */
	public function getChangePassword()
	{
		return $this->changePassword;
	}

	/**
	 * @param ChangePassword $changePassword
	 */
	public function setChangePassword($changePassword)
	{
		$this->changePassword = $changePassword;
	}


}