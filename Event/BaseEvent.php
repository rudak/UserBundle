<?php
namespace Rudak\UserBundle\Event;

use Rudak\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\KernelEvent;


class BaseEvent extends KernelEvent
{

	protected $user;

	protected $options;

	public function __construct(User $user, array $options = array())
	{
		$this->user    = $user;
		$this->options = $options;
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
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param array $options
	 */
	public function setOptions($options)
	{
		$this->options = $options;
	}


} 