<?php
namespace elmys\yii2\utils\ShareVideoLink\components\products\vk;

use elmys\yii2\utils\ShareVideoLink\components\Product;

class VkProduct implements Product
{
    const TEMPLATE = [
        'masks' => [
            '/https:\/\/vk\.com\/video-([0-9]+_[0-9]+)/',
            '/https:\/\/vk\.com\/video\?z=video-([0-9]+_[0-9]+)/', // https://vk.com/video?z=video-44563983_456239191
        ],
        'code' => '<iframe src="https://vk.com/video_ext.php?oid=-%videoId%&id=%videoId1%&hash=0&hd=1" width="100%" height="500" allow="autoplay; encrypted-media; fullscreen; picture-in-picture;" frameborder="0" allowfullscreen></iframe>',
    ];

    public function generateIframeCode($res): string
    {
        return $res;
    }
}