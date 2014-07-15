<?php 
namespace WatermarkerTest\Filter\File; 

use WatermarkerTest\Bootstrap;
use WatermarkerTest\Util\ServiceManagerFactory;
use Watermarker\Filter\File\ImageWatermark;
use PHPUnit_Framework_TestCase;
use Watermarker\Watermarker\Watermarker;

class ImageWatermarkTest 
	extends PHPUnit_Framework_TestCase
{
    /**
     * The objet to be tested
     * @var Watermarker
     */
	protected $obj;
	
	/**
	 * The watermarker service.
	 * @var Watermarker
	 */
	protected $watermarkerService;
	
	/**
	 * @var string
	 */
	protected $testImage;
	
	/**
	 * This is called once for every test function.
	 * This is reset by tearDown for each test.
	 */
	protected function setUp()
	{
	    $serviceLocator = Bootstrap::getServiceManager();
		$config = $serviceLocator->get('Configuration');
		$this->testImage = realpath('./data/berserkJPG.jpg');
	    $this->watermarkerService = new Watermarker($serviceLocator->get('Thumbnailer'));
	    $this->watermarkerService->parseConfig(isset($config['watermarker']) ? $config['watermarker'] : array());
	    $this->obj = new ImageWatermark($this->watermarkerService);
	}
	
	/**
	 * @covers \Watermarker\Filter\File\ImageWatermark::__construct()
	 * @covers \Watermarker\Filter\File\ImageWatermark::filter()
	 */
	public function testFilter() 
	{
	    // we use a copy of this image that will be removed in teardown
	    // so we start always with a clean image
		$imageCopy = str_replace('berserkJPG', 'berserkFilterTest', $this->testImage);
		copy($this->testImage, $imageCopy);
	    $response = $this->obj->filter($imageCopy);
	    $this->assertEquals($imageCopy, $response);
	}
	
	/**
	 * @covers \Watermarker\Filter\File\ImageWatermark::__construct()
	 * @covers \Watermarker\Filter\File\ImageWatermark::filter()
	 */
	public function testFilterArrayValue() 
	{
	    // we use a copy of this image that will be removed in teardown
	    // so we start always with a clean image
		$imageCopy = array('tmp_name' => str_replace('berserkJPG', 'berserkFilterTest', $this->testImage));
		copy($this->testImage, $imageCopy['tmp_name']);
	    $response = $this->obj->filter($imageCopy);
	    $this->assertEquals($imageCopy, $response);
	}
	
	public function tearDown() 
	{
	    $testImage = realpath('.' . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . 'berserkFilterTest.jpg');
	    if(!empty($testImage) && file_exists($testImage)) {
	        unlink($testImage);
	    }
	    
	    $this->obj = null;
	}
}