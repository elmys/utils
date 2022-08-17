<?php
namespace elmys\yii2\utils\ShareVideoLink\components\products\vimeo;

use elmys\yii2\utils\ShareVideoLink\components\Product;

class VimeoProduct implements Product
{
    const TEMPLATE = [
        'masks' => [
            '/https:\/\/vimeo\.com\/manage\/videos\/([a-zA-Z0-9_-]+)/'
        ],
        'code' => '<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://player.vimeo.com/video/%videoId%?badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479" 
frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Informal tournaments 09/03/18"></iframe></div><script src="https://player.vimeo.com/api/player.js"></script>',
    ];

    public function generateIframeCode($res): string
    {
        return $res;
    }
}