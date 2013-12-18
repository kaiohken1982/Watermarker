[![Build Status](https://travis-ci.org/kaiohken1982/Thumbnailer.png)](https://travis-ci.org/kaiohken1982/Thumbnailer) - [![Dependency Status](https://www.versioneye.com/user/projects/52b17633ec1375723700004e/badge.png)](https://www.versioneye.com/user/projects/52b17633ec1375723700004e)

Image Watermarker Module
========================

An image watermark service module for Zend Framework 2


### Install with Composer
 ```
{
  "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kaiohken1982/Watermarker.git"
        }
    ],
    "require": {
        ......,
        "razor/watermarker" : "dev-master"
    }
}
 ```

### How to use

In a controller

 ```
		$watermarker = $this->getServiceLocator()->get('Watermarker');
		$watermarker->openImage('/path/to/image.jpg');
		$watermarker->openWatermark('/path/to/watermark.gif');
		$watermarker->watermark();
 ```

### Run unit test
 
Please note you must be in the module root.

```
curl -s http://getcomposer.org/installer | php
php composer.phar install
cd tests
../vendor/bin/phpunit 
```

If you have xdebug enabled and you want to see code coverage 
run the command below, it'll create html files in 
Watermarker\test\data\coverage

```
../vendor/bin/phpunit --coverage-html data/coverage
```