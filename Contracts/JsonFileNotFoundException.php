<?php

/**
 * MooToons\Manifest
 *
 * @package   MooToons\Manifest
 * @author    MooToon <support@mootoons.com>
 * @license   GPL v2 or later
 * @link      https://mootoons.com/
 */

namespace MooToons\Manifest\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

use Exception;

class JsonFileNotFoundException extends Exception
{
}
