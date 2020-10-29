<?php

namespace SimplyLink\UtilsBundle\Utils;

use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidFile;
use SimplyLink\UtilsBundle\Utils\Exceptions\SLExceptionUnexpectedValue;

/**
 * Class MediaManager
 *
 * MediaManager contains static functions for handling media directory structure for project
 *
 * @package SimplyLink\UtilsBundle\Utils
 */
class MediaManager extends FileUtils
{
    
    /**
     * Get main media folder path(server disk path) of the project 
     * 
     * @return string - folder path
     */
    public static function getMainMediaFolder()
    {
        return parent::getWebPath() . DIRECTORY_SEPARATOR . 'media';
    }

    /**
     * Get temp folder path(server disk path) in media folder of the project 
     * 
     * @return string - folder path
     */
    public static function getTempFolder()
    {
        return self::getMainMediaFolder() . DIRECTORY_SEPARATOR . 'tmp';
    }

    
    /**
     * Get images folder path(server disk path) in media folder of the project 
     * 
     * @return string - folder path
     */
    public static function getImagesMediaFolder()
    {
        return self::getMainMediaFolder() . DIRECTORY_SEPARATOR . 'images';
    }

  
    /**
     * Download file from url to media temp folder.
     * save the file with unique name and return the relative path.
     * 
     * @param string $fileUrl
     * @return string - new file path
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionUnexpectedValue
     * @throws SLExceptionInvalidFile
     */
    public static function saveTempFileFromUrl($fileUrl)
    {
        if(!is_string($fileUrl))
            throw SLExceptionInvalidArgument::expectedString('$fileUrl',$fileUrl);

        if(!GenericDataManager::isUrlValid($fileUrl))
            throw SLExceptionUnexpectedValue::expectedUrl('$fileUrl',$fileUrl);

        $tempFolder = self::getTempFolder();
        parent::createFolder($tempFolder);

        $ext = pathinfo($fileUrl, PATHINFO_EXTENSION);
        $newName = uniqid() . '.' . $ext;

        $newImgPath = $tempFolder . DIRECTORY_SEPARATOR . $newName;

        if(!parent::saveFileFromPath($newImgPath, $fileUrl))
        {
            return null;
        }

        return parent::subtractServerFolderFromPath($newImgPath);
    }
    
    /**
     * save the file with unique name to media temp folder and return the relative path.
     * 
     * @param string $content
     * @param string $fileExt
     * @return string - new file path
     * @throws SLExceptionInvalidArgument
     */
    public static function saveTempFileWithContent($content, $fileExt = '.txt')
    {
        parent::createFolder(self::getTempFolder());

        $newName = uniqid() . $fileExt;

        $newFilePath = self::getTempFolder() . '/' . $newName;

        if(file_put_contents($newFilePath, $content) === false)
        {
            return null;
        }

        return parent::subtractServerFolderFromPath($newFilePath);
    }
    
    
    /**
     * Save an image from base64 encoding
     *
     * @param string $base64String
     * @param string $outputFile Output file name including path
     * @return string
     */
    public static function saveBase64ToJpeg($base64String, $outputFile) {
        // open the output file for writing
        $ifp = fopen( $outputFile, 'wb' );
        
        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode( ',', $base64String );
        
        // we could add validation here with ensuring count( $data ) > 1
        
        
        fwrite( $ifp, base64_decode( end($data) ) );
        
        // clean up the file resource
        fclose( $ifp );
        
        return $outputFile;
    }


}
