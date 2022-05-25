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

class PhpFileTranslationCache implements TranslationCache {
    public function get(string $domain, string $moFile): ?MO {
        if(!file_exists($moFile)) {
            return null;
        }

        $cacheFile = $this->getCacheFile($domain, $moFile);
        if($cacheFile === null) {
            return null;
        }

        if(!file_exists($cacheFile)) {
            return null;
        }

        $cached = require $cacheFile;

        if(!$cached) {
            CacheStats::get()->miss('translation-cache', $domain);
            return null;
        }

        CacheStats::get()->hit('translation-cache', $domain);

        $mo = new MO();
        $mo->entries = new TranslationEntryCollection($cached['entries']);
        $mo->headers = $cached['headers'];

        return $mo;
    }

    protected function getCacheKey(string $domain, string $moFile) : ?string {
        $mtime = filemtime($moFile);
        if($mtime === false) {
            return null;
        }
        $moFilename = pathinfo($moFile, PATHINFO_FILENAME);
        if(strpos($moFilename, $domain) === 0) {
            /* Simplify the filename, this covers most cases */
            $rawFilename = "{$moFilename}_{$mtime}";
        } else {
            $rawFilename = "{$domain}_{$moFilename}_{$mtime}";
        }
        $hash = md5("{$domain}_{$moFile}_{$mtime}");
        $validFilename = preg_replace('/[^a-zA-Z0-9\-._]/','', $rawFilename);
        return "{$validFilename}_{$hash}";
    }

    public function set(string $domain, string $moFile, MO $mo): void {
        CacheStats::get()->set('translation-cache', $domain);
        $cacheFile = $this->getCacheFile($domain, $moFile);
        if($cacheFile === null) {
            return;
        }

        file_put_contents(
            $cacheFile,
            '<?php return ' . var_export($this->convertTranslationToArray($mo), true) . ';'
        );
    }

    protected function convertTranslationToArray(\Translations $translations) : array {
        $result = [
            'headers' => [],
            'entries' => []
        ];

        $result['headers'] = $translations->headers;

        foreach($translations->entries as $key => $entry) {
            $result['entries'][$key] = (array)$entry;
        }

        return $result;
    }

    protected function getCacheFile(string $domain, string $moFile) : ?string {
        $cacheKey = $this->getCacheKey($domain, $moFile);
        if($cacheKey === null) {
            return null;
        }

        $path = WP_CONTENT_DIR . '/cache/translations/';
        if(!file_exists($path)) {
            if(!mkdir($path, 0777, true) && !is_dir($path)) {
                return null;
            }
        }

        return $path . $cacheKey;
    }
}
