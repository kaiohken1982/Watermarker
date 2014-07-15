<?php 
namespace WatermarkerTest\Watermarker; 

use WatermarkerTest\Bootstrap;
use WatermarkerTest\Util\ServiceManagerFactory;
use Watermarker\Watermarker\Watermarker;
use PHPUnit_Framework_TestCase;

class WatermarkerTest 
	extends PHPUnit_Framework_TestCase
{
    /**
     * The objet to be tested
     * @var Watermarker
     */
	protected $obj;
	
	/**
	 * This is a dependency passed to constructor
	 * @var Object
	 */
	protected $thumbnailerService;
	
	/**
	 * The fullpath of the image to be watermarked for test
	 * @var string
	 */
	protected $testImage;
	
	protected $testWatermarkName = 'watermark.gif';
	
	/**
	 * This is called once for every test function.
	 * This is reset by tearDown for each test.
	 */
	protected function setUp()
	{
	    //$serviceLocator = $this->getApplicationServiceLocator();
	    $serviceLocator = Bootstrap::getServiceManager();
	    
	    // we use a copy of this image that will be removed in teardown
	    // so we start always with a clean image
	    $image = realpath('.' . DIRECTORY_SEPARATOR . 'data'. 
		    DIRECTORY_SEPARATOR . 'berserkJPG.jpg');
	    
		$this->testImage = str_replace('berserkJPG', 'berserkTest', $image);
		
		copy($image, $this->testImage);
		
		$this->thumbnailerService = $serviceLocator->get('Thumbnailer');
		$config = $serviceLocator->get('Configuration');
	    $this->obj = new Watermarker($this->thumbnailerService);
		$this->obj->parseConfig(isset($config['watermarker']) ? $config['watermarker'] : array());
		$this->obj->openImage($this->testImage);
	}
	
	/**
	 * Test if the temporary watermark directory is set by parseConfig 
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkTmpDir()
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkTmpDir()
	 * @covers \Watermarker\Watermarker\Watermarker::parseConfig()
	 */
	public function testSetGetWatermarkTmpDir() 
	{
	    $this->assertEquals(realpath('./data/tmpWatermark/') . DIRECTORY_SEPARATOR, $this->obj->getWatermarkTmpDir());
	}
	
	/**
	 * Test if the watermark full path is set by parseConfig 
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkFullPath()
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkFullPath()
	 * @covers \Watermarker\Watermarker\Watermarker::parseConfig()
	 */
	public function testSetGetWatermarkFullPath() 
	{
	    $this->assertEquals(realpath('./data/watermark.gif'), $this->obj->getWatermarkFullPath());
	}

	/**
	 * Test if it throws an exception if the image is not valid to be a watermark
	 *
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkTmpDir()
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Watermark directory is empty. This can be due to the provided directory '/doesnotexists' not exists, please check.
	 */
	public function testSetGetWatermarkTmpDirEmptyException()  
	{
	    $this->obj->setWatermarkTmpDir('/doesnotexists'); 
	}
	
	/**
	 * The if this value was set in openImage
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::setImageFullPath()
	 * @covers \Watermarker\Watermarker\Watermarker::getImageFullPath()
	 */
	public function testGetSetImageFullPath() 
	{
	    $this->assertEquals($this->testImage, $this->obj->getImageFullPath());
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::__construct()
	 * @covers \Watermarker\Watermarker\Watermarker::getType()
	 * @covers \Watermarker\Watermarker\Watermarker::setType()
	 */
	public function testGetSetType()
	{
	    $this->assertEquals(Watermarker::WATERMARK_TYPE_FULLWIDTH, $this->obj->getType());
	}
	
	/**
	 * Test if it throws an exception if the image is not valid to be a watermark
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 * @expectedException \RuntimeException
     * @expectedExceptionMessage Watermark is required to be of the mimetype image/gif, passed watermark is of mimetype 'image/jpeg'
	 */
	public function testOpenWatermarkJpgShoulGiveException() 
	{
	    $this->obj->openWatermark($this->testImage);
	}
	
	/**
	 * This tests if the image to be marked is well loaded
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::setImageInfo()
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
	    $this->assertEquals($info['filename'], 'berserkTest');
	    $this->assertEquals($info['basename'], 'berserkTest.jpg');
	    $this->assertEquals($info['dirname'], realpath('./data'));
	}

	/**
	 * This tests if the watermark is well loaded.
	 * Watermark path is given in setUp by parseConfig
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::setWatermarkInfo()
	 * @covers \Watermarker\Watermarker\Watermarker::getWatermarkInfo()
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 */
	public function testWatermarkInfo()
	{
	    $info = $this->obj->getWatermarkInfo();
	
	    $this->assertEquals($info['extension'], 'gif');
	    $this->assertEquals($info['height'], 98);
	    $this->assertEquals($info['width'], 671);
	    $this->assertEquals($info['mime'], 'image/gif');
	    $this->assertEquals($info['channels'], 3);
	    $this->assertEquals($info['bits'], 3);
	    $this->assertEquals($info['filename'], 'watermark');
	    $this->assertEquals($info['basename'], 'watermark.gif');
	    $this->assertEquals($info['dirname'], realpath('./data'));
	}
	
	/**
	 * @covers \Watermarker\Watermarker\Watermarker::openWatermark()
	 * @covers \Watermarker\Watermarker\Watermarker::watermarkDimensionCoords()
	 */
	public function testWatermarkDimensionCoordsTypeFullwidth() 
	{
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
	 * Check if it does the job..
	 * This uses the PNG image set in this function, setUp is overridden
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::openImage()
	 * @covers \Watermarker\Watermarker\Watermarker::watermark()
	 */
	public function testWatermarkPNG()
	{
	    $image = realpath('.' . DIRECTORY_SEPARATOR . 'data'. 
		    DIRECTORY_SEPARATOR . 'berserkPNG.png');
	    
		$testImage = str_replace('berserkPNG', 'berserkTestPNG', $image);
		
		copy($image, $testImage);
		
	    $this->obj->openImage(realpath('.' . DIRECTORY_SEPARATOR . 'data'. 
		    DIRECTORY_SEPARATOR . 'berserkPNG.png'));
	    
	    $watermarkResponse = $this->obj->watermark();
	     
	    $this->assertTrue($watermarkResponse);
	}
	
	/**
	 * Check if it does the job..
	 * This uses the GIF image set in this function, setUp is overridden
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::openImage()
	 * @covers \Watermarker\Watermarker\Watermarker::watermark()
	 */
	public function testWatermarkGIF()
	{
	    $image = realpath('.' . DIRECTORY_SEPARATOR . 'data'. 
		    DIRECTORY_SEPARATOR . 'berserkGIF.gif');
	    
		$testImage = str_replace('berserkGIF', 'berserkTestGIF', $image);
		
		copy($image, $testImage);
		
	    $this->obj->openImage(realpath('.' . DIRECTORY_SEPARATOR . 'data'. 
		    DIRECTORY_SEPARATOR . 'berserkGIF.gif'));
	    
	    $watermarkResponse = $this->obj->watermark();
	     
	    $this->assertTrue($watermarkResponse);  
	}
	
	/**
	 * The image extension is not supported
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::openImage()
	 * @covers \Watermarker\Watermarker\Watermarker::watermark()
	 * @expectedException \RuntimeException
     * @expectedExceptionMessage Not supported image type
	 */
	public function testWatermarkBMP()
	{
		$this->obj->setImageInfo(array(
		    'extension' => 'bmp',
		    'width' => 100,
		    'height' => 100
		));
	    
	    $watermarkResponse = $this->obj->watermark();
	}
	
	/**
	 * Final test, check if it does the job..
	 * This uses the JPG image set in setUp.
	 * This method *MUST* stay as final method if you are checking 
	 * if the imaged is actually being marked (commenting tearDown).
	 * If setUp run again the image copy will be overriden, 
	 * resulting in a fresh image.
	 * 
	 * @covers \Watermarker\Watermarker\Watermarker::openImage()
	 * @covers \Watermarker\Watermarker\Watermarker::watermark()
	 */
	public function testWatermarkJPG()
	{
	    $watermarkResponse = $this->obj->watermark();
	     
	    $this->assertTrue($watermarkResponse);
	    
	    $this->obj->setWatermarkInfo(null);
	    $watermarkResponse = $this->obj->watermark();
	     
	    $this->assertTrue($watermarkResponse);
	}
	
	/**
	 * This is called once for every test function after the test function run
	 */
	protected function tearDown() 
	{
	    unlink($this->testImage);
	    
	    $testImage = realpath('.' . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . 'berserkTestPNG.png');
	    if(!empty($testImage) && file_exists($testImage)) {
	        unlink($testImage);
	    }
	    
	    $testImage = realpath('.' . DIRECTORY_SEPARATOR . 'data'. DIRECTORY_SEPARATOR . 'berserkTestGIF.gif');
	    if(!empty($testImage) && file_exists($testImage)) {
	        unlink($testImage);
	    }
	    
	    $this->obj = null;
	}
}