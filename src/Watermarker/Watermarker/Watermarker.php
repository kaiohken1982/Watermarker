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
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::parseConfig()
	 */
	public function parseConfig($config) 
	{
    	if(isset($config['watermarkFile'])) {
			// This also call setWatermarkFullPath
			$this->openWatermark($config['watermarkFile']);
    	}
    	
    	if(isset($config['tmpDir'])) {
			$this->setWatermarkTmpDir($config['tmpDir']);
    	}
        
        return $this;
	}
    
    /**
     * Get the image info
     * 
     * @return array
     */
    public function getImageInfo() 
    {
        return $this->imageInfo;
    }
    
    /**
     * Set the image info
     * 
     * @return Watermark
     */
    public function setImageInfo($imageInfo) 
    {
        $this->imageInfo = $imageInfo;
	    
	    return $this;
    }
	
	/**
	 * Get the watermark image info
	 * 
	 * @return string
	 */
	public function getWatermarkInfo() 
	{
	    return $this->watermarkInfo;
	}
	
	/**
	 * Set the watermark image info
	 * 
	 * @return Watermark
	 */
	public function setWatermarkInfo($watermarkInfo) 
	{
	    $this->watermarkInfo = $watermarkInfo;
	    
	    return $this;
	}
	
	/**
	 * Get the image to be marked's full path
	 * 
	 * @return string
	 */
	public function getImageFullPath() 
	{
	    return $this->imageFullPath;
	}
	
	/**
	 * Set the image to be marked's full path
	 * 
	 * @return Watermark
	 */
	public function setImageFullPath($imageToBeMarked) 
	{
	    $this->imageFullPath = $imageToBeMarked;
	    
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
		$this->setImageFullPath($watermarkableImageFullPath);
		$this->thumbnailerService->open($watermarkableImageFullPath);
		$this->setImageInfo($this->thumbnailerService->getImageInfo());
		
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Watermarker\Watermarker\WatermarkerInterface::openWatermark()
	 */
	public function openWatermark($watermarkImageFullPath = null)
	{
		if (null !== $watermarkImageFullPath) {
		    $this->setWatermarkFullPath($watermarkImageFullPath);
		}
		$this->thumbnailerService->open($this->getWatermarkFullPath());
		$this->setWatermarkInfo($this->thumbnailerService->getImageInfo());
		
		$watermarkInfo = $this->getWatermarkInfo();
		
        if('image/gif' !== $watermarkInfo['mime']) {
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
	    $watermarkInfo = $this->getWatermarkInfo();
		if(null === $watermarkInfo) {
			$this->openWatermark();
		}
		
        // New height and width for the watermark and coords where to place it
        list($resizedWatermarkWidth, $resizedWatermarkHeight, $destX, $destY) = 
        	$this->watermarkDimensionCoords();
        
        // Creation of the temporary watermark for this image
        $watermarkTmpFile = $this->getWatermarkTmpDir() . 
        	"tempWatermark_" . md5(uniqid()) . "." . 
        	$watermarkInfo['extension'];
        
        // This must stay here to ensure that the watermark is the current resource of thumbnailer service
        $this->openWatermark();
        $this->thumbnailerService->resize($resizedWatermarkWidth, $resizedWatermarkHeight);
        $this->thumbnailerService->save($watermarkTmpFile);
        
        // We open the temporary watermark image and the image to watermark using GD libraries
        $watermark = imagecreatefromgif($watermarkTmpFile);
        
        // Creazione del nuovo file immagine
        $imageInfo = $this->getImageInfo();
        switch ($imageInfo['extension']) {
		case 'png':
			$image = @imagecreatefrompng($this->getImageFullPath());
			break;
        
		case 'jpg':
			$image = @imagecreatefromjpeg($this->getImageFullPath());
			break;
        
		case 'gif':
			$image = @imagecreatefromgif($this->getImageFullPath());
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
        
        switch ($imageInfo['extension']) {
		case 'png':
			$watermarkedImage = @imagepng($image, $this->getImageFullPath(), 0, null); // Corrupted image if NULL fourth parameter omitted!
			break;
        
		case 'jpg':
			$watermarkedImage = @imagejpeg($image, $this->getImageFullPath(), 100);
			break;
        
		case 'gif':
			$watermarkedImage = @imagegif($image, $this->getImageFullPath(), 100);
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
    public function watermarkDimensionCoords() 
    {
        $watermarkInfo = $this->getWatermarkInfo();
        $imageInfo = $this->getImageInfo();
        
        switch ($this->getType()) :
		case self::WATERMARK_TYPE_FULLWIDTH:
		default:
			$resizedWatermarkWidth = number_format($imageInfo['width'], 0, ',', '');
			$resizedWatermarkHeight = 
				number_format($resizedWatermarkWidth * $watermarkInfo['height'] / 
						$watermarkInfo['width'], 0, ',', '');
			$destX = 0;  
			$destY = number_format(($imageInfo['height']/2 - $resizedWatermarkHeight/2 ), 0, ',', '');
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
        $watermarkTmpDirCopy = $watermarkTmpDir;
        $watermarkTmpDir = realpath($watermarkTmpDir);
        
        // If the provided tmpDir is empty throw exception
        if (empty($watermarkTmpDir)) {
            throw new \RuntimeException("Watermark directory is empty. This can be due to the provided directory '" . $watermarkTmpDirCopy . "' not exists, please check.");
        }
        
        // Adding the trailing slash
        $watermarkTmpDir = substr($watermarkTmpDir, -1) == DIRECTORY_SEPARATOR ?
            $watermarkTmpDir : $watermarkTmpDir . DIRECTORY_SEPARATOR;
         
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
    	return $this->watermarkTmpDir;
    }
    
    /**
     * Set the watermark file
     *
     * @param string
     * @throws \RuntimeException
     * @return \Watermarker\Watermarker\Watermarker
     */
    public function setWatermarkFullPath($watermarkFullPath) 
    {
        $this->watermarkFullPath = realpath($watermarkFullPath);
        
        return $this;
    }
    
    /**
     * get the watermark file
     * 
     * @return string
     */
    public function getWatermarkFullPath() 
    {
        return $this->watermarkFullPath;
    }
}