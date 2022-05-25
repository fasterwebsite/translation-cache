<?php
/**
 * @author Maciej Klepaczewski <matt@fasterwebsite.com>
 * @link https://fasterwebsite.com/
 * @copyright Copyright (c) 2021, Maciej Klepaczewski FasterWebsite.com
 */
declare(strict_types=1);

namespace FasterWebsite\TranslationCache;

class TranslationEntryCollection implements \ArrayAccess {

    /**
     * @var array<string, array>
     */
    private array $entries;

    /**
     * TranslationEntryCollection constructor.
     * @param array[] $entries
     */
    public function __construct(array $entries) {
        $this->entries = $entries;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool {
        return array_key_exists($offset, $this->entries);
    }

    /**
     * @param string $offset
     * @return \Translation_Entry
     */
    public function offsetGet($offset) : \Translation_Entry {
        return new \Translation_Entry($this->entries[$offset]);
    }

    /**
     * @param string $offset
     * @param \Translation_Entry $value
     */
    public function offsetSet($offset, $value) : void {
        $this->entries[$offset] = (array)$value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset) : void {
        unset($this->entries[$offset]);
    }
}
