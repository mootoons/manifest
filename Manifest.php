<?php

/**
 * MooToons\Manifest
 *
 * @package   MooToons\Manifest
 * @author    MooToon <support@mootoons.com>
 * @license   GPL v2 or later
 * @link      https://mootoons.com/
 */

namespace MooToons\Manifest;

if (!defined('ABSPATH')) {
    exit;
}

use Illuminate\Support\Arr;
use MooToons\Manifest\Concerns\ReadJson;
use MooToons\Manifest\Contracts\JsonFileNotFoundException;

class Manifest
{
    use ReadJson {
        load as traitLoad;
    }

    /** @var string */
    protected $url;

    /** @var string */
    protected $path;

    public function __construct(string $path, string $url, string $directory = 'dist')
    {
        $this->path = rtrim($path, '\/') . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR;
        $this->url  = rtrim($url, '\/') . "/{$directory}/";
    }

    /**
     * {@inheritDoc}
     */
    protected function getJsonPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR . 'asset-manifest.json';
    }

    /**
     * {@inheritDoc}
     */
    protected function load(string $file): array
    {
        try {
            return $this->traitLoad($file);
        } catch (JsonFileNotFoundException $e) {
            // We used to throw an exception here but it just causes confusion for new users.
            error_log($e->getMessage());
        }

        return [];
    }

    /**
     * Get a path dist asset-manifest.json.
     *
     * @return EntryPoints
     */
    public function entrypoints(string $id): EntryPoints
    {
        return new EntryPoints($id, $this->path, $this->url, $this->get('entrypoints'));
    }

    /**
     * Get a path dist asset-manifest.json key files.
     *
     * @return string|array|null
     */
    public function files(?string $file = null)
    {
        $files = array_map(
            fn ($f) => $this->url . trim(substr($f, strpos($f, '/dist/') + 6)),
            $this->get('files')
        );

        return $file ? Arr::get($files, $file, null) : $files;
    }
}
