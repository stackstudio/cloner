<?php
namespace verbb\cloner\services;

use verbb\cloner\Cloner;
use verbb\cloner\base\Service;

use Craft;
use craft\elements\Asset;
use craft\models\AssetTransform;

class AssetTransforms extends Service
{
    // Properties
    // =========================================================================

    public static $matchedRoute = 'asset-transforms/transform-index';
    public static $id = 'transforms';
    public static $title = 'Asset Transform';
    public static $action = 'clone/transform';


    // Public Methods
    // =========================================================================

    public function setupClonedTransform($oldTransform, $name, $handle)
    {
        $transform = new AssetTransform();
        $transform->name = $name;
        $transform->handle = $handle;

        $this->cloneAttributes($oldTransform, $transform, [
            'width',
            'height',
            'mode',
            'position',
            'quality',
            'interlace',
            'format',
        ]);

        return $transform;
    }

}