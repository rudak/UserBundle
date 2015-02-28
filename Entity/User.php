<?php

namespace Rudak\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acme\UserBundle\Entity\User
 *
 * @ORM\Table(name="rudak_users")
 * @ORM\Entity(repositoryClass="Rudak\UserBundle\Entity\UserRepository")
 */
class User implements UserInterface, \Serializable
{
	const ROLE_DEFAULT = 'ROLE_USER';
	const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

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
	 * @ORM\Column(type="array")
	 */
	protected $roles;

	/**
	 * @ORM\Column(name="lastLogin", type="datetime")
	 */
	private $lastLogin;

	public function __construct()
	{
		$this->isActive = true;
		$this->roles[ ] = static::ROLE_DEFAULT;
	}

	public function __toString()
	{
		return 'user' . $this->username;
	}

	/**
	 * @inheritDoc
	 */
	public function getUsername()
	{
		return $this->username;
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
	public function getPassword()
	{
		return $this->password;
	}

	public function setRoles(array $roles)
	{
		foreach ($roles as $role) {
			$this->addRole($role);
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function getRoles()
	{
		$roles = $this->roles;
		$roles[ ] = static::ROLE_DEFAULT;

		return array_unique($roles);
	}

	public function addRole($role)
	{
		$role = strtoupper($role);
		if ($role === static::ROLE_DEFAULT) {
			return $this;
		}
		if (!in_array($role, $this->roles, true)) {
			$this->roles[ ] = $role;
		}

		return $this;
	}

	public function hasRole($role)
	{
		return in_array(strtoupper($role), $this->getRoles(), true);
	}

	public function removeRole($role)
	{
		if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
			unset($this->roles[ $key ]);
			$this->roles = array_values($this->roles);
		}

		return $this;
	}

	public function isSuperAdmin()
	{
		return $this->hasRole(static::ROLE_SUPER_ADMIN);
	}

	public function setSuperAdmin($boolean)
	{
		if (true === $boolean) {
			$this->addRole(static::ROLE_SUPER_ADMIN);
		} else {
			$this->removeRole(static::ROLE_SUPER_ADMIN);
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
			) = unserialize($serialized);
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
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
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
	 * Get isActive
	 *
	 * @return boolean
	 */
	public function getIsActive()
	{
		return $this->isActive;
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


}
