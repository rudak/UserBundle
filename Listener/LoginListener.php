<?php
namespace Rudak\UserBundle\Listener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\SecurityContext;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;

class LoginListener
{

	private $securityContext;

	private $em;

	function __construct(SecurityContext $securityContext, Doctrine $doctrine)
	{
		$this->securityContext = $securityContext;
		$this->em = $doctrine->getManager();
	}

	/**
	 * Do the magic.
	 *
	 * @param InteractiveLoginEvent $event
	 */
	public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
	{
		if ($this->securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
			// user has just logged in
		}

		if ($this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			// user has logged in using remember_me cookie
		}

		// do some other magic here
		$user = $event->getAuthenticationToken()->getUser();

		$user->setLastLogin(new \Datetime());
		$this->em->persist($user);
		$this->em->flush();
	}
} 