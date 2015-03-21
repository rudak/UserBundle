<?php

namespace Rudak\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Rudak\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * {@inheritDoc}
	 */
	public function setContainer(ContainerInterface $container = null)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$barre = '--------------------';
		echo $barre . "\nCREATION USERS\n" . $barre . "\n";

		$user = [];
		foreach ($this->getUsersToLoad() as $key => $userInfos) {
			$user[$key] = new User();
			$user[$key]->setUsername($userInfos['username']);
			$user[$key]->setEmail($userInfos['email']);
			$user[$key]->setRoles($userInfos['roles']);
			$user[$key]->setEmailValidation(new \Datetime('NOW'));
			$user[$key]->setIsActive(true);

			$encoder = $this->container
				->get('security.encoder_factory')
				->getEncoder($user[$key]);
			$user[$key]->setPassword($encoder->encodePassword($userInfos['password'], $user[$key]->getSalt()));
			$manager->persist($user[$key]);

			echo 'Creation user : ' . $userInfos['username'] . " " . $userInfos['password'] . "\n";
		}

		$manager->flush();
		echo "\nTERMINE\n" . $barre . "\n";
	}

	private function getUsersToLoad()
	{
		return [
			[
				'username' => 'joe',
				'password' => '0000',
				'email' => 'joe@website.com',
				'roles' => ['ROLE_USER']
			],
			[
				'username' => 'gustave',
				'password' => '0000',
				'email' => 'michel@website.com',
				'roles' => ['ROLE_USER', 'ROLE_MODO']
			],
			[
				'username' => 'admin',
				'password' => 'admin',
				'email' => 'admin@admin.com',
				'roles' => ['ROLE_USER', 'ROLE_ADMIN']
			],
			[
				'username' => 'charlotte184',
				'password' => '0000',
				'email' => 'charlotte@wanadoo.fr',
				'roles' => ['ROLE_USER', 'ROLE_ADMIN']
			]
		];
	}
}