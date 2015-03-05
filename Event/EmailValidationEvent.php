<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 05/03/2015
 * Time: 00:51
 */

namespace Rudak\UserBundle\Event;


use Rudak\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class EmailValidationEvent extends KernelEvent
{

    function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
}