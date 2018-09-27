<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\Translation\Extractor;

use EzSystems\EzPlatformAdminUi\Translation\CustomTagsTranslator;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

/**
 * Generate translation strings for RichText Custom Tags Admin UI.
 *
 * To extract translations, execute the following Symfony command in eZ Platform project:
 * <code>
 * $ php bin/console translation:extract <locale> --dir=<messages_directory> \
 *   --enable-extractor=ez_richtext_custom_tags --output-dir=<translations_output_directory>
 * </code>
 */
class CustomTagsExtractor implements ExtractorInterface
{
    /** @var array */
    private $customTagsConfiguration = [];

    /** @var string */
    private $translationDomain;

    public function __construct(array $customTagsConfiguration, string $translationDomain)
    {
        $this->customTagsConfiguration = $customTagsConfiguration;
        $this->translationDomain = $translationDomain;
    }

    /**
     * Extract translation strings for RichText Custom Tags.
     *
     * @return MessageCatalogue
     */
    public function extract(): MessageCatalogue
    {
        $catalogue = new MessageCatalogue();
        foreach ($this->customTagsConfiguration as $customTagName => $customTagSettings) {
            $catalogue->add(
                $this->createTagMessage(
                    $customTagName,
                    'label',
                    CustomTagsTranslator::TAG_LABEL_KEY_FORMAT,
                    $this->normalizeDefaultLocaleString($customTagName)
                )
            );
            $catalogue->add(
                $this->createTagMessage(
                    $customTagName,
                    'description',
                    CustomTagsTranslator::TAG_DESCRIPTION_KEY_FORMAT,
                    ''
                )
            );
            foreach (array_keys($customTagSettings['attributes']) as $attributeName) {
                $catalogue->add(
                    $this->createTagAttributeLabelMessage($customTagName, $attributeName)
                );
            }
        }

        return $catalogue;
    }

    /**
     * Generate translation message for a Custom Tag label or description.
     *
     * @param string $customTagName
     * @param string $type current implementation supports either "label" or "description"
     * @param string $messageKeyFormat Format for translation/message id/key
     * @param string $defaultMessage default translation
     *
     * @return Message\XliffMessage
     */
    private function createTagMessage(
        string $customTagName,
        string $type,
        string $messageKeyFormat,
        string $defaultMessage
    ): Message\XliffMessage {
        $id = sprintf($messageKeyFormat, $customTagName);
        $message = new Message\XliffMessage($id, $this->translationDomain);
        $message->setNew(false);
        $message->setMeaning("{$customTagName} {$type}");
        $message->setDesc("RichText CustomTag {$customTagName} {$type}");
        $message->setLocaleString($defaultMessage);
        $message->addNote('key: ' . $id);

        return $message;
    }

    /**
     * @param string $customTagName
     * @param string $customTagAttributeName
     *
     * @return Message\XliffMessage
     */
    private function createTagAttributeLabelMessage(
        string $customTagName,
        string $customTagAttributeName
    ): Message\XliffMessage {
        $id = sprintf(
            CustomTagsTranslator::TAG_ATTRIBUTE_LABEL_KEY_FORMAT,
            $customTagName,
            $customTagAttributeName
        );
        $message = new Message\XliffMessage($id, $this->translationDomain);
        $message->setNew(false);
        $message->setMeaning("{$customTagName}.{$customTagAttributeName} label");
        $message->setDesc(
            "RichText CustomTag {$customTagName} {$customTagAttributeName} attribute label"
        );
        $message->setLocaleString($this->normalizeDefaultLocaleString($customTagAttributeName));
        $message->addNote('key: ' . $id);

        return $message;
    }

    /**
     * Get default translation for Custom Tag label.
     *
     * @param string $localeString
     *
     * @return string
     */
    private function normalizeDefaultLocaleString(string $localeString): string
    {
        // treat locale strings starting with "ez" as internal names and remove prefix before processing
        if (strpos($localeString, 'ez') === 0) {
            $localeString = substr($localeString, 2);
        }

        return ucfirst(str_replace('_', ' ', $localeString));
    }
}
