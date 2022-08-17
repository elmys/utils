<?php

namespace elmys\yii2\utils\ShareVideoLink\components\products\youtube;

use \elmys\yii2\utils\ShareVideoLink\Creator;
use yii\helpers\StringHelper;
use elmys\yii2\utils\ShareVideoLink\components\Product;

class YouTubeCreator extends Creator
{
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->platformCreator = StringHelper::basename(get_class($this));
    }

    public function getSocialProduct(): Product
    {
        return new YouTubeProduct();
    }
}