<?php

namespace elmys\yii2\utils;

use elmys\yii2\utils\ShareVideoLink\components\YouTubeCreator;
use elmys\yii2\utils\ShareVideoLink\ClientCode;

require_once __DIR__ . '/vendor/autoload.php';

echo ClientCode::iframe(new YouTubeCreator(), 'grgra');

/*echo ShareVideoLink::getVideoIFrame('https://www.youtube.com/watch?v=bWLEFKiLBG4');
echo ShareVideoLink::getVideoIFrame('https://rutube.ru/video/8a6d5fe35ecd2b8aad48705eb76ef992');
echo ShareVideoLink::getVideoIFrame('https://vk.com/video?z=video-49107734_456241557');*/