<?php

/**
 * MooToons\Manifest
 *
 * @package   MooToons\Manifest
 * @author    MooToon <support@mootoons.com>
 * @license   GPL v2 or later
 * @link      https://mootoons.com/
 */

namespace MooToons\Manifest\Concerns;

if (!defined('ABSPATH')) {
    exit;
}

trait GenerateFileVersion
{
    /**
     * Remove the protocol from an http/https url.
     *
     * @param  string $url
     * @return string
     */
    protected function removeProtocol(string $url): string
    {
        return preg_replace('~^https?:~i', '', $url);
    }

    /**
     * Get if a url is external or not.
     *
     * @param  string  $url
     * @param  string  $homeUrl
     * @return bool
     */
    protected function isExternalUrl(string $url, string $homeUrl): bool
    {
        $delimiter = '~';
        $patternHomeUrl = preg_quote($homeUrl, $delimiter);
        $pattern = $delimiter . '^' . $patternHomeUrl . $delimiter . 'i';

        return !preg_match($pattern, $url);
    }

    /**
     * Generate a version for a given asset src.
     *
     * @param  string   $src
     * @return int|bool
     */
    protected function version(string $src)
    {
        // Normalize both URLs in order to avoid problems with http, https
        // and protocol-less cases.
        $src = $this->removeProtocol($src);
        $homeUrl = $this->removeProtocol(WP_CONTENT_URL);
        $version = false;

        if (!$this->isExternalUrl($src, $homeUrl)) {
            // Generate the absolute path to the file.
            $filePath = str_replace(
                [$homeUrl, '/'],
                [WP_CONTENT_DIR, DIRECTORY_SEPARATOR],
                $src
            );

            if (file_exists($filePath)) {
                // Use the last modified time of the file as a version.
                $version = filemtime($filePath);
            }
        }

        return $version;
    }
}
