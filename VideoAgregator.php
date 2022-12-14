<?php
namespace elmys\yii2\utils;

 use elmys\yii2\utils\ShareVideoLink\components\products\vimeo\VimeoCreator;
 use elmys\yii2\utils\ShareVideoLink\components\products\youtube\YouTubeCreator;
 use elmys\yii2\utils\ShareVideoLink\components\products\rutube\RutubeCreator;
 use elmys\yii2\utils\ShareVideoLink\components\products\vk\VkCreator;
 use elmys\yii2\utils\ShareVideoLink\Creator;

 class VideoAgregator
 {
     public static function iframe($link): string
     {
         $creator = self::getCreator($link);
         return $creator->postVideo($link);
     }

     public static function getCreator($link): Creator
     {
         if (mb_strpos($link, 'youtube') !== false) {
             return new YouTubeCreator($link);
         } elseif (mb_strpos($link, 'vk') !== false) {
             return new VkCreator($link);
         } elseif (mb_strpos($link, 'rutube') !== false) {
             return new RutubeCreator($link);
         } elseif (mb_strpos($link, 'vimeo') !== false) {
             return new VimeoCreator($link);
         }else{
             throw new \Exception('Video host not recognized');
         }
     }
 }

