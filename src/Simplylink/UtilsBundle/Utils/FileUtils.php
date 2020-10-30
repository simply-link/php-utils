<?php

namespace Simplylink\UtilsBundle\Utils;


use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidArgument;
use Simplylink\UtilsBundle\Utils\Exceptions\SLExceptionInvalidFile;

/**
 * Class FileUtils
 *
 * Utils function for handling files and directories
 *
 * @package Simplylink\UtilsBundle\Utils
 */
class FileUtils extends SLBaseUtils {

    /**
     * Get file mime - for ex: image/jpeg
     * 
     * @param string $filePath
     * @return string Return file mime type
     * @throws SLExceptionInvalidArgument
     */
    public static function getFileMimeType($filePath) {
        if(!is_string($filePath))
            throw SLExceptionInvalidArgument::expectedString('$filePath',$filePath);

        return mime_content_type($filePath);
    }

    /**
     * Get file extension by file mime type
     *
     * if file mime type not found - return "unknown"
     * 
     * @param string $filePath The path of the file
     * @return string Return file extension by file mime type
     * @throws SLExceptionInvalidArgument
     */
    public static function getExtensionByMimeType($filePath) {
        if(!is_string($filePath))
            throw SLExceptionInvalidArgument::expectedString('$filePath',$filePath);

        $mime_types = array(
            'text/plain' => 'txt',
            'text/html' => 'html',
            'text/css' => 'css',
            'application/javascript' => 'js',
            'application/json' => 'json',
            'application/xml' => 'xml',
            'application/x-shockwave-flash' => 'swf',
            'video/x-flv' => 'flv',
            // images
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/vnd.microsoft.icon' => 'ico',
            'image/tiff' => 'tiff',
            'image/svg+xml' => 'svg',
            // archives
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'application/x-msdownload' => 'exe',
            'application/vnd.ms-cab-compressed' => 'cab',
            // audio/video
            'audio/mpeg' => 'mp3',
            'video/quicktime' => 'mov',
            // adobe
            'application/pdf' => 'pdf',
            'image/vnd.adobe.photoshop' => 'psd',
            'application/postscript' => 'ai',
            // ms office
            'application/msword' => 'doc',
            'application/rtf' => 'rtf',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-powerpoint' => 'ppt',
            // open office
            'application/vnd.oasis.opendocument.text' => 'odt',
            'application/vnd.oasis.opendocument.spreadsheet' => 'ods'
        );

        $fileMime = self::getFileMimeType($filePath);


        if (array_key_exists($fileMime, $mime_types)) {
            return $mime_types[$fileMime];
        }

        return 'unknown';
    }

    /**
     * Get project base folder path in the server disk (absolute path)
     *
     * Built for symfony projects
     * 
     * @global \AppKernel $kernel
     * @param string $defaultFolder folder path in the main project folder
     * @return string Return the absolute path of the project folder
     */
    public static function getBasePath($defaultFolder = DIRECTORY_SEPARATOR) {
        $kernel = parent::getKernel();
        return str_replace(DIRECTORY_SEPARATOR . 'app', $defaultFolder, $kernel->getRootDir());
    }

    /**
     * Get base Web folder path in the server disk (absolute path)
     *
     * Built for symfony projects
     *
     * @global \AppKernel $kernel
     * @return string Return the absolute path of the project web folder
     */
    public static function getWebPath() {
        return self::getBasePath(DIRECTORY_SEPARATOR . 'web');
    }

    /**
     * Remove web folder from absolute path to file
     *
     * Use this function before saving files path to database.
     * Remove all absolute path from file
     * return only relative path to current project
     * 
     * @param string $path Path to file in the current server
     * @return string Return given path without the base path (server disk path)
     * @throws SLExceptionInvalidArgument
     */
    public static function subtractServerFolderFromPath($path) {
        if(!is_string($path))
            throw SLExceptionInvalidArgument::expectedString('$path',$path);

        return str_replace(self::getWebPath(), '', $path);
    }

    /**
     * create folders structure (if not exists) 
     * 
     * @param string $structure structure of folders to create
     * @return boolean Folder creation success
     * @throws SLExceptionInvalidArgument
     */
    public static function createFolder($structure) {
        if(!is_string($structure))
            throw SLExceptionInvalidArgument::expectedString('$structure',$structure);


        // To create the nested structure, the $recursive parameter 
        // to mkdir() must be specified.
        if (file_exists($structure)) {
            return true;
        }

        return mkdir($structure, 0777, true);
    }

    /**
     * Delete file by absolute path (server disk path)
     * 
     * @param string $filePath Absolute path to file
     * @return boolean File delete success
     * @throws SLExceptionInvalidArgument
     */
    public static function deleteFile($filePath) {
        if(!is_string($filePath))
            throw SLExceptionInvalidArgument::expectedString('$filePath',$filePath);

        return unlink($filePath);
    }

    /**
     * copy file from source to target folder
     *
     * @param string $target Absolute path to target folder where the file should be saved
     * @param string $source Absolute path to the file should copy
     * @return bool Save file success
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionInvalidFile
     */
    public static function saveFileFromPath($target, $source) {

        if(!is_string($source))
            throw SLExceptionInvalidArgument::expectedString('$source',$source);

        if(!is_string($target))
            throw SLExceptionInvalidArgument::expectedString('$target',$target);

        $content = file_get_contents($source);

        if ($content === FALSE) {
            throw  SLExceptionInvalidFile::getContentError($source);
        }

        if(file_put_contents($target, $content) === false)
            return false;

        return true;
    }


    /**
     * Get the latest file name which was created/modified in a folder
     * 
     * @param string $pathFolder Absolute path to folder to scan
     * @param string $typeTime 'create' or 'modified' (create by default)
     * @param string $extension type extension file ('*.*' by default)
     * @return string if file exist, else null
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionInvalidFile
     */
    public static function getLastCreateOrModifiedFile($pathFolder, $typeTime = 'create', $extension = '*.*') {

        if(!is_string($pathFolder))
            throw SLExceptionInvalidArgument::expectedString('$pathFolder',$pathFolder);

        $latest_file_time = null;
        $latest_filename = '';
        if (file_exists($pathFolder)) {
            $latest_file_time = '';
            $type_time = ($typeTime == 'create') ? 'filectime' : 'filemtime';
            $d = dir($pathFolder);
            while (false !== ($entry = $d->read())) {
                $filePath = "{$pathFolder}/{$entry}";
                if (is_file($filePath) && $type_time($filePath) > $latest_file_time) {
                    $latest_file_time = $type_time($filePath);
                    $latest_filename = $entry;
                }
            }
        }
        else
        {
            throw  SLExceptionInvalidFile::getFileExistsError($pathFolder);
        }

        return ($latest_file_time) ? $pathFolder . '/' . $latest_filename : null;
    }


    /**
     * Get all files in folder
     *
     *
     *
     * @param string $directory Directory absolute path
     * @param string $extension get only file with specific extension - for example: ".pdf"
     * @return array Return array of file names
     * @throws SLExceptionInvalidArgument
     * @throws SLExceptionInvalidFile
     */
    public static function getAllFilesInDirectory($directory, $extension = "")
    {
        if(!is_string($directory))
            throw SLExceptionInvalidArgument::expectedString('$directory',$directory);


        if (!file_exists($directory))
            throw  SLExceptionInvalidFile::getFileExistsError($directory);

        $files = scandir($directory);

        if(strlen($extension) > 0)
        {
            $filesTmp = [];
            foreach ($files as $file)
            {
                if(GenericDataManager::stringEndsWith($file,$extension))
                    $filesTmp[] = $file;
            }
            $files = $filesTmp;
        }

        return $files;
    }


}
