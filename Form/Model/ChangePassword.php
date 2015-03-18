<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 18/03/2015
 * Time: 18:06
 */

namespace Rudak\UserBundle\Form\Model;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
	/**
	 * @SecurityAssert\UserPassword(
	 *     message = "Mauvaise valeur pour votre mot de passe actuel"
	 * )
	 */
	protected $oldPassword;

	/**
	 * @Assert\Length(
	 *     min = 4,
	 *     minMessage = "Le mot de passe doit faire au moins 4 caractères"
	 * )
	 */
	protected $newPassword;

	/**
	 * @return mixed
	 */
	public function getOldPassword()
	{
		return $this->oldPassword;
	}

	/**
	 * @param mixed $oldPassword
	 */
	public function setOldPassword($oldPassword)
	{
		$this->oldPassword = $oldPassword;
	}

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


}