<?php

namespace Rudak\UserBundle\Controller;

use Facebook\GraphUser;
use Rudak\UserBundle\Facebook\FacebookConnect;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;

class FacebookController extends Controller
{
	public function loginAction()
	{
		$app_id     = '************';
		$app_secret = '************';
		$fb_connect = new FacebookConnect($app_id, $app_secret);
		$result     = $fb_connect->connect($this->getAbsoluteUrl());

		if (is_string($result)) {
			return $this->redirect($result);
		} elseif ($result instanceof GraphUser) {
			$this->addFlash('notice', 'Vous etes déja connecté');

			return $this->redirectToRoute('homepage');
		} else {
			$this->addFlash('notice', 'Erreur de connection Facebook, veuillez recommencer svp.');

			return $this->redirectToRoute('homepage');
		}
	}

	public function authenticationOkAction()
	{
		$this->addFlash('notice', 'Connexion Facebook Ok !');

		return $this->redirectToRoute('rudakUser_fb_authentication_ok');
	}

	private function getAbsoluteUrl()
	{
		return $this->generateUrl('homepage', array(), true);
	}
}
