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

namespace Pixelant\PxaVidyard\Resource\Rendering;

use \TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Vidyard renderer class
 */
class VidyardRenderer implements FileRendererInterface
{
    /**
     * @var OnlineMediaHelperInterface
     */
    protected $onlineMediaHelper;

    /**
     * Returns the priority of the renderer
     * This way it is possible to define/overrule a renderer
     * for a specific file type/context.
     * For example create a video renderer for a certain storage/driver type.
     * Should be between 1 and 100, 100 is more important than 1
     *
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }

    /**
     * Check if given File(Reference) can be rendered
     *
     * @param FileInterface $file File of FileReference to render
     * @return bool
     */
    public function canRender(FileInterface $file)
    {
        return ($file->getMimeType() === 'video/vidyard' || $file->getExtension() === 'vidyard') && $this->getOnlineMediaHelper($file) !== false;
    }

    /**
     * Get online media helper
     *
     * @param FileInterface $file
     * @return bool|OnlineMediaHelperInterface
     */
    protected function getOnlineMediaHelper(FileInterface $file)
    {
        if ($this->onlineMediaHelper === null) {
            $orgFile = $file;
            if ($orgFile instanceof FileReference) {
                $orgFile = $orgFile->getOriginalFile();
            }
            if ($orgFile instanceof File) {
                $this->onlineMediaHelper = OnlineMediaHelperRegistry::getInstance()->getOnlineMediaHelper($orgFile);
            } else {
                $this->onlineMediaHelper = false;
            }
        }
        return $this->onlineMediaHelper;
    }

    /**
     * Render for given File(Reference) html output
     *
     * @param FileInterface $file
     * @param int|string $width TYPO3 known format; examples: 220, 200m or 200c
     * @param int|string $height TYPO3 known format; examples: 220, 200m or 200c
     * @param array $options
     * @param bool $usedPathsRelativeToCurrentScript See $file->getPublicUrl()
     * @return string
     */
    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false)
    {
        $options = $this->collectOptions($options, $file);
        $src = $this->createVidyardUrl($options, $file);
        // $attributes = $this->collectIframeAttributes($width, $height, $options);

        GeneralUtility::makeInstance(AssetCollector::class)
            ->addJavaScript('vidyard', 'EXT:pxa_vidyard/Resources/Public/Js/Lib/vidyard.js', ['async' => 'async']);

        return sprintf(
            '<img class="vidyard-player-embed" src="https://play.vidyard.com/%s.jpg" data-uuid="%s"  data-v="4"  data-type="inline"/ data-autoplay="%s">',
            htmlspecialchars($src, ENT_QUOTES | ENT_HTML5),
            htmlspecialchars($src, ENT_QUOTES | ENT_HTML5),
            $options['autoplay']
        );
    }

    /**
     * @param array $options
     * @param FileInterface $file
     * @return array
     */
    protected function collectOptions(array $options, FileInterface $file)
    {
        // Check for an autoplay option at the file reference itself, if not overridden yet.
        if (!isset($options['autoplay']) && $file instanceof FileReference) {
            $autoplay = $file->getProperty('autoplay');
            if ($autoplay !== null) {
                $options['autoplay'] = $autoplay;
            }
        }

        return $options;
    }

    /**
     * @param array $options
     * @param FileInterface $file
     * @return string
     */
    protected function createVidyardUrl(array $options, FileInterface $file)
    {
        $videoId = $this->getVideoIdFromFile($file);

        $VidyardUrl = sprintf(
            '%s',
            rawurlencode($videoId)
        );

        return $VidyardUrl;
    }

    /**
     * @param FileInterface $file
     * @return string
     */
    protected function getVideoIdFromFile(FileInterface $file)
    {
        if ($file instanceof FileReference) {
            $orgFile = $file->getOriginalFile();
        } else {
            $orgFile = $file;
        }

        return $this->getOnlineMediaHelper($file)->getOnlineMediaId($orgFile);
    }

}
