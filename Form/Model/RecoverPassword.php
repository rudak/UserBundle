<?php
namespace Rudak\UserBundle\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class RecoverPassword
{

	/**
	 * @Assert\Length(
	 *     min = 4,
	 *     minMessage = "Password should by at least 6 chars long"
	 * )
	 */
	protected $newPassword;

	private $hash;

	/**
	 * @return mixed
	 */
	public function getNewPassword()
	{
		return $this->newPassword;
	}

	/**
	 * @param mixed $newPassword
	 */
	public function setNewPassword($newPassword)
	{
		$this->newPassword = $newPassword;
	}

	/**
	 * @return mixed
	 */
	public function getHash()
	{
		return $this->hash;
	}

	/**
	 * @param mixed $hash
	 */
	public function setHash($hash)
	{
		$this->hash = $hash;
	}


}