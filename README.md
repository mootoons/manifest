# Wordpress-Manifest

Wordpress packages

Package: Manifest ใช้สำหรับ react หรือ laravel mix

```php
use MooToons\Manifest\Manifest;

$manifest = new Manifest(plugin_dir_path(__FILE__), plugin_dir_url(__FILE__));
$manifest->entrypoints('my-plugin')->enqueue();

```
