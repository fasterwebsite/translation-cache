<?php
/**
 * @author Maciej Klepaczewski <matt@fasterwebsite.com>
 * @link https://fasterwebsite.com/
 * @copyright Copyright (c) 2021, Maciej Klepaczewski FasterWebsite.com
 */
declare(strict_types=1);

namespace FasterWebsite\TranslationCache;


interface TranslationCache {
    public function get(string $domain, string $moFile) : ?\MO;
    public function set(string $domain, string $moFile, \MO $mo) : void;
}
