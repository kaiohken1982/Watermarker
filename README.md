Image Watermarker Module
========================

[![Build Status](https://travis-ci.org/kaiohken1982/Watermarker.png?branch=master)](https://travis-ci.org/kaiohken1982/Watermarker)
[![Coverage Status](https://coveralls.io/repos/kaiohken1982/Watermarker/badge.png)](https://coveralls.io/r/kaiohken1982/Watermarker)
[![Dependency Status](https://www.versioneye.com/user/projects/52b20b09ec1375e702000038/badge.png)](https://www.versioneye.com/user/projects/52b20b09ec1375e702000038)
[![Latest Stable Version](https://poser.pugx.org/razor/watermarker/v/stable.png)](https://packagist.org/packages/razor/watermarker) 
[![Total Downloads](https://poser.pugx.org/razor/watermarker/downloads.png)](https://packagist.org/packages/razor/watermarker) 
[![Latest Unstable Version](https://poser.pugx.org/razor/watermarker/v/unstable.png)](https://packagist.org/packages/razor/watermarker) 
[![License](https://poser.pugx.org/razor/watermarker/license.png)](https://packagist.org/packages/razor/watermarker)

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