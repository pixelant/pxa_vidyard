<?php

defined('TYPO3_MODE') or die();

// Register your own online video service (the used key is also the bind file extension name)
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers']['vidyard'] = \Pixelant\PxaVidyard\Resource\OnlineMedia\Helpers\VidyardHelper::class;

$rendererRegistry = \TYPO3\CMS\Core\Resource\Rendering\RendererRegistry::getInstance();
$rendererRegistry->registerRendererClass(
    \Pixelant\PxaVidyard\Resource\Rendering\VidyardRenderer::class
);

// Register an custom mime-type for your videos
$GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType']['vidyard'] = 'video/vidyard';

// Register your custom file extension as allowed media file
$GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] .= ',vidyard';
