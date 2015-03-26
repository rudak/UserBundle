<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 07/03/2015
 * Time: 03:48
 */

namespace Rudak\UserBundle\Listener;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Router;

class AccessDeniedListener
{
	protected $_session;
	protected $_router;
	protected $_request;

	public function __construct(Session $session, Router $router, Request $request)
	{
		$this->_session = $session;
		$this->_router  = $router;
		$this->_request = $request;
	}

	public function onAccessDeniedException(GetResponseForExceptionEvent $event)
	{

		if ($event->getException()->getMessage() == 'Access Denied') {
			$this->_session->getFlashBag()->add(
				'notice',
				'Votre type de compte ne vous permet pas d\'accéder à cette page.'
			);
			/*
			if ($this->_request->headers->get('referer')) {
				$route = $this->_request->headers->get('referer');
			} else {
				$route = $this->_router->generate('homepage');
			}
			*/

			$event->setResponse(new RedirectResponse($this->_router->generate('homepage')));
		}
	}
}