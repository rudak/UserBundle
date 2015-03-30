<?php

namespace Rudak\UserBundle\Facebook;

use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
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

		try {
			if ($this->getSession()->has(static::FB_TOKEN_NAME)) {
				$session = new FacebookSession($this->getSession()->get(static::FB_TOKEN_NAME));
			} else {
				$session = $helper->getSessionFromRedirect();
			}
		} catch (FacebookRequestException $ex) {
			var_dump($ex);
			exit;
		} catch (Exception $ex) {
			var_dump($ex);
			exit;
		}

		if (isset($session)) {
			if (!$this->getSession()->has(static::FB_TOKEN_NAME)) {
				$this->getSession()->set(static::FB_TOKEN_NAME, $session->getToken());
			}
			try {
				$request  = new FacebookRequest($session, 'GET', '/me');
				$response = $request->execute();

				$graphObject = $response->getGraphObject('Facebook\GraphUser');
				if (null === $graphObject->getEmail()) {
					$this->getSession()->remove(static::FB_TOKEN_NAME);
					return $helper->getLoginUrl(array('email'));
				}

				return $graphObject;
			} catch (FacebookRequestException $ex) {
				$this->getSession()->remove(static::FB_TOKEN_NAME);
			} catch (Exception $ex) {
				var_dump($ex);
				exit;
			}

		} else {
			return $helper->getReRequestUrl(array('email'));
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