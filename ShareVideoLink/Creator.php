<?php
namespace elmys\yii2\utils\ShareVideoLink;

use elmys\yii2\utils\ShareVideoLink\components\Product;

abstract class Creator
{
    abstract public function getSocialProduct(): Product;

/*    public function someOperation(): string
    {
        $product = $this->factoryMethod();
        return $product->operation();
    }*/

    public function postVideo($url): string
    {
        // Вызываем фабричный метод для создания объекта Продукта...
        $product = $this->getSocialProduct();

        // ...а затем используем его по своему усмотрению.
        return $product->generateIframeCode($url);
    }
}