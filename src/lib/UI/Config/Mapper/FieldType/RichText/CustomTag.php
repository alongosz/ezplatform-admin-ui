<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformAdminUi\UI\Config\Mapper\FieldType\RichText;

use EzSystems\EzPlatformAdminUi\Translation\CustomTagsTranslator;
use EzSystems\EzPlatformAdminUi\UI\Config\Mapper\FieldType\RichText\CustomTag\AttributeMapper;
use RuntimeException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Translation\TranslatorInterface;
use Traversable;

/**
 * RichText Custom Tag configuration mapper.
 */
class CustomTag
{
    /** @var array */
    private $customTagsConfiguration;

    /** @var Packages */
    private $packages;

    /** @var AttributeMapper[] */
    private $customTagAttributeMappers;

    /** @var TranslatorInterface */
    private $customTagsTranslator;

    /** @var AttributeMapper[] */
    private $supportedTagAttributeMappersCache;

    public function __construct(
        array $customTagsConfiguration,
        Packages $packages,
        Traversable $customTagAttributeMappers,
        CustomTagsTranslator $customTagsTranslator
    ) {
        $this->customTagsConfiguration = $customTagsConfiguration;
        $this->packages = $packages;
        $this->customTagAttributeMappers = $customTagAttributeMappers;
        $this->supportedTagAttributeMappersCache = [];
        $this->customTagsTranslator = $customTagsTranslator;
    }

    /**
     * Map Configuration for the given list of enabled Custom Tags.
     *
     * @param array $enabledCustomTags
     *
     * @return array Mapped configuration
     */
    public function mapConfig(array $enabledCustomTags)
    {
        $config = [];
        foreach ($enabledCustomTags as $tagName) {
            if (!isset($this->customTagsConfiguration[$tagName])) {
                throw new RuntimeException(
                    "RichText Custom Tag configuration for {$tagName} not found."
                );
            }

            $customTagConfiguration = $this->customTagsConfiguration[$tagName];

            if (!empty($customTagConfiguration['icon'])) {
                $config[$tagName]['icon'] = $this->packages->getUrl(
                    $customTagConfiguration['icon']
                );
            }

            $config[$tagName]['label'] = $this->customTagsConfiguration;
            $config[$tagName]['description'] = "ezrichtext.custom_tags.{$tagName}.description";
            foreach ($customTagConfiguration['attributes'] as $attributeName => $properties) {
                $typeMapper = $this->getAttributeTypeMapper(
                    $tagName,
                    $attributeName,
                    $properties['type']
                );
                $config[$tagName]['attributes'][$attributeName] = $typeMapper->mapConfig(
                    $tagName,
                    $attributeName,
                    $properties
                );
            }
        }

        return $this->customTagsTranslator->addTranslationLabels($config);
    }

    /**
     * Get first available Custom Tag Attribute Type mapper.
     *
     * @param string $tagName
     * @param string $attributeName
     * @param string $attributeType
     *
     * @return AttributeMapper
     */
    private function getAttributeTypeMapper(
        string $tagName,
        string $attributeName,
        string $attributeType
    ): AttributeMapper {
        if (isset($this->supportedTagAttributeMappersCache[$attributeType])) {
            return $this->supportedTagAttributeMappersCache[$attributeType];
        }

        foreach ($this->customTagAttributeMappers as $attributeMapper) {
            // get first supporting, order of these mappers is controlled by 'priority' DI tag attribute
            if ($attributeMapper->supports($attributeType)) {
                return $this->supportedTagAttributeMappersCache[$attributeType] = $attributeMapper;
            }
        }

        throw new RuntimeException(
            "RichText Custom Tag configuration: unsupported attribute type '{$attributeType}' of '{$attributeName}' attribute of '{$tagName}' Custom Tag"
        );
    }
}
