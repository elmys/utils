<?php

namespace elmys\yii2\utils\ShareVideoLink\components;

interface Product
{
    public function generateIframeCode($url): string;
}