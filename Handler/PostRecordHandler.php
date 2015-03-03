<?php
namespace Rudak\UserBundle\Handler;

use Rudak\UserBundle\Entity\User;

class PostRecordHandler
{
	private $mailer;
	private $user;

	public function __construct($mailer = null, User $user)
	{
		$this->mailer = $mailer;
		$this->user = $user;
	}


	public function sendMail()
	{
		return true;
	}

	public function setHash()
	{
		$this->user->setHash(md5(uniqid(rand(), TRUE)));
	}

	/**
	 * @return mixed
	 */
	public function getMailer()
	{
		return $this->mailer;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}


} 