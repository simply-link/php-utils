<?php

namespace SimplyLink\UtilsBundle\Utils;

use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionRuntime;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionUnexpectedValue;

/**
 * Class ImageUtils
 *
 * ImageUtils contains static functions for handling images manipulations
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class ImageUtils extends FileUtils
{
    
    /**
     * convert image to png file - delete the old file and create new one as png, return the new file path
     * 
     * @param string $imagePath
     * @return string - new path
     * @throws SLExceptionInvalidArgument
     */
    public static function convertImageToPng($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        if(self::getImageType($imagePath) == IMAGETYPE_PNG)
        {
            return $imagePath;
        }
        $newPath = substr($imagePath, 0, -strlen(pathinfo($imagePath, PATHINFO_EXTENSION))).'png';
        if(!imagepng(imagecreatefromstring(file_get_contents($imagePath)), $newPath))
        {
            parent::getLogger()->notice(__METHOD__ . '() line: ' . __LINE__ . ', imagepng() failed');
            return $imagePath;
        }
        parent::deleteFile($imagePath); // delete old file
        return $newPath;
    }
    
    /**
     * Convert image to square image.
     * Fill pixels in the same color as image background color.
     * Replace the existing image file with the new one.
     *
     * @param string $imagePath
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionRuntime
     * @throws SLExceptionUnexpectedValue
     */
    public static function convertImageToSquare($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        $imageSize = self::getImageSize($imagePath);
        if($imageSize)
        {
            $width = $imageSize['width'];
            $height = $imageSize['height'];
            
            if($width == $height) 
            {
                return; // image already is square
            }
            
            $backgroundColorArray = self::getImageBackgroundColor($imagePath);
            
            $squareSize = ($width > $height) ? $width : $height;
            $src = self::getImageDataByMime($imagePath);
            $canvas = imagecreatetruecolor($squareSize, $squareSize);
            $canvasColor = imagecolorallocate($canvas, $backgroundColorArray['red'], $backgroundColorArray['green'], $backgroundColorArray['blue']);
            if(!imagefill($canvas, 0, 0, $canvasColor))
                throw new SLExceptionRuntime('imagefill() failed','unexpected error, please try again later');
            $locationX = ($width > $height) ? 0 : ($height - $width)/2;
            $locationY = ($width > $height) ? ($width-$height)/2 : 0;
            
            if(imagecopy($canvas, $src, $locationX, $locationY, 0, 0, $width, $height))
            {
                // success
                if(imagepng($canvas, $imagePath))
                {
                    throw new SLExceptionRuntime('imagepng() failed','unexpected error, please try again later');
                }

            }
            else
            {
                throw new SLExceptionRuntime('imagecopy() failed','unexpected error, please try again later');
            }
            
        }
    }
     
    /**
     * get image size in pixels - if fail return null
     * 
     * @param string $imagePath
     * @return null|array - [width,height]
     * @throws SLExceptionInvalidArgument
     */
    public static function getImageSize($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        $arr = getimagesize($imagePath);

        if(count($arr) >= 2)
        {
            $width = $arr[0];
            $height = $arr[1];
            return ['width' => $width, 'height' => $height];
        }
        return null;
    }
    
    /**
     * Get image background color - if fail return null
     * 
     * @param string $imagePath
     * @return array - array of 4 items [red,green,blue,alpha].
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     */
    public static function getImageBackgroundColor($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        $imageSize = self::getImageSize($imagePath);
        if($imageSize)
        {
            $width = $imageSize['width'];
            $height = $imageSize['height'];
            
            $topLeftColor = self::getPixelColorForImage($imagePath, 0, 0);
            $bottomLeftColor = self::getPixelColorForImage($imagePath, 0, $height-1);
            $topRightColor = self::getPixelColorForImage($imagePath, $width-1, 0);
            $bottomRightColor = self::getPixelColorForImage($imagePath, $width-1, $height-1);
            
            return self::getAvgColor([$topLeftColor,$bottomLeftColor,$topRightColor,$bottomRightColor]);
            
        }
        return null;
    }
    
    /**
     * get average color of multiple colors
     * 
     * @param array $colorsArrays - array of colors array [red,green,blue,alpha]
     * @return array - array of 4 items [red,green,blue,alpha].
     */
    private static function getAvgColor($colorsArrays)
    {
        $finalColorsValue = ['red'=>0,'green'=>0,'blue'=>0,'alpha'=>0];
        if(count($colorsArrays) > 0)
        {
            foreach ($colorsArrays as $color) 
            {
                $finalColorsValue['red'] += $color['red'];
                $finalColorsValue['green'] += $color['green'];
                $finalColorsValue['blue'] += $color['blue'];
                $finalColorsValue['alpha'] += $color['alpha'];
            }

            $finalColorsValue['red'] /= count($colorsArrays);
            $finalColorsValue['green'] /= count($colorsArrays);
            $finalColorsValue['blue'] /= count($colorsArrays);
            $finalColorsValue['alpha'] /= count($colorsArrays);
        }
        return $finalColorsValue;
    }
    
    /**
     * check if 2 colors are matching (with some tolerance)
     * 
     * @param array $colorA - color array [red,green,blue,alpha]
     * @param array $colorB - color array [red,green,blue,alpha]
     * @param int $tolerance 
     * @return boolean - colors are matching
     */
    public static function checkColorMatching($colorA,$colorB,$tolerance = 0)
    {
        $totalTolerance = 0;
        $totalTolerance += abs($colorA['red'] - $colorB['red']);
        $totalTolerance += abs($colorA['green'] - $colorB['green']);
        $totalTolerance += abs($colorA['blue'] - $colorB['blue']);
        $totalTolerance += abs($colorA['alpha'] - $colorB['alpha']);
        return $totalTolerance <= $tolerance;
    }
    
    /**
     * check the RGBA color of pixel in image.
     * each RGBA value is between 0 - 255.
     * 
     * @param string $imagePath
     * @param int $x
     * @param int $y
     * @return array - array of 4 items [red,green,blue,alpha].
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     */
    private static function getPixelColorForImage($imagePath,$x,$y)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        if(!is_int($x))
            throw SLExceptionInvalidArgument::expectedInt('$x',$x);

        if($x < 0)
            throw SLExceptionUnexpectedValue::expectedPositiveNumber('$x',$x);

        if(!is_int($y))
            throw SLExceptionInvalidArgument::expectedInt('$y',$y);


        if($y < 0)
            throw SLExceptionUnexpectedValue::expectedPositiveNumber('$y',$y);

        $imgData = self::getImageDataByMime($imagePath);
	    $colorRGB = imagecolorat($imgData,$x,$y);
        $colors = imagecolorsforindex($imgData, $colorRGB);
        return $colors;
    }
    
    
    
    /**
     * Get image type - for ex: IMAGETYPE_JPEG
     * if fail - return null
     * 
     * @param string $imagePath
     * @return integer - image type
     * @throws SLExceptionInvalidArgument
     */
    public static function getImageType($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);


        $arr = getimagesize($imagePath);
        if($arr && count($arr)>=3)
        {
            return $arr[2];
        }
        return null;
    }
    
    /**
     * get the correct extension by reading the file mime type
     * 
     * @param string $imagePath
     * @return string|null - image extension
     * @throws SLExceptionInvalidArgument
     */
    public static function getImageExtensionByMime($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);


        $type = self::getImageType($imagePath);
        if($type)
        {
            switch ($type)
            {
                case IMAGETYPE_GIF:
                    return 'gif';
                case IMAGETYPE_ICO:
                    return 'ico';
                case IMAGETYPE_JPEG:
                    return 'jpg';
                case IMAGETYPE_PNG:
                    return 'png';
            }
        }
        return null;
    }
    
    /**
     * Get image data by mime type.
     * if fail - return null
     * 
     * @param string $imagePath
     * @return resource|null an image resource identifier on success
     * @throws SLExceptionInvalidArgument
     */
    public static function getImageDataByMime($imagePath)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        $type = self::getImageType($imagePath);
        if($type)
        {
            switch ($type)
            {
                case IMAGETYPE_GIF:
                    return imagecreatefromgif($imagePath);
                case IMAGETYPE_JPEG:
                    return imagecreatefromjpeg($imagePath);
                case IMAGETYPE_PNG:
                    return imagecreatefrompng($imagePath);
            }
        }
        return null;
    }
    
    /**
     * scale down image to maximum size
     * 
     * @param string $imagePath
     * @param int $maxSizeAllowed - max size allowed to image
     * @return boolean success
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     */
    public static function resizeImageToLimitedSize($imagePath, $maxSizeAllowed)
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        $imageSize = self::getImageSize($imagePath);
        if(!$imageSize)
        {
            return false;
        }
        
        $width = $imageSize['width'];
        $height = $imageSize['height'];
        
        $widthPercentage = $width / $maxSizeAllowed;
        $heightPercentage = $height / $maxSizeAllowed;
        $percentage = max($widthPercentage,$heightPercentage);
        
        if($percentage <= 1.0)
        {
            // no need to resize
            return true;
        }   
        
        if($widthPercentage > $heightPercentage)
        {
            self::resizeImage($imagePath,$maxSizeAllowed / $width);
            return true;
        }
        else
        {
            self::resizeImage($imagePath,$maxSizeAllowed / $height);
            return true;
        }
        
    }
    
    /**
     * resize image file by percentage
     * replace image file with new resized image.
     * 
     * @param string $imagePath
     * @param float $percentage
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     */
    public static function resizeImage($imagePath,$percentage) 
    {
        if(!is_string($imagePath))
            throw SLExceptionInvalidArgument::expectedString('$imagePath',$imagePath);

        if(!is_float($percentage))
            throw SLExceptionInvalidArgument::expectedFloat('$percentage',$percentage);

        if($percentage<=0)
            throw SLExceptionUnexpectedValue::expectedPositiveNumber('$percentage',$percentage);

        $size = self::getImageSize($imagePath);
        if($size && count($size)>=2)
        {
            $width  = $size['width'];
            $height = $size['height'];

            $rs_width  = $width * $percentage;
            $rs_height = $height * $percentage;


            $img = NULL;

            switch ($size['mime']) {
               case "image/gif":
                  $img = imagecreatefromgif($imagePath);
                  break;
               case "image/jpeg":
                  $img = imagecreatefromjpeg($imagePath);
                  break;
               case "image/png":
                  $img = imagecreatefrompng($imagePath);
                  break;
            }

            $img_base = imagecreatetruecolor($rs_width, $rs_height);
            imagecopyresized($img_base, $img, 0, 0, 0, 0, $rs_width, $rs_height, $width, $height);

            switch ($size['mime']) {
               case "image/gif":
                  imagegif($img_base, $imagePath);
                  break;
               case "image/jpeg":
                  imagejpeg($img_base, $imagePath);
                  break;
               case "image/png":
                  imagepng($img_base, $imagePath);
                  break;
            }
        }
    }
}
