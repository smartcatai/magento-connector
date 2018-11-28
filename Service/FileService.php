<?php
/**
 * Created by PhpStorm.
 * User: medic84
 * Date: 29.10.18
 * Time: 19:00
 */

namespace SmartCat\Connector\Magento\Service;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use \FilesystemIterator;
use SmartCat\Connector\Magento\Module;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use \ZipArchive;

class FileService
{
    private $filesystem;

    /**
     * FileService constructor.
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $directoryPath
     * @return FilesystemIterator
     */
    public function getDirectoryFiles($directoryPath)
    {
        $iterator = new FilesystemIterator(
            $directoryPath,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS
        );
        
        return $iterator;
    }

    /**
     * @param string $zipFile
     * @param string $extractPath
     * @return bool
     */
    public function unZip($zipFile, $extractPath)
    {
        if (!is_file($zipFile)) {
            throw new FileNotFoundException("File $zipFile not found");
        }

        if (is_dir($extractPath)) {
            @mkdir($extractPath, 0755, true);
        }

        $zip = new ZipArchive();
        $zip->open($zipFile);
        $result = $zip->extractTo($extractPath);
        $zip->close();

        return $result;
    }

    /**
     * @param null $relativePath
     * @param string $directoryCode
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getAbsolutePath($relativePath = null)
    {
        $media = $this->getWriteInterface(DirectoryList::VAR_DIR);
        
        if ($relativePath) {
            $path = Module::MODULE_FOLDER . '/' . $relativePath;

            return $media->getAbsolutePath($path);
        }
        
        return $media->getAbsolutePath(Module::MODULE_FOLDER);
    }

    /**
     * @return Filesystem\Directory\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getWriteInterface($directoryCode)
    {
        return $this->filesystem->getDirectoryWrite($directoryCode);
    }

    /**
     * @param $relativePath
     * @param $content
     * @return int
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function writeFile($relativePath, $content)
    {
        $media = $this->getWriteInterface(DirectoryList::VAR_DIR);
        $filePath = Module::MODULE_FOLDER . '/' . dirname($relativePath);
        $media->create($filePath);
        
        return $media->writeFile($filePath . '/' . basename($relativePath), $content);
    }
}