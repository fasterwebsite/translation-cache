<?php
/*
Plugin Name: FasterWebsite Translations
Plugin URI: https://fasterwebsite.com/
Description: Speed up WordPress by optimizing loading of translation files (MO).
Author: Maciej Klepaczewski
Author URI: https://fasterwebsite.com/
Version: 0.2
Requires PHP: 7.4
*/
declare(strict_types=1);

use FasterWebsite\TranslationCache\CachedTranslations;
use FasterWebsite\TranslationCache\PhpFileTranslationCache;

new CachedTranslations(new PhpFileTranslationCache());
