<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Facebook\FacebookConnect;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IncludeController extends Controller
{
	public function userAreaAction()
	{
		$facebookConnect = new FacebookConnect('**********', '**********');
		$connect         = $facebookConnect->connect($this->generateUrl('homepage', array(), true));

		$content = $this->renderView('RudakUserBundle:Include:user-area.html.twig', array(
			'facebook' => !is_string($connect)
		));

		return new Response($content);
	}
}
