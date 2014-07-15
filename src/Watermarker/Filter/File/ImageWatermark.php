<?php

namespace Watermarker\Filter\File;

use Zend\Filter\AbstractFilter;

use Watermarker\Watermarker\Watermarker;

class ImageWatermark 
	extends AbstractFilter
{
    /**
     * @var Watermarker
     */
	protected $watermarkerService;
	
	/**
	 * Set watermarker service dependency
	 * @param unknown $watermarkerService
	 */
	public function __construct($watermarkerService)
	{
		$this->watermarkerService = $watermarkerService;
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

    	$this->watermarkerService->openImage($filtered);
        $this->watermarkerService->watermark();
        
        return $value;
    }
}
