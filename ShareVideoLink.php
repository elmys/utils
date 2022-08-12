<?php

namespace elmys\yii2\utils;

use yii\helpers\Html;

class ShareVideoLink
{
    const RUTUBE_HOST = [
        'masks' => [
            '/https:\/\/rutube\.ru\/video\/([a-zA-Z0-9_-]+)/'
        ],
        'code' => '<iframe src="https://rutube.ru/play/embed/%videoId%" width="100%" height="500" frameborder="0" allow="clipboard-write; autoplay" webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>',
    ];

    const VK_HOST = [
        'masks' => [
            '/https:\/\/vk\.com\/video-([0-9]+_[0-9]+)/',
        ],
        'code' => '<iframe src="https://vk.com/video_ext.php?oid=-%videoId%&id=%videoId1%&hash=0&hd=1" width="100%" height="500" allow="autoplay; encrypted-media; fullscreen; picture-in-picture;" frameborder="0" allowfullscreen></iframe>',
    ];

    const YOUTUBE_HOST = [
        'masks' => [
            '/https:\/\/[www\.]*youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/',
            '/https:\/\/youtu\.be\/([a-zA-Z0-9_-]+)/',
        ],
        'code' => '<iframe width="100%" height="500" class="embed-responsive-item" src="https://www.youtube.com/embed/%videoId%?rel=0" allow="autoplay; encrypted-media" allowfullscreen></iframe>',
    ];

    const VIDEO_HOSTS = [
        'youtube' => self::YOUTUBE_HOST,
        'rutube' => self::RUTUBE_HOST,
        'vk' => self::VK_HOST,
    ];

    public static function getVideoId($url, $platform = 'youtube')
    {
        if ($pattern = self::VIDEO_HOSTS[$platform]) {
            foreach ($pattern['masks'] as $mask) {
                preg_match($mask, $url, $matches);
                if (count($matches) > 1) {
                    return $matches[1];
                }
            }
        }
        return null;
    }

    public static function getVideoCode($videoId, $platform = 'youtube')
    {
        if ($platform == 'vk') {
            $res = explode('_', $videoId);
            $videoId1 = $res[0];
            $videoId2 = $res[1];
            $pass1 = str_replace("%videoId%", $videoId1, self::VIDEO_HOSTS[$platform]['code']);
            $pass2 = str_replace("%videoId1%", $videoId2, $pass1);
            return $pass2;
        }
        return str_replace("%videoId%", $videoId, self::VIDEO_HOSTS[$platform]['code']);
    }

    /**
     * @return string
     */
    public static function getCodeIFrame($link, $platform = 'youtube')
    {
        $vid = self::getVideoId($link, $platform);
        return self::getVideoCode($vid, $platform);
    }

    /**
     * @return string
     */
    public static function getVideoIFrame($link)
    {
        if (mb_strpos($link, 'youtube') !== false) {
            $platform = 'youtube';
            $vid = strpos($link, 'youtu') !== false ? self::getVideoId($link, $platform) : $link;
            return self::getVideoCode($vid, $platform);
        } elseif (mb_strpos($link, 'vk') !== false) {
            // если iframe
            if (mb_strpos($link, 'video_ext') !== false) {
                return $link;
            } else {
                return Html::a($link, $link, ['target' => '_blank']);
            }
            // @todo пока не знаю как получить hash, без которого видео в iframe недоступно
            /*$platform = 'vk';
            $vid = self::getVideoId($link, $platform);
            return self::getVideoCode($vid, $platform);*/

        } elseif (mb_strpos($link, 'rutube') !== false) {
            $platform = 'rutube';
            $vid = self::getVideoId($link, $platform);
            return self::getVideoCode($vid, $platform);
        }
        return $link;
    }
}
