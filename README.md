Watermarker
============

A watermark service module for Zend Framework 2


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