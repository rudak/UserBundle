<?php

namespace Rudak\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Rudak\UserBundle\Entity\User;

class LoadUserData implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $barre = '--------------------';
        echo $barre . "\nCREATION USERS\n" . $barre . "\n";

        $user = array();
        foreach ($this->getUsersToLoad() as $key => $userInfos) {
            $user[$key] = new User();
            $user[$key]->setUsername($userInfos['username']);
            $user[$key]->setEmail($userInfos['email']);
            $user[$key]->setRoles($userInfos['roles']);

            $encoder = $this->container
                ->get('security.encoder_factory')
                ->getEncoder($user[$key]);
            $user[$key]->setPassword($encoder->encodePassword('secret', $user[$key]->getSalt()));
            $manager->persist($user[$key]);

            echo 'Creation user : ' . $userInfos['username'] . "\n";
        }

        $manager->flush();
        echo "\nTERMINE\n" . $barre . "\n";
    }

    private function getUsersToLoad()
    {
        return array(
            array(
                'username' => 'joe',
                'password' => '0000',
                'email'    => 'joe@website.com',
                'roles'    => array('ROLE_USER')
            ),
            array(
                'username' => 'michel',
                'password' => '0000',
                'email'    => 'michel@website.com',
                'roles'    => array('ROLE_USER')
            ),
            array(
                'username' => 'admin',
                'password' => 'admin',
                'email'    => 'admin@admin.com',
                'roles'    => array('ROLE_USER', 'ROLE_ADMIN')
            )
        );
    }
}