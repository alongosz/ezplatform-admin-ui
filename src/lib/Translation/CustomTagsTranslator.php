<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformAdminUi\Translation;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Custom Tags UI translator.
 */
class CustomTagsTranslator
{
    const TAG_LABEL_KEY_FORMAT = 'ezrichtext.custom_tags.%s.label';
    const TAG_DESCRIPTION_KEY_FORMAT = 'ezrichtext.custom_tags.%s.description';
    const TAG_ATTRIBUTE_LABEL_KEY_FORMAT = 'ezrichtext.custom_tags.%s.attributes.%s.label';

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $translationDomain;

    public function __construct(TranslatorInterface $translator, string $translationDomain)
    {
        $this->translator = $translator;
        $this->translationDomain = $translationDomain;
    }

    /**
     * Process Custom Tags config and amend it with translation labels for UI.
     *
     * Config is mapped by:
     *
     * @see \EzSystems\EzPlatformAdminUi\UI\Config\Mapper\FieldType\RichText\CustomTag
     *
     * @param array $config
     *
     * @return array processed Custom Tags config with translated labels
     */
    public function addTranslationLabels(array $config): array
    {
        foreach ($config as $tagName => $tagConfig) {
            $config[$tagName]['label'] = $this->getTranslatedTagLabel($tagName);
            $config[$tagName]['description'] = $this->getTranslatedTagDescription($tagName);

            if (empty($tagConfig['attributes'])) {
                continue;
            }

            foreach ($tagConfig['attributes'] as $attributeName => $attributeConfig) {
                $config[$tagName]['attributes'][$attributeName]['label'] = $this->getTranslatedTagAttributeLabel(
                    $tagName,
                    $attributeName
                );
            }
        }

        return $config;
    }

    private function getTranslatedTagLabel(string $tagName): string
    {
        return $this->translator->trans(
            /** @Ignore */
            sprintf(static::TAG_LABEL_KEY_FORMAT, $tagName),
            [],
            $this->translationDomain
        );
    }

    private function getTranslatedTagDescription(string $tagName): string
    {
        return $this->translator->trans(
            /** @Ignore */
            sprintf(static::TAG_DESCRIPTION_KEY_FORMAT, $tagName),
            [],
            $this->translationDomain
        );
    }

    private function getTranslatedTagAttributeLabel(string $tagName, string $tagAttribute): string
    {
        return $this->translator->trans(
            /** @Ignore */
            sprintf(static::TAG_ATTRIBUTE_LABEL_KEY_FORMAT, $tagName, $tagAttribute),
            [],
            $this->translationDomain
        );
    }
}
