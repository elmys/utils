<?php
namespace elmys\yii2\utils\ShareVideoLink\components\products\rutube;

use elmys\yii2\utils\ShareVideoLink\components\Product;

class RutubeProduct implements Product
{
    const TEMPLATE = [
        'masks' => [
            '/https:\/\/rutube\.ru\/video\/([a-zA-Z0-9_-]+)/'
        ],
        'code' => '<iframe src="https://rutube.ru/play/embed/%videoId%" width="100%" height="500" frameborder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>',
    ];

    public function generateIframeCode($res): string
    {
        return $res;
    }
}