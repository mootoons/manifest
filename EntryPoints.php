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

use MooToons\Manifest\Concerns\Enqueuable;
use MooToons\Manifest\Concerns\Conditional;

class EntryPoints
{
    use Enqueuable, Conditional;

    /**
     * @var array
     */
    protected $entrypoints = ['js' => [], 'css' => []];

    /** @var string */
    protected $id;

    /** @var string */
    protected $path;

    /** @var string */
    protected $uri;


    public function __construct(string $id, string $path, string $uri, array $entrypoints)
    {
        $this->id   = $id;
        $this->path = $path;
        $this->uri  = $uri;

        $this->entrypoints['js'] = $this->filterJs($entrypoints);
        $this->entrypoints['css'] = $this->filterCss($entrypoints);
    }

    /**
     * {@inheritDoc}
     */
    public function js(?callable $callable = null)
    {
        $scripts = $this->conditional ? $this->entrypoints['js'] : [];

        if (!$callable) {
            return collect($scripts);
        }

        collect($scripts)
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src), []);
            });

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function css(?callable $callable = null)
    {
        $styles = $this->conditional ? $this->entrypoints['css'] : [];

        if (!$callable) {
            return collect($styles);
        }

        collect($styles)
            ->each(function ($src, $handle) use ($callable) {
                $callable("{$this->id}/{$handle}", $this->getUrl($src));
            });

        return $this;
    }

    protected function filterJs(array $entrypoints): array
    {
        return $this->renameIndex(
            array_filter($entrypoints, fn ($f) => pathinfo($f, PATHINFO_EXTENSION) === 'js')
        );
    }

    protected function filterCss(array $entrypoints): array
    {
        return $this->renameIndex(
            array_filter($entrypoints, fn ($f) => pathinfo($f, PATHINFO_EXTENSION) === 'css')
        );
    }

    protected function renameIndex(array $filters): array
    {
        $data = [];
        foreach ($filters as $file) {
            $basename = basename($file);
            $index = explode('.', $basename)[0];
            $data[$index] = $file;
        }

        return $data;
    }

    /**
     * Get the entrypoints URL.
     *
     * @param  string $path
     * @return string
     */
    protected function getUrl(string $path): string
    {
        if (parse_url($path, PHP_URL_HOST)) {
            return $path;
        }

        $path = ltrim($path, '/');
        $uri = rtrim($this->uri, '/');

        return "{$uri}/{$path}";
    }
}
