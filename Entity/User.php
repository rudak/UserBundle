<?php

namespace Rudak\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Acme\UserBundle\Entity\User
 *
 * @ORM\Table(name="rudak_users")
 * @ORM\Entity(repositoryClass="Rudak\UserBundle\Entity\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable, EquatableInterface
{
	const ROLE_DEFAULT     = 'ROLE_USER';
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
	 * @ORM\Column(type="string", length=80, unique=true)
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=78, nullable=true)
	 */
	private $emailTmp;

	/**
	 * @ORM\Column(name="is_active", type="boolean", nullable=true)
	 */
	private $isActive;
	/**
	 * @ORM\Column(name="blocked", type="boolean", nullable=true)
	 */
	private $blocked;
	/**
	 * @ORM\Column(type="array")
	 */
	private $roles;
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
	private $securityHash;
	/**
	 * @ORM\Column(name="SecurityHashExpireAt", type="datetime", nullable=true)
	 */
	private $SecurityHashExpireAt;

	private $plainPassword;

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
		$this->plainPassword = null;
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
	 * @return mixed
	 */
	public function getEmailTmp()
	{
		return $this->emailTmp;
	}

	/**
	 * @param mixed $emailTmp
	 */
	public function setEmailTmp($emailTmp)
	{
		$this->emailTmp = $emailTmp;
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
	public function getBlocked()
	{
		return $this->blocked;
	}

	/**
	 * @param mixed $blocked
	 */
	public function setBlocked($blocked)
	{
		$this->blocked = $blocked;
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
	public function getSecurityHash()
	{
		return $this->securityHash;
	}

	/**
	 * @param mixed $securityHash
	 */
	public function setSecurityHash($securityHash)
	{
		$this->securityHash = $securityHash;
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
	public function getSecurityHashExpireAt()
	{
		return $this->SecurityHashExpireAt;
	}

	/**
	 * @param mixed $SecurityHashExpireAt
	 */
	public function setSecurityHashExpireAt($SecurityHashExpireAt)
	{
		$this->SecurityHashExpireAt = $SecurityHashExpireAt;
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
	 * @return mixed
	 */
	public function getPlainPassword()
	{
		return $this->plainPassword;
	}

	/**
	 * @param mixed $plainPassword
	 */
	public function setPlainPassword($plainPassword)
	{
		$this->plainPassword = $plainPassword;
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

	/**
	 * @inheritDoc
	 */
	public function getSalt()
	{
		return null;
	}

	/**
	 * Checks whether the user's account has expired.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw an AccountExpiredException and prevent login.
	 *
	 * @return bool true if the user's account is non expired, false otherwise
	 *
	 * @see AccountExpiredException
	 */
	public function isAccountNonExpired()
	{
		return true;
	}

	/**
	 * Checks whether the user is locked.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw a LockedException and prevent login.
	 *
	 * @return bool true if the user is not locked, false otherwise
	 *
	 * @see LockedException
	 */
	public function isAccountNonLocked()
	{
		return (true === $this->blocked) ? false : true;

	}

	/**
	 * Checks whether the user's credentials (password) has expired.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw a CredentialsExpiredException and prevent login.
	 *
	 * @return bool true if the user's credentials are non expired, false otherwise
	 *
	 * @see CredentialsExpiredException
	 */
	public function isCredentialsNonExpired()
	{
		return true;
	}

	/**
	 * Checks whether the user is enabled.
	 *
	 * Internally, if this method returns false, the authentication system
	 * will throw a DisabledException and prevent login.
	 *
	 * @return bool true if the user is enabled, false otherwise
	 *
	 * @see DisabledException
	 */
	public function isEnabled()
	{
		return (true === $this->blocked) ? false : true;
	}


}
