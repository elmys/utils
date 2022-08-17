<?php
namespace elmys\yii2\utils\ShareVideoLink\components;

use elmys\yii2\utils\ShareVideoLink\components\Product;

class YouTubeProduct implements Product
{
    public function generateIframeCode($url): string
    {
        return "{Result of the YouTubeProduct}";
    }
}