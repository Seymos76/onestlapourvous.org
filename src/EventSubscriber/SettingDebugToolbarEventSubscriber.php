<?php


namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SettingDebugToolbarEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse']];
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $response = $event->getResponse();
        //$response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
    }
}