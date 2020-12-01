<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Pixelant\PxaVidyard\Resource\OnlineMedia\Helpers;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Vidyard helper class
 */
class VidyardHelper extends AbstractOEmbedHelper
{
    /**
     * Get public url
     *
     * @param File $file
     * @param bool $relativeToCurrentScript
     * @return string|null
     */
    public function getPublicUrl(File $file, $relativeToCurrentScript = false)
    {
        $videoId = $this->getOnlineMediaId($file);
        return sprintf('https://play.vidyard.com/%s', rawurlencode($videoId));
    }

    /**
     * Get local absolute file path to preview image
     *
     * @param File $file
     * @return string
     */
    public function getPreviewImage(File $file)
    {
        $videoId = $this->getOnlineMediaId($file);
        $temporaryFileName = $this->getTempFolderPath() . 'vidyard_' . md5($videoId) . '.jpg';

        if (!file_exists($temporaryFileName)) {
            $previewImage = GeneralUtility::getUrl(
                sprintf('https://play.vidyard.com/%s.jpg', $videoId)
            );
            if ($previewImage !== false) {
                file_put_contents($temporaryFileName, $previewImage);
                GeneralUtility::fixPermissions($temporaryFileName);
            }
        }

        return $temporaryFileName;
    }

    /**
     * Try to transform given URL to a File
     *
     * @param string $url
     * @param Folder $targetFolder
     * @return File|null
     */
    public function transformUrlToFile($url, Folder $targetFolder)
    {
        $videoId = null;
        // Try to get the Vidyard code from given url.
        // These formats are supported with and without http(s)://
        // - vidyard.com/watch/<code> # Normal web link

        if (preg_match('/vidyard\.com\/(?:watch\/)?([0-9a-z\/]+)/i', $url, $matches)) {
            $videoId = $matches[1];
        }
        if (empty($videoId)) {
            return null;
        }

        return $this->transformMediaIdToFile($videoId, $targetFolder, $this->extension);
    }

    /**
     * Get oEmbed url to retrieve oEmbed data
     *
     * @param string $mediaId
     * @param string $format
     * @return string
     */
    protected function getOEmbedUrl($mediaId, $format = 'json')
    {
        return sprintf(
            'https://api.vidyard.com/dashboard/v1.1/oembed?url=%s&format=%s',
            rawurlencode(sprintf('https://play.vidyard.com/%s', rawurlencode($mediaId))),
            rawurlencode($format)
        );
    }
}
