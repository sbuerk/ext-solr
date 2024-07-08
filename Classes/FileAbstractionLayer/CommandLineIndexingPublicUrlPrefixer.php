<?php

namespace ApacheSolrForTypo3\Solr\FileAbstractionLayer;

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\Event\GeneratePublicUrlForResourceEvent;
use TYPO3\CMS\Core\Resource\ResourceInterface;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * TYPO provides public url prefixer event listener for the backend and the frontend context and leaving the
 * command line context to it own. Despite personal feelings TYPO3 has no change to correctly determine it
 * in that context. Due to the fact that the solr provides the ability to execute indexing as scheduler task
 * on the command line, the core listeners are not registered and thus not properly prefixing FAL resource urls.
 *
 * This class is a special implementation for the solr indexer, registered through the Symfony DI instead of adding
 * them during runtime like the TYPO3 register the be and fe listeners. The implementation is basically a clone from
 * the TYPO3 frontend listener, with the modification that the TypoScriptFrontendController is not fetched from the
 * global `$GLOBALS['TSFE']` and set within the IndexService constructor.
 *
 * Due to limiting it to cli usage only, this does not interfere indexing in the real frontend or simulated in the
 * backend.
 */
final class CommandLineIndexingPublicUrlPrefixer
{
    /**
     * Static property to avoid an infinite loop, because this listener is called when
     * public URLs are generated, but also calls public URL generation to obtain the
     * URL without prefix from the driver and possibly other listeners
     *
     * @var bool
     */
    private static bool $isProcessingUrl = false;

    private ?TypoScriptFrontendController $typoScriptFrontendController = null;

    public function prefixWithAbsRefPrefix(GeneratePublicUrlForResourceEvent $event): void
    {
        $controller = $this->typoScriptFrontendController;
        if (!Environment::isCli() || self::$isProcessingUrl || $controller === null) {
            return;
        }
        $resource = $event->getResource();
        if (!$this->isLocalResource($resource)) {
            return;
        }

        // Before calling getPublicUrl, we set the static property to true to avoid to be called in a loop
        self::$isProcessingUrl = true;
        try {
            $resource = $event->getResource();
            $originalUrl = $event->getStorage()->getPublicUrl($resource);
            if (!$originalUrl || PathUtility::hasProtocolAndScheme($originalUrl)) {
                return;
            }
            $event->setPublicUrl($controller->absRefPrefix . $originalUrl);
        } finally {
            self::$isProcessingUrl = false;
        }
    }

    private function isLocalResource(ResourceInterface $resource): bool
    {
        return $resource->getStorage()->getDriverType() === 'Local';
    }

    private function getCurrentFrontendController(): ?TypoScriptFrontendController
    {
        return $this->typoScriptFrontendController;
    }

    public function setCurrentFrontendController(TypoScriptFrontendController $typoScriptFrontendController): void
    {
        $this->typoScriptFrontendController = $typoScriptFrontendController;
    }
}
