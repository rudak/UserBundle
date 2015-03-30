<?php

namespace Rudak\UserBundle\Facebook;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Symfony\Component\HttpFoundation\Session\Session;

class FacebookConnect
{
	const FB_TOKEN_NAME = 'fb_token';
	private $appId;
	private $appSecret;
	private $session;

	/**
	 * @param $appId     Facebook Application ID
	 * @param $appSecret Facebook Application secret
	 */
	function __construct($appId, $appSecret)
	{
		$this->appId     = $appId;
		$this->appSecret = $appSecret;
	}

	/**
	 * @param $redirect_url
	 * @return string|Facebook\GraphUser Login URL or GraphUser
	 */
	function connect($redirect_url)
	{
		FacebookSession::setDefaultApplication($this->appId, $this->appSecret);
		$helper = new FacebookRedirectLoginHelper($redirect_url);
		if ($this->getSession()->has(static::FB_TOKEN_NAME)) {
			$session = new FacebookSession($this->getSession()->get(static::FB_TOKEN_NAME));
		} else {
			$session = $helper->getSessionFromRedirect();
		}
		if ($session) {
			try {
				$this->getSession()->set(static::FB_TOKEN_NAME, $session->getToken());
				$request = new FacebookRequest($session, 'GET', '/me');
				$profile = $request->execute()->getGraphObject('Facebook\GraphUser');
				if ($profile->getEmail() === null) {
					throw new \Exception('L\'email n\'est pas disponible');
				}

				return $profile;
			} catch (\Exception $e) {
				$this->getSession()->remove(static::FB_TOKEN_NAME);

				return $helper->getReRequestUrl(['email']);
			}
		} else {
			return $helper->getReRequestUrl(['email']);
		}
	}

	private function getSession()
	{
		if (null === $this->session) {
			$this->session = new Session();
		}

		return $this->session;
	}

}