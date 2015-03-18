<?php

namespace Rudak\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acme\UserBundle\Entity\User
 *
 * @ORM\Table(name="rudak_users")
 * @ORM\Entity(repositoryClass="Rudak\UserBundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable, EquatableInterface
{
	const ROLE_DEFAULT     = 'ROLE_USER';
	const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
	/**
	 * @ORM\Column(type="array")
	 */
	private $roles;
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	/**
	 * @ORM\Column(type="string", length=25, unique=true)
	 */
	private $username;
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $password;
	/**
	 * @ORM\Column(type="string", length=60, unique=true)
	 */
	private $email;
	/**
	 * @ORM\Column(name="is_active", type="boolean")
	 */
	private $isActive;
	/**
	 * @ORM\Column(name="lastLogin", type="datetime", nullable=true)
	 */
	private $lastLogin;
	/**
	 * @ORM\Column(name="email_validation", type="datetime", nullable=true)
	 */
	private $emailValidation;
	/**
	 * @ORM\Column(type="string", length=70, nullable=true)
	 */
	private $recoveryHash;
	/**
	 * @ORM\Column(name="recovery_date", type="datetime", nullable=true)
	 */
	private $recoveryExpireAt;


	public function __construct()
	{
		$this->isActive = false;
		$this->roles[]  = static::ROLE_DEFAULT;
	}

	public function __toString()
	{
		return $this->username;
	}

	public function isSuperAdmin()
	{
		return $this->hasRole(static::ROLE_SUPER_ADMIN);
	}

	public function hasRole($role)
	{
		return in_array(strtoupper($role), $this->getRoles(), true);
	}

	/**
	 * @inheritDoc
	 */
	public function getRoles()
	{
		$roles   = $this->roles;
		$roles[] = static::ROLE_DEFAULT;

		return array_unique($roles);
	}

	public function setRoles(array $roles)
	{
		foreach ($roles as $role) {
			$this->addRole($role);
		}

		return $this;
	}

	public function addRole($role)
	{
		$role = strtoupper($role);
		if ($role === static::ROLE_DEFAULT) {
			return $this;
		}
		if (!in_array($role, $this->roles, true)) {
			$this->roles[] = $role;
		}

		return $this;
	}

	public function setSuperAdmin($boolean)
	{
		if (true === $boolean) {
			$this->addRole(static::ROLE_SUPER_ADMIN);
		}
		else {
			$this->removeRole(static::ROLE_SUPER_ADMIN);
		}

		return $this;
	}

	public function removeRole($role)
	{
		if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
			unset($this->roles[$key]);
			$this->roles = array_values($this->roles);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function eraseCredentials()
	{
	}

	/**
	 * @see \Serializable::serialize()
	 */
	public function serialize()
	{
		return serialize([
			$this->id,
			$this->username,
			$this->password,
		]);
	}

	/**
	 * @see \Serializable::unserialize()
	 */
	public function unserialize($serialized)
	{
		list (
			$this->id,
			$this->username,
			$this->password,
			)
			= unserialize($serialized);
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return User
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Get isActive
	 *
	 * @return boolean
	 */
	public function getIsActive()
	{
		return $this->isActive;
	}

	/**
	 * Set isActive
	 *
	 * @param boolean $isActive
	 * @return User
	 */
	public function setIsActive($isActive)
	{
		$this->isActive = $isActive;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastLogin()
	{
		return $this->lastLogin;
	}

	/**
	 * @param mixed $lastLogin
	 */
	public function setLastLogin($lastLogin)
	{
		$this->lastLogin = $lastLogin;
	}

	/**
	 * @return mixed
	 */
	public function getRecoveryHash()
	{
		return $this->recoveryHash;
	}

	/**
	 * @param mixed $recoveryHash
	 */
	public function setRecoveryHash($recoveryHash)
	{
		$this->recoveryHash = $recoveryHash;
	}

	/**
	 * @return mixed
	 */
	public function getEmailValidation()
	{
		return $this->emailValidation;
	}

	/**
	 * @param mixed $emailValidation
	 */
	public function setEmailValidation($emailValidation)
	{
		$this->emailValidation = $emailValidation;
	}

	/**
	 * @return mixed
	 */
	public function getRecoveryExpireAt()
	{
		return $this->recoveryExpireAt;
	}

	/**
	 * @param mixed $recoveryExpireAt
	 */
	public function setRecoveryExpireAt($recoveryExpireAt)
	{
		$this->recoveryExpireAt = $recoveryExpireAt;
	}

	public function isEqualTo(UserInterface $user)
	{
		if (!$user instanceof User) {
			return false;
		}

		if ($this->password !== $user->getPassword()) {
			return false;
		}

		if ($this->username !== $user->getUsername()) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * Set password
	 *
	 * @param string $password
	 * @return User
	 */
	public function setPassword($password)
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getSalt()
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set username
	 *
	 * @param string $username
	 * @return User
	 */
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

}
