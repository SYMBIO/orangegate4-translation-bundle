<?php

namespace Symbio\OrangeGate\TranslationBundle\EventListener;

use Sonata\PageBundle\Site\SiteSelectorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Translation\TranslatorInterface;

class ResourceListener
{
	private $translator;
	private $siteSelector;

	public function __construct(TranslatorInterface $translator, SiteSelectorInterface $siteSelector)
	{
		$this->translator = $translator;
		$this->siteSelector = $siteSelector;
	}

    public function onKernelRequest(GetResponseEvent $event)
    {
		$site = $this->siteSelector->retrieve();

		foreach ($site->getLocales() as $locale) {
			$this->translator->addResource('db', null, $locale, 'messages');
			$this->translator->addResource('db', null, $locale, 'validators');
		}
    }

	public function onKernelException(GetResponseEvent $event)
	{
		$site = $this->siteSelector->retrieve();

		$this->translator->setLocale($site->getLocale());
	}
}