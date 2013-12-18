<?php 
namespace WatermarkerTest\Watermarker; 

use WatermarkerTest\Bootstrap;
use WatermarkerTest\Util\ServiceManagerFactory;
use Watermarker\Watermarker\Watermarker;
use PHPUnit_Framework_TestCase;

class WatermarkerTest 
	extends PHPUnit_Framework_TestCase
{
	protected $obj;
	
	protected $thumbnailerService;
	
	protected $testImagePath;
	
	protected $testImageName = 'berserk.jpg';
	
	protected $testWatermarkName = 'watermark.gif';
	
	protected function setUp()
	{
	    //$serviceLocator = $this->getApplicationServiceLocator();
	    $serviceLocator = Bootstrap::getServiceManager();
		$this->testImagePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' 
		    . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		$this->thumbnailerService = $serviceLocator->get('Thumbnailer');
	    $this->obj = new Watermarker($this->thumbnailerService);
		$this->obj->openImage($this->testImagePath . $this->testImageName);
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkFile()
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkFile() 
	 */
	public function testOpenWatermark() 
	{
	    $fullpath = $this->testImagePath . $this->testWatermarkName;
	    $this->obj->openWatermark($fullpath);
	    
	    $this->assertEquals($fullpath, $this->obj->getWatermarkFile());
	}
}