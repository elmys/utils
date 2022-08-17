<?php
namespace elmys\yii2\utils\ShareVideoLink;

 class ClientCode
 {
     public static function iframe(Creator $creator, $url): string
     {
         return $creator->postVideo($url);
     }
 }
