<?php

namespace Rudak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IncludeController extends Controller
{
	public function userAreaAction()
	{
		$content = $this->renderView('RudakUserBundle:Include:user-area.html.twig');
		return new Response($content);
	}
}
