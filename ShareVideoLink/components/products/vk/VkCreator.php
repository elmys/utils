<?php

namespace elmys\yii2\utils\ShareVideoLink\components\products\vk;

use \elmys\yii2\utils\ShareVideoLink\Creator;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use elmys\yii2\utils\ShareVideoLink\components\Product;

class VkCreator extends Creator
{
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->platformCreator = StringHelper::basename(get_class($this));
    }

    // non-typical method @todo not work without hash
    public function getVideoCode(): string
    {
        if (mb_strpos($this->url, 'video_ext') !== false) {
            return $this->url;
        } else {
            return Html::a($this->url, $this->url, ['target' => '_blank']);
        }
        /*$res = explode('_', $this->getVideoId());
        $videoId1 = $res[0];
        $videoId2 = $res[1];
        $pass1 = str_replace("%videoId%", $videoId1, self::$templates[$this->platformCreator]['code']);
        $pass2 = str_replace("%videoId1%", $videoId2, $pass1);
        return $pass2;*/
    }

    public function getSocialProduct(): Product
    {
        return new VkProduct();
    }
}