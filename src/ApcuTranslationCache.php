<?php
/**
 * @author Maciej Klepaczewski <matt@fasterwebsite.com>
 * @link https://fasterwebsite.com/
 * @copyright Copyright (c) 2021, Maciej Klepaczewski FasterWebsite.com
 */
declare(strict_types=1);

namespace FasterWebsite\TranslationCache;


use fasterwebsite\perf\utils\CacheStats;
use MO;

class ApcuTranslationCache implements TranslationCache {

    public function get(string $domain, string $moFile): ?MO {
        if(!file_exists($moFile)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($domain, $moFile);
        if($cacheKey === null) {
            return null;
        }

        $found = false;
        $cached = apcu_fetch($cacheKey, $found);

        if(!$found) {
            CacheStats::get()->miss('translation-cache', $domain);
            return null;
        }

        CacheStats::get()->hit('translation-cache', $domain);

        $mo = new MO();
        $mo->entries = $cached['entries'];
        $mo->headers = $cached['headers'];

        return $mo;
    }

    protected function getCacheKey(string $domain, string $moFile) : ?string {
        $mtime = filemtime($moFile);
        if($mtime === false) {
            return null;
        }
        return md5("{$domain}_{$moFile}_{$mtime}");
    }

    public function set(string $domain, string $moFile, MO $mo): void {
        CacheStats::get()->set('translation-cache', $domain);
        $cacheKey = $this->getCacheKey($domain, $moFile);
        if($cacheKey === null) {
            return;
        }
        apcu_add(
            $cacheKey,
            ['entries' => $mo->entries, 'headers' => $mo->headers],
        );
    }
}
