<?php
namespace elmys\yii2\utils\ShareVideoLink;

use elmys\yii2\utils\ShareVideoLink\components\Product;
use elmys\yii2\utils\ShareVideoLink\components\products\rutube\RutubeProduct;
use elmys\yii2\utils\ShareVideoLink\components\products\vk\VkProduct;
use elmys\yii2\utils\ShareVideoLink\components\products\youtube\YouTubeProduct;
use yii\helpers\StringHelper;

abstract class Creator
{
    public static $templates = [
        'YouTubeCreator' => YouTubeProduct::TEMPLATE,
        'RutubeCreator' => RutubeProduct::TEMPLATE,
        'VkCreator' => VkProduct::TEMPLATE,
    ];
    public $url;
    public $platformCreator;

    abstract public function getSocialProduct(): Product;

    public function getVideoId(): string
    {
        if ($pattern = self::$templates[$this->platformCreator]) {
            foreach ($pattern['masks'] as $mask) {
                preg_match($mask, $this->url, $matches);
                if (count($matches) > 1) {
                    return $matches[1];
                }
            }
        }
        return '';
    }

    public function getVideoCode(): string
    {
        return str_replace("%videoId%", $this->getVideoId(), self::$templates[$this->platformCreator]['code']);
    }

    public function postVideo(): string
    {
        $product = $this->getSocialProduct();
        return $product->generateIframeCode($this->getVideoCode());
    }
}