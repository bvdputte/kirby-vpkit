<?php

namespace bvdputte\kirbyVPKit;

/** An internal array that stores the generated children (virtual pages)
  * of a given parent (`$key` is the parent ID) to speed up successive
  * queries in the current request as an in-memory store.
 **/

class Register {
    private $items = [];
 
    public function __construct()
    {
        $this->items = [];
    }

    public function get($key)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        return false;
    }

    public function set($key, $value)
    {
        $this->items[$key] = $value;
    }
}
