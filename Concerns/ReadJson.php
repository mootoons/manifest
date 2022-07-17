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

use Illuminate\Support\Arr;
use MooToons\Manifest\Contracts\JsonFileInvalidException;
use MooToons\Manifest\Contracts\JsonFileNotFoundException;

trait ReadJson
{
    /**
     * Cache.
     *
     * @var array|null
     */
    protected $cache = null;

    /**
     * Get the path to the JSON that should be read.
     *
     * @return string
     */
    abstract protected function getJsonPath(): string;

    /**
     * Load the json file.
     *
     * @param string $file
     *
     * @return array
     */
    protected function load(string $file): array
    {
        /** @var \WP_Filesystem_Base $wp_filesystem */
        global $wp_filesystem;

        require_once ABSPATH . '/wp-admin/includes/file.php';

        WP_Filesystem();

        if (!$wp_filesystem->exists($file)) {
            throw new JsonFileNotFoundException(
                sprintf(
                    __('The required %s file is missing.', 'mootoon-starter'),
                    basename($file)
                )
            );
        }

        $contents = $wp_filesystem->get_contents($file);
        $json = json_decode($contents, true);
        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            throw new JsonFileInvalidException(
                sprintf(
                    __('The required %1$s file is not valid JSON (error code %2$s).', 'mootoon-starter'),
                    basename($file),
                    $jsonError

                )
            );
        }

        return $json;
    }

    /**
     * Get the entire json array.
     *
     * @return array
     */
    public function getAll(): array
    {
        if ($this->cache === null) {
            $this->cache = $this->load($this->getJsonPath());
        }

        return $this->cache;
    }

    /**
     * Get a json value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->getAll(), $key, $default);
    }
}
