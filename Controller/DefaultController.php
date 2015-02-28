<?php

namespace Rudak\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RudakUserBundle:Default:index.html.twig');
    }

    public function testAction()
    {
        return $this->render('RudakUserBundle:Admin:index.html.twig');
    }
}
