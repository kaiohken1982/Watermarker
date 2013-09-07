<?php 
namespace Watermarker\Watermarker;

interface WatermarkerInterface 
{
	/**
	 * Configure object params
	 * 
	 * @param array $config
     * @return \Watermarker\Watermarker\Watermarker
	 */
	function parseConfig($config);
	
	/**
	 * Open the watermark image
	 * 
	 * @param string
	 * @throws \RuntimeException
     * @return \Watermarker\Watermarker\Watermarker
	 */
	function openWatermark($watermarkImageFullPath = null);
	
	/**
	 * Open the image that will be watermarked
	 * 
	 * @param string
     * @return \Watermarker\Watermarker\Watermarker
	 */
	function openImage($watermarkableImageFullPath);
	
	/**
	 * Do the watermark work
	 * 
	 * @throws \RuntimeException
	 * @return bool
	 */
	function watermark();
}