<?php

/**
 * Keeps the administrator favicon independent from the Atum core template.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

final class PlgSystemAdminfavicon extends CMSPlugin
{
    protected $app;

    public function onBeforeCompileHead(): void
    {
        if (!$this->app->isClient('administrator')) {
            return;
        }

        $document = $this->app->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $headData = $document->getHeadData();

        foreach ($headData['links'] ?? [] as $url => $attributes) {
            $relation = strtolower((string) ($attributes['relation'] ?? ''));

            if (in_array($relation, ['icon', 'alternate icon', 'mask-icon'], true)) {
                unset($headData['links'][$url]);
            }
        }

        $document->setHeadData($headData);
        $document->addFavicon(
            Uri::root(true) . '/images/utama/favicon.png',
            'image/png'
        );
    }
}

