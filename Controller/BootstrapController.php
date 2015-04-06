<?php

namespace Rudak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class BootstrapController extends Controller
{
	public function getTopBarAction()
	{
		$content = $this->renderView('RudakUserBundle:Bootstrap:topBar.html.twig');

		return new Response($content);
	}
}
