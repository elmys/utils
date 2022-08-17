<?php
namespace elmys\yii2\utils\ShareVideoLink\components\products\youtube;

use elmys\yii2\utils\ShareVideoLink\components\Product;

class YouTubeProduct implements Product
{
    const TEMPLATE = [
        'masks' => [
            '/https:\/\/[www\.]*youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/https:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/',
        ],
        'code' => '<iframe width="100%" height="500" class="embed-responsive-item" src="https://www.youtube.com/embed/%videoId%?rel=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',
    ];

    public function generateIframeCode($res): string
    {
        return $res;
    }
}