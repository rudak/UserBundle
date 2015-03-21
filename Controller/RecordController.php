<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Event\EmailValidationEvent;
use Rudak\UserBundle\Event\RecordEvent;
use Rudak\UserBundle\Event\UserEvents;
use Rudak\UserBundle\Form\RecordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RecordController extends Controller
{
    public function newAction()
    {
        $User = new User();
        $form = $this->createNewForm($User);

        return $this->render('RudakUserBundle:Record:new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    private function createNewForm($user)
    {
        $form = $this->createForm(new RecordType(), $user, [
            'action' => $this->generateUrl('record_create')
        ]);

        return $form;
    }

    public function createAction(Request $request)
    {

        $User = new User();
        $form = $this->createNewForm($User);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $User->setPassword($this->createPassword($User));

            if (!$this->checkDuplicate($User)) {

                $RecordEvent = new RecordEvent($User);
                $this
                    ->get('event_dispatcher')
                    ->dispatch(UserEvents::USER_RECORD, $RecordEvent);

                $em = $this->getDoctrine()->getManager();
                $em->persist($User);
                $em->flush();

                $this->addFlash('notice', 'Utilisateur ' . $User->getUsername() . ' créé.');

                return $this->render('RudakUserBundle:Record:after.html.twig', array(
                    'user' => $User
                ));
            } else {
                $this->addFlash(
                    'notice',
                    'Utilisateur possedant le meme pseudo ou mot de passe existe deja dans la base'
                );
            }
        } else {
            $this->addFlash(
                'notice',
                'Formulaire invalide !'
            );
        }


        return $this->redirect($this->generateUrl('record_new'));
    }

    private function createPassword(User $user)
    {
        $encoder = $this->container
            ->get('security.encoder_factory')
            ->getEncoder($user);
        return $encoder->encodePassword($user->getPassword(), $user->getSalt());

    }

    private function checkDuplicate(User $user)
    {
        $em   = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('RudakUserBundle:User');

        if ($repo->getUserIfExists($user->getUsername()) instanceof User) {
            return true;
        } else {
            return false;
        }
    }

    public function validationAction($hash, Request $request)
    {
        $em   = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('RudakUserBundle:User');

        $User = $repo->getUserByHash($hash);
        if (!$User) {
            // correspond a rien
            throw $this->createNotFoundException('Utilisateur impossible a trouver avec le hash "' . $hash . '".');
        }
        if (true == $User->getIsActive()) {
            // utilisateur deja actif
            throw $this->createNotFoundException('Cet utilisateur a déja été activé !');
        }

        $EmailValidationEvent = new EmailValidationEvent($User);
        $this
            ->get('event_dispatcher')
            ->dispatch(UserEvents::USER_EMAIL_VALIDATION, $EmailValidationEvent);

        $token = new UsernamePasswordToken($User, null, "secured_area", $User->getRoles());
        $this->get("security.context")->setToken($token); //maintenant le gars est loggé

        //maintenant il faut dispatch l'event du login 'classique'
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        $em->persist($User);
        $em->flush();

        $this->addFlash(
            'notice',
            'Email de l\'utilisateur ' . $User->getUsername() . ' validée.'
        );
        return $this->redirectToRoute('homepage');
    }
}
