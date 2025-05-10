<?php

namespace PCG\AccessControl\Core;

use ArrayAccess;

class Container implements ArrayAccess {
    private static $instance = null;
    private $container = [];

    private function __construct() {
        $this->container['plugin'] = new Plugin();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __get($name) {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }
        return null;
    }

    public function __isset($name) {
        return isset($this->container[$name]);
    }

    // ArrayAccess implementation
    public function offsetExists($offset): bool {
        return isset($this->container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset): mixed {
        return $this->container[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void {
        unset($this->container[$offset]);
    }
} 