<?php
namespace Watermarker\Watermarker;

/**
 * Watermark an image
 * 
 * @Version    2.0
 * @author     Sergio Rinaudo
 */
class Watermarker 
	implements WatermarkerInterface
{
    /**
     * @const watermark types
     */
    const WATERMARK_TYPE_FULLWIDTH = 1;
    
	/**
	 * The thumnailer service
	 * @var object
	 */
	private $thumbnailerService;
	
	/**
	 * Watermark temporary directory
	 * @var string
	 */
	private $watermarkTmpDir;
	
	/**
	 * Watermark full path
	 * @var string
	 */
	private $watermarkFullPath;
	
	/**
	 * Info of the watermark image
	 * @var null|array
	 */
	private $watermarkInfo;
	
	/**
	 * Full path of the image that will be watermarkeds
	 * @var string
	 */
	private $imageFullPath;
	
	/**
	 * Info of the image to be watermarked
	 * @var null|array
	 */
	private $imageInfo;
	
	/**
	 * The watermark type
	 * @var int
	 */
	private $type;
	
	/**
	 * Class construct
	 * Set the thumbnailer service.
	 * 
	 * @param object $thumbnailerService
	 */
	public function __construct($thumbnailerService) 
	{
		$this->thumbnailerService = $thumbnailerService;
		$this->setType(self::WATERMARK_TYPE_FULLWIDTH);
		$this->setWatermarkTmpDir(dirname(__DIR__) . '/../../data/');
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::parseConfig()
	 */
	public function parseConfig($config) 
	{
    	if(isset($config['watermarkFile'])) {
			$this->setWatermarkFile($config['watermarkFile']);
    	}
    	
    	if(isset($config['tmpDir'])) {
			$this->setWatermarkTmpDir($config['tmpDir']);
    	}
        
        return $this;
	}
	
	/**
	 * Get the watermark type
	 * 
	 * @return int
	 */
	public function getType() 
	{
		return $this->type;
	}
	
	/**
	 * Set the watermark type
	 * 
	 * @return \Watermarker\Watermarker\Watermarker
	 */
	public function setType($type) 
	{
		$this->type = $type;
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::openImage()
	 */
	public function openImage($watermarkableImageFullPath) 
	{
		$this->imageFullPath = $watermarkableImageFullPath;
		$this->thumbnailerService->open($watermarkableImageFullPath);
		$this->imageInfo = $this->thumbnailerService->getImageInfo();
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::openWatermark()
	 */
	public function openWatermark($watermarkImageFullPath = null)
	{
		$watermarkFile = null === $watermarkImageFullPath ? 
			$this->watermarkFullPath : $watermarkImageFullPath;
		$this->setWatermarkFile($watermarkFile);
		$this->thumbnailerService->open($watermarkFile);
		$this->watermarkInfo = $this->thumbnailerService->getImageInfo();
		
        if('image/gif' !== $this->watermarkInfo['mime']) {
            throw new \RuntimeException("Watermark is required to be of the mimetype image/gif, passed watermark is of mimetype '" . $this->watermarkInfo['mime'] . "'");
        }
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::watermark()
	 */
	public function watermark($opacity = 30) 
	{
		// This means that user didn't explicitly call it, use config instead
		if(null === $this->watermarkInfo) {
			$this->openWatermark();
		}
		
        // New height and width for the watermark and coords where to place it
        list($resizedWatermarkWidth, $resizedWatermarkHeight, $destX, $destY) = 
        	$this->watermarkDimensionCoords();
        
        // Creation of the temporary watermark for this image
        $watermarkTmpFile = $this->getWatermarkTmpDir() . 
        	"tempWatermark_" . md5(uniqid()) . "." . 
        	$this->watermarkInfo['extension'];
        
        $this->thumbnailerService->resize($resizedWatermarkWidth, $resizedWatermarkHeight);
        $this->thumbnailerService->save($watermarkTmpFile);
        
        // We open the temporary watermark image and the image to watermark using GD libraries
        $watermark = imagecreatefromgif($watermarkTmpFile);
        
        // Creazione del nuovo file immagine
        switch ($this->imageInfo['extension']) {
		case 'png':
			$image = @imagecreatefrompng($this->imageFullPath);
			break;
        
		case 'jpg':
			$image = @imagecreatefromjpeg($this->imageFullPath);
			break;
        
		case 'gif':
			$image = @imagecreatefromgif($this->imageFullPath);
			break;
        
		default:
			throw new \RuntimeException('Not supported image type');
			break;
        }
        
        // This is where the watermarking happens
        imagecopymerge(
        	$image, $watermark, $destX, 
        	$destY, 0, 0, $resizedWatermarkWidth, 
        	$resizedWatermarkHeight, $opacity
       	);
        
        // We overwrite the passed image with this new watermarked image
        switch ($this->imageInfo['extension']) {
		case 'png':
			$watermarkedImage = @imagepng($image, $this->imageFullPath, 0, null); // Corrupted image if NULL fourth parameter omitted!
			break;
        
		case 'jpg':
			$watermarkedImage = @imagejpeg($image, $this->imageFullPath, 100);
			break;
        
		case 'gif':
			$watermarkedImage = @imagegif($image, $this->imageFullPath, 100);
			break;
        }
        
        // Destruction of the images from memory
        @imagedestroy($watermark);
        @imagedestroy($image);
        
        return $watermarkedImage;
	}
    
    /**
     * Get watermark type dimension from a type
     *
     * @param array
     * @param array
     * @param int
     * @return array
     */
    protected function watermarkDimensionCoords() 
    {
        switch ($this->getType()) :
		case self::WATERMARK_TYPE_FULLWIDTH:
		default:
			$resizedWatermarkWidth = number_format($this->imageInfo['width'], 0, ',', '');
			$resizedWatermarkHeight = 
				number_format($resizedWatermarkWidth * $this->watermarkInfo['height'] / 
						$this->watermarkInfo['width'], 0, ',', '');
			$destX = 0;  
			$destY = number_format(($this->imageInfo['height']/2 - $resizedWatermarkHeight/2 ), 0, ',', '');
            break;
        endswitch;
        
        return array($resizedWatermarkWidth, $resizedWatermarkHeight, $destX, $destY);
    }
    
    /**
     * Set the watermark directory
     *
     * @param string
     * @throws \RuntimeException
     * @return \Watermarker\Watermarker\Watermarker
     */
    public function setWatermarkTmpDir($watermarkTmpDir) 
    {
        $watermarkTmpDir = substr( $watermarkTmpDir, -1 ) == '/' ? 
        	$watermarkTmpDir : $watermarkTmpDir . '/';
        
        if (!is_dir($watermarkTmpDir)) {
            if (!mkdir($watermarkTmpDir)) {
                throw new \RuntimeException("Impossible to create dir '" . $watermarkTmpDir . "'. Please check permissions");
            }
            
            if (!is_writable( $watermarkTmpDir)) {
                throw new \RuntimeException("Dir '" . $watermarkTmpDir . "' is *not* writeable. Please check permissions");
            }
        }
         
        $this->watermarkTmpDir = $watermarkTmpDir;
        
        return $this;
    }
    
    /**
     * Get watermark tmp dir
     * 
     * @return string
     */
    public function getWatermarkTmpDir() 
    {
    	return realpath($this->watermarkTmpDir) . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Set the watermark file
     *
     * @param string
     * @throws \RuntimeException
     * @return \Watermarker\Watermarker\Watermarker
     */
    public function setWatermarkFile($watermarkFullPath) 
    {
        $this->watermarkFullPath = $watermarkFullPath;
        
        return $this;
    }
    
    /**
     * get the watermark file
     * 
     * @return string
     */
    public function getWatermarkFile() 
    {
        return $this->watermarkFullPath;
    }
}