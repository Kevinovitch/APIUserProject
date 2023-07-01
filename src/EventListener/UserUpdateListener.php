<?php

namespace App\EventListener;

use App\Controller\UserController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * An event listener to write a message in the logs
 * after each update on a User
 */
class UserUpdateListener
{

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $controller = $event->getController();

        // Check if the controller used is UserController and the action is an update action
        if($controller[0] instanceof UserController && $event->getRequest()->getMethod() === 'PUT')
        {
            // Get the id of the modified User
            $id = $event->getRequest()->attributes->get('id');

            // Write and save a message in the logs
            $this->logger->info('Update of the user with id :'.$id);
        }
    }
}