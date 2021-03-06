<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Event\BaseEvent;
use Rudak\UserBundle\Event\UserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class EmailController extends Controller
{

	public function changeEmailAction(Request $request)
	{
		$user = $this->getUser();
		if (null != $user->getSecurityHash()) {
			// validation en attente
			$this->addFlash('warning', 'Suppression de la precedente confirmation par mail en attente.');
			$user->setSecurityHash(null);
			$user->setSecurityHashExpireAt(null);
			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();
		}
		$values = array(
			'email' => $user->getEmail()
		);
		$form   = $this->getForm($values, array(
			'action' => $this->generateUrl('rudakUser_email_modification'),
		));

		$form->handleRequest($request);
		if ($form->isValid()) {
			$formData = $form->getData();
			$user->setEmailTmp($formData['email']);
			$this->addFlash('success', 'Un mail vous a été envoyé pour valider cette nouvelle adresse. Sans validations aucune modification ne sera effectuée.');
			$BaseEvent = new BaseEvent($user);
			$this
				->get('event_dispatcher')
				->dispatch(UserEvents::USER_EMAIL_CHANGE_REQUEST, $BaseEvent);

			return $this->redirectToRoute($this->getHomepageRoute());
		}

		return $this->render('RudakUserBundle:Default:change-email.html.twig', array(
			'form' => $form->createView()
		));
	}

	public function confirmationEmailAction($hash)
	{
		$em   = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('RudakUserBundle:User');
		$user = $this->getUser();
		if (!$user) {
			// si le gars n'est pas loggé
			$user = $repo->getUserByHash($hash);
			if (!$user) {
				$this->addFlash('danger', 'Ce lien de validation ne correspond a rien.');

				return $this->redirectToRoute($this->getHomepageRoute());
			}
		}
		if ($user->getSecurityHashExpireAt() < new \Datetime('NOW')) {
			// expiré
			$this->addFlash('danger', 'Ce lien de validation est inconnu ou expiré.');

			return $this->redirectToRoute($this->getHomepageRoute());
		}
		$BaseEvent = new BaseEvent($user);
		$this
			->get('event_dispatcher')
			->dispatch(UserEvents::USER_EMAIL_CHANGE_SUCCESS, $BaseEvent);
		$this->addFlash('success', 'Adresse email modifiée avec succès.');

		return $this->redirectToRoute($this->getHomepageRoute());
	}

	private function getForm($values, $options)
	{
		return $this->createFormBuilder($values, $options)
					->add('email', 'email', array(
						'label' => 'Adresse email'
					))
					->add('submit', 'submit', array(
						'label' => 'Modifier',
						'attr'  => array(
							'class' => 'btn btn-success'
						)
					))
					->getForm();
	}

	private function getHomepageRoute()
	{
		$config = $this->container->getParameter('rudak.user.config');
		return $config['homepage_route'];
	}
}
