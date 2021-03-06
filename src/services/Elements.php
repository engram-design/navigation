<?php
namespace verbb\navigation\services;

use Craft;
use craft\base\Component;

use verbb\navigation\Navigation;
use verbb\navigation\events\RegisterElementEvent;

use craft\elements\Asset;
use craft\elements\Entry;
use craft\elements\Category;
use craft\helpers\ArrayHelper;

use craft\commerce\elements\Product;

class Elements extends Component
{
    // Constants
    // =========================================================================

    const EVENT_REGISTER_NAVIGATION_ELEMENT = 'registerNavigationElement';


    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->getRegisteredElements();
    }

    public function getRegisteredElements()
    {
        // Add default element support
        $elements = [
            [
                'label' => Craft::t('site', Entry::pluralDisplayName()),
                'button' => Craft::t('site', 'Add an entry'),
                'type' => Entry::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Entry::class, 'modal'),
                'default' => true,
            ],
            [
                'label' => Craft::t('site', Category::pluralDisplayName()),
                'button' => Craft::t('site', 'Add a category'),
                'type' => Category::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Category::class, 'modal'),
                'default' => true,
            ],
            [
                'label' => Craft::t('site', Asset::pluralDisplayName()),
                'button' => Craft::t('site', 'Add an asset'),
                'type' => Asset::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Asset::class, 'modal'),
                'default' => true,
            ],
        ];

        if (Craft::$app->getPlugins()->isPluginEnabled('commerce')) {
            $elements[] = [
                'label' => Craft::t('site', Product::pluralDisplayName()),
                'button' => Craft::t('site', 'Add a product'),
                'type' => Product::class,
                'sources' => Craft::$app->getElementIndexes()->getSources(Product::class, 'modal'),
                'default' => true,
            ];
        }

        // Add all other elements that suport URIs
        $addedElementTypes = ArrayHelper::getColumn($elements, 'type');

        foreach (Craft::$app->getElements()->getAllElementTypes() as $elementType) {
            if ($elementType::hasUris() && !in_array($elementType, $addedElementTypes)) {
                $elements[] = [
                    'label' => Craft::t('site', $elementType::pluralDisplayName()),
                    'button' => Craft::t('site', 'Add a {name}', ['name' => $elementType::lowerDisplayName()]),
                    'type' => $elementType,
                    'sources' => Craft::$app->getElementIndexes()->getSources($elementType, 'modal'),
                ];
            }
        }

        // Remove any defined in our config
        $settings = Navigation::$plugin->getSettings();

        $event = new RegisterElementEvent([
            'elements' => $elements,
        ]);

        $this->trigger(self::EVENT_REGISTER_NAVIGATION_ELEMENT, $event);

        return $event->elements;
    }

}