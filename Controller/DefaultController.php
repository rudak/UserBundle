<?php

namespace Rudak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
	public function indexAction()
	{
		return $this->render('RudakUserBundle:Default:index.html.twig');
	}

	public function profileAction()
	{
		return $this->render('RudakUserBundle:Default:profile.html.twig');
	}
}
