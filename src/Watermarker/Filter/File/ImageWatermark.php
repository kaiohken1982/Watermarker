<?php

namespace Watermarker\Filter\File;

use Zend\Filter\AbstractFilter;

use Thumbnailer\Thumbnailer\Thumbnailer;
use Watermarker\Watermarker\Watermarker;

class ImageWatermark 
	extends AbstractFilter
{
	protected $options = array(
		'thumbnailer' => null,
		'watermarkPath' => null
	);
	
	public function __construct($options)
	{
		$this->options['watermarkPath'] = getcwd() . DIRECTORY_SEPARATOR . '/www/watermark.gif';
		$this->setOptions($options);
	}
	
	/**
	 * Set the thumbnailer given with the options
	 * @throws \Exception
	 * @return Cropper\Filter\File\ImageCrop
	 */
    protected function setThumbnailer(Thumbnailer $thumbnailer)
    {
    	if(!$thumbnailer instanceof Thumbnailer) {
    		throw new \Exception('The thumbnailer service given is not instance of Thumbnailer\Thumbnailer\Thumbnailer');
    	}
        $this->options['thumbnailer'] = $thumbnailer;
        
        return $this;
    }
    
    /**
     * Get thumbnailer service
     * @return Thumbnailer\Thumbnailer\Thumbnailer;
     */
    protected function getThumbnailer() 
    {
    	return $this->options['thumbnailer'];
    }
    
    protected function getWatermarkPath() 
    {
    	return $this->options['watermarkPath'];
    }
	
    /**
     * @param  string $value
     * @return string|mixed
     */
    public function filter($value)
    {
    	$isFile = false;
    	if(is_array($value) && isset($value['tmp_name'])) {
    		$filtered = $value['tmp_name'];
    		$isFile = true;
    	} else {
    		$filtered = $value;
    	}
    	
    	$basename = basename($filtered);
    	$dirname = dirname($filtered);
    	
		$watermarker = new Watermarker($this->getThumbnailer());
        $watermarker->openImage($filtered);
        $watermarker->openWatermark();
        $watermarker->watermark();
        
        return $value;
    }
}
