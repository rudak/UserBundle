<?php

namespace Rudak\UserBundle\Controller;

use Rudak\UserBundle\Entity\User;
use Rudak\UserBundle\Event\RecordEvent;
use Rudak\UserBundle\Event\UserEvents;
use Rudak\UserBundle\Form\RecordType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

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
            $data = $form->getData();
            $User->setPassword($this->createPassword($User));

            if (!$this->checkDuplicate($User)) {

                $RecordEvent = new RecordEvent($User);
                $this
                    ->get('event_dispatcher')
                    ->dispatch(UserEvents::USER_RECORD, $RecordEvent);

                $em = $this->getDoctrine()->getManager();
                $em->persist($User);
                $em->flush();

                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Utilisateur ' . $User->getUsername() . ' créé.'
                );

                return $this->render('RudakUserBundle:Record:after.html.twig');
            } else {
                $request->getSession()->getFlashBag()->add(
                    'notice',
                    'Utilisateur possedant le meme pseudo ou mot de passe existe deja dans la base'
                );
            }
        } else {
            $request->getSession()->getFlashBag()->add(
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
}
