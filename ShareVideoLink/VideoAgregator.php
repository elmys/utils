<?php
namespace elmys\yii2\utils\ShareVideoLink;

 use yii\db\Exception;
 use yii\helpers\Html;
 use elmys\yii2\utils\ShareVideoLink\components\products\youtube\YouTubeCreator;
 use elmys\yii2\utils\ShareVideoLink\components\products\rutube\RutubeCreator;
 use elmys\yii2\utils\ShareVideoLink\components\products\vk\VkCreator;

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
         }else{
             throw new \Exception('Video host not recognized');
         }
     }
 }
