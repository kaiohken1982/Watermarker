<?php 
namespace WatermarkerTest\Service; 

use WatermarkerTest\Bootstrap;
use WatermarkerTest\Util\ServiceManagerFactory;
use Watermarker\Service\WatermarkerFactory;
use PHPUnit_Framework_TestCase;
use Watermarker\Watermarker\Watermarker;
use Zend\ServiceManager\FactoryInterface;

class WatermarkerFactoryTest 
	extends PHPUnit_Framework_TestCase
{
    /**
     * The objet to be tested
     * @var FactoryInterface
     */
	protected $obj;
	
	/**
	 * This is called once for every test function.
	 * This is reset by tearDown for each test.
	 */
	protected function setUp()
	{
	    $this->obj = new WatermarkerFactory();
	}
	
	/**
	 * @covers \Watermarker\Service\WatermarkerFactory::createService()
	 */
	public function testCreateService() 
	{
	    $service = $this->obj->createService(Bootstrap::getServiceManager());
	    $this->assertInstanceOf('\Watermarker\Watermarker\Watermarker', $service);
	}
}