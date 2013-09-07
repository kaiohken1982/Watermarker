<?php 
namespace Watermarker\Service;

use Watermarker\Watermarker\Watermarker;
use Zend\ServiceManager\ServiceLocatorInterface,
	Zend\ServiceManager\FactoryInterface;

class WatermarkerFactory
	implements FactoryInterface
{
    /**
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return \Watermarker\Watermarker\Watermarker
     */
    public function createService(ServiceLocatorInterface $sl)
    {
		$config = $sl->get('Configuration');
        $watermarkerConfig = isset($config['watermarker']) ? $config['watermarker'] : array();
        $watermarker = new Watermarker($sl->get('Thumbnailer'));
        $watermarker->parseConfig($watermarkerConfig);
		
		return $watermarker;
    }
}