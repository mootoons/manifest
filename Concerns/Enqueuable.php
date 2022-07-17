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

use Illuminate\Support\Collection;

trait Enqueuable
{
    use GenerateFileVersion;

    /**
     * Resolved inline sources.
     *
     * @var array
     */
    protected static $inlined = [];

    /**
     * Get JS files in entrypoints.
     *
     * Optionally pass a function to execute on each JS file.
     *
     * @param callable $callable
     * @return Collection|$this
     */
    abstract public function js(?callable $callable = null);

    /**
     * Get CSS files in entrypoints.
     *
     * Optionally pass a function to execute on each CSS file.
     *
     * @param callable $callable
     * @return Collection|$this
     */
    abstract public function css(?callable $callable = null);

    /**
     * Enqueue CSS files in WordPress.
     *
     * @param string $media
     * @param array $dependencies
     * @return $this
     */
    public function enqueueCss(string $media = 'all', array $dependencies = [])
    {
        $this->css(function (string $handle, string $src) use (&$dependencies, $media) {
            wp_enqueue_style($handle, $src, $dependencies, $this->version($src), $media);
            $this->mergeDependencies($dependencies, [$handle]);
        });

        return $this;
    }

    /**
     * Enqueue JS files in WordPress.
     *
     * @param bool $inFooter
     * @param array $dependencies
     * @return $this
     */
    public function enqueueJs(bool $inFooter = true, array $dependencies = [])
    {
        $this->js(
            function (string $handle, string $src, array $entrypointDependencies) use (&$dependencies, $inFooter) {
                $this->mergeDependencies($dependencies, $entrypointDependencies);

                wp_enqueue_script($handle, $src, $dependencies, $this->version($src), $inFooter);

                $this->mergeDependencies($dependencies, [$handle]);
            }
        );

        return $this;
    }

    /**
     * Enqueue JS and CSS files in WordPress.
     *
     * @return $this
     */
    public function enqueue()
    {
        return $this->enqueueCss()->enqueueJs();
    }

    /**
     * Dequeue CSS files in WordPress.
     *
     * @return $this
     */
    public function dequeueCss()
    {
        $this->css(function (string $handle) {
            wp_dequeue_style($handle);
        });

        return $this;
    }

    /**
     * Dequeue JS files in WordPress.
     *
     * @return $this
     */
    public function dequeueJs()
    {
        $this->js(function (string $handle) {
            wp_dequeue_script($handle);
        });

        return $this;
    }

    /**
     * Dequeue JS and CSS files in WordPress.
     *
     * @return $this
     */
    public function dequeue()
    {
        return $this->dequeueCss()->dequeueJs();
    }

    /**
     * Add an inline script before or after the entrypoints loads
     *
     * @param string $contents
     * @param string $position
     * @return $this
     */
    public function inline(string $contents, string $position = 'after')
    {
        $entrypoints = $this->entrypoints['js'] ?? [];

        if (!$handles = array_keys($entrypoints)) {
            return $this;
        }

        $handle = "{$this->id}/" . ($position === 'after'
            ? array_pop($handles)
            : array_shift($handles)
        );

        wp_add_inline_script($handle, $contents, $position);

        return $this;
    }

    /**
     * Add localization data to be used by the entrypoints
     *
     * @param string $name
     * @param array $object
     * @return $this
     */
    public function localize(string $name, array $object)
    {
        if (!$handles = array_keys($this->entrypoints['js'] ?? [])) {
            return $this;
        }

        $handle = "{$this->id}/{$handles[0]}";
        wp_localize_script($handle, $name, $object);

        return $this;
    }

    /**
     * Merge two or more arrays.
     *
     * @param array $dependencies
     * @param array $moreDependencies
     * @return void
     */
    protected function mergeDependencies(array &$dependencies, array ...$moreDependencies)
    {
        $dependencies = array_unique(array_merge($dependencies, ...$moreDependencies));
    }

    /**
     * Reset inlined sources.
     *
     * @internal
     * @return void
     */
    public static function resetInlinedSources()
    {
        self::$inlined = [];
    }
}
