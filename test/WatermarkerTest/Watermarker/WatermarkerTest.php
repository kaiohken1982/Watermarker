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
	
	protected $testConfig;
	
	protected $testImageName = 'berserk.jpg';
	
	protected $testWatermarkName = 'watermark.gif';
	
	protected function setUp()
	{
	    //$serviceLocator = $this->getApplicationServiceLocator();
	    $serviceLocator = Bootstrap::getServiceManager();
		$this->testImagePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' 
		    . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
		$this->thumbnailerService = $serviceLocator->get('Thumbnailer');
		$config = $serviceLocator->get('Configuration');
        $this->testConfig = isset($config['watermarker']) ? $config['watermarker'] : array();
	    $this->obj = new Watermarker($this->thumbnailerService);
		$this->obj->openImage($this->testImagePath . $this->testImageName);
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::__construct()
	 * @covers \Watermarker\Watermarker\Watermarker::getType()
	 */
	public function testGetType() 
	{
	    $this->assertEquals(Watermarker::WATERMARK_TYPE_FULLWIDTH, $this->obj->getType());
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::setType()
	 */
	public function testSetType() 
	{
	    $this->obj->setType(2);
	    $this->assertEquals(2, $this->obj->getType());
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::parseConfig()
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkFile()
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkFile() 
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkTmpDir() 
	 */
	public function testParseConfig() 
	{
        $this->obj->parseConfig($this->testConfig);
        
        $this->assertEquals('./data/watermark.gif', $this->obj->getWatermarkFile());
	    $this->assertEquals(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR, $this->obj->getWatermarkTmpDir());
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkTmpDir()
	 * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to create dir '/this/does/not/exists/'. Please check permissions
	 */
	public function testSetWatermarkTmpDir() 
	{
	    $this->obj->setWatermarkTmpDir('/this/does/not/exists');
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::openImage()
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
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 * @expectedException \RuntimeException
     * @expectedExceptionMessage Watermark is required to be of the mimetype image/gif, passed watermark is of mimetype 'image/jpeg'
	 */
	public function testOpenWatermarkJpg() 
	{
	    $fullpath = $this->testImagePath . $this->testImageName;
	    $this->obj->openWatermark($fullpath);
	}
	
	/**
 	 * @covers \Watermarker\Watermarker\Watermarker::getImageInfo()
	 */
	public function testGetImageInfo() 
	{
 	    $info = $this->obj->getImageInfo();

 	    $this->assertEquals($info['extension'], 'jpg');
 	    $this->assertEquals($info['height'], 406);
 	    $this->assertEquals($info['width'], 960);
 	    $this->assertEquals($info['mime'], 'image/jpeg');
 	    $this->assertEquals($info['channels'], 3);
 	    $this->assertEquals($info['bits'], 8);
 	    $this->assertEquals($info['filename'], 'berserk');
 	    $this->assertEquals($info['basename'], 'berserk.jpg');
 	    $this->assertEquals($info['dirname'], dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
	}
	
	/**
 	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkInfo()
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 */
	public function testWatermarkInfo() 
	{
	    $fullpath = $this->testImagePath . $this->testWatermarkName;
	    $this->obj->openWatermark($fullpath);
 	    $info = $this->obj->getWatermarkInfo();

 	    $this->assertEquals($info['extension'], 'gif');
 	    $this->assertEquals($info['height'], 98);
 	    $this->assertEquals($info['width'], 671);
 	    $this->assertEquals($info['mime'], 'image/gif');
 	    $this->assertEquals($info['channels'], 3);
 	    $this->assertEquals($info['bits'], 3);
 	    $this->assertEquals($info['filename'], 'watermark');
 	    $this->assertEquals($info['basename'], 'watermark.gif');
 	    $this->assertEquals($info['dirname'], dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'data');
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::setType()
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 * @covers \Watermarker\Watermarker\Watermarker::watermarkDimensionCoords()
	 */
	public function testWatermarkDimensionCoordsTypeFullwidth() 
	{
	    $fullpath = $this->testImagePath . $this->testWatermarkName;
	    $this->obj->openWatermark($fullpath);
	    $info = $this->obj->getImageInfo();
	    $this->obj->setType(Watermarker::WATERMARK_TYPE_FULLWIDTH);
	    $data = $this->obj->watermarkDimensionCoords();

	    // $resizedWatermarkWidth, same ad image width
	    $this->assertEquals($data[0], 960); 
	    
	    // $resizedWatermarkHeight, proportion with watermark height
	    $this->assertEquals($data[1], 140); 
	    
	    // the X cord where to place the watermark, with type 1 is 0
	    $this->assertEquals($data[2], 0); 
	    
	    // the Y cord where to place the watermark
	    $this->assertEquals($data[3], 133); 
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::watermark()
	 */
	public function testWatermark() 
	{
	    $fullpath = $this->testImagePath . $this->testWatermarkName;
	    $this->obj->openWatermark($fullpath);
	    $watermark = $this->obj->watermark();
	    
	    $this->assertTrue($watermark);
	}
}