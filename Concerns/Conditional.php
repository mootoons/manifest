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

trait Conditional
{
    /**
     * Conditionally load assets.
     *
     * @var bool
     */
    protected $conditional = true;

    /**
     * Set conditional loading.
     *
     * @param bool|callable $conditional
     * @return $this
     */
    public function when($conditional, ...$args)
    {
        $this->conditional = is_callable($conditional)
            ? call_user_func($conditional, $args)
            : $conditional;

        return $this;
    }
}
