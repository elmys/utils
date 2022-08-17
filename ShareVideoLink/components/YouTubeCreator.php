<?php

namespace elmys\yii2\utils\ShareVideoLink\components;

use \elmys\yii2\utils\ShareVideoLink\Creator;

class YouTubeCreator extends Creator
{
    public function getSocialProduct(): Product
    {
        return new YouTubeProduct();
    }
}