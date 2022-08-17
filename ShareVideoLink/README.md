# ShareVideoLink
Generate iframe-code from video url (rutube, vimeo, vk(*), youtube).
-
Installation
-
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```
php composer.phar require --prefer-dist "elmys/utils" : "*"
```

or add

```
"elmys/utils" : "*"
```

to the require section of your application's `composer.json` file.

Usage
-

```php
namespace elmys\yii2\utils;

use elmys\yii2\utils\ShareVideoLink\VideoAgregator;

require_once __DIR__ . '/vendor/autoload.php';

echo VideoAgregator::iframe('https://www.youtube.com/watch?v=bWLEFKiLBG4');
echo VideoAgregator::iframe('https://rutube.ru/video/8a6d5fe35ecd2b8aad48705eb76ef992');
echo VideoAgregator::iframe('https://vk.com/video?z=video-49107734_456241557');
echo VideoAgregator::iframe('https://vimeo.com/manage/videos/259487847');
```

- vk not working without hash, which generating by user via UI