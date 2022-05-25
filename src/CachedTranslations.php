<?php
/**
 * @author Maciej Klepaczewski <matt@fasterwebsite.com>
 * @link https://fasterwebsite.com/
 * @copyright Copyright (c) 2021, Maciej Klepaczewski FasterWebsite.com
 */
declare(strict_types=1);

namespace FasterWebsite\TranslationCache;

use fasterwebsite\perf\utils\PerfHelper;

class CachedTranslations {

    public const CACHE_KEY_GROUP = 'translations';
    protected TranslationCache $cache;

    public function __construct(TranslationCache $cache) {
        $this->cache = $cache;
        add_filter('override_load_textdomain',
            function(bool $override, string $domain, string $moFile) : bool {
                return PerfHelper::get()->timeIt(
                    'translation_cache',
                    fn() => PerfHelper::get()->timeIt("translation_cache-$domain-$moFile", fn() => $this->overrideLoadTextDomain($override, $domain, $moFile))
                );
            },
            PHP_INT_MAX,
            3
        );
    }

    private function overrideLoadTextDomain(bool $override, string $domain, string $moFile) : bool {
        global $l10n;

        $mo = $this->cache->get($domain, $moFile);

        /**
         * We should have separate case for (non existent yet) has() but failed
         * to load case. We don't want to repeatedly try to load it from cache
         * on each request if it fails.
         */

        if($mo === null) {
            $mo = $this->loadMoFile($domain, $moFile);
            if($mo === null) {
                return false;
            }
            $this->cache->set($domain, $moFile, $mo);
        }

        if(isset($l10n[$domain])) {
            $mo->merge_with($l10n[$domain]);
        }

        $l10n[$domain] = $mo;

        return true;
    }

    protected function loadMoFile(string $domain, string $moFile) : ?\MO {
        if ( ! is_readable( $moFile ) ) {
            return null;
        }

        $mo = new \MO();
        if(false === $mo->import_from_file($moFile)) {
            return null;
        }
        return $mo;
    }
}
