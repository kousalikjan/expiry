<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            dump('no session');
            return;
        }

        // session exists
        dump('base: ' . $request->getLocale());
        $request->setLocale($request->getSession()->get('_locale', $request->getLocale()));
        $this->translator->setLocale($request->getLocale());
        dump('request locale: ' . $request->getLocale());
        dump('translator locale: ' . $this->translator->getLocale());

    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 10]],
        ];
    }
}