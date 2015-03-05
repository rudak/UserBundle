<?php
/**
 * Created by PhpStorm.
 * User: rudak
 * Date: 01/03/2015
 * Time: 15:55
 */

namespace Rudak\UserBundle\Listener;

use Rudak\UserBundle\Event\RecordEvent;
use Rudak\UserBundle\Handler\PostRecordHandler;

class PostRecordListener
{

    private $postRecordHandler;

    function __construct(PostRecordHandler $postRecordHandler)
    {
        $this->postRecordHandler = $postRecordHandler;
    }


    public function updateUser(RecordEvent $event)
    {
        $this->postRecordHandler->setUser($event->getUser());
        $this->postRecordHandler->justRecorded();
        $event->setUser($this->postRecordHandler->getUser());
    }
} 