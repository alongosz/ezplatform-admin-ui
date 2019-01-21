<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\EventListener;

use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Set AdminUI user-preferred language based on browser settings.
 */
class UserLanguagePreferenceListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface
     */
    private $userLanguagePreferenceProvider;

    /**
     * @var \eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver
     */
    private $languageResolver;

    public function __construct(
        UserLanguagePreferenceProviderInterface $userLanguagePreferenceProvider,
        LanguageResolver $languageResolver
    ) {
        $this->userLanguagePreferenceProvider = $userLanguagePreferenceProvider;
        $this->languageResolver = $languageResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 6],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event): void
    {
        $languages = $this->userLanguagePreferenceProvider->getPreferredLanguages();

        if (empty($languages)) {
            return;
        }

        $this->languageResolver->setContextLanguage(
            $languages[0]
        );
    }
}
