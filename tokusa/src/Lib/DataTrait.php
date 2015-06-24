<?php

/*
 * Copyright (c) 2015, developer
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Tokusa\Lib;

/**
 * Description of DataTrait
 *
 * @author Ryoh Kawai
 */
trait DataTrait
{
    public function __construct(Array $properties = []) {
        foreach (array_keys(get_object_vars($this)) as $name) {
            $this->initializeProperty($name, $properties);
        }
    }

    private function initializeProperty($name, Array $properties) {
        if ($name === "defaults") {
            return;
        }
        $this->{$name} = $this->getDefaultValue($name);
        if (!array_key_exists($name, $properties)) {
            return;
        }

        $value = (is_object($properties[$name])) ? clone $properties[$name] : $properties[$name];
        $setter = 'set' . $this->camelize($name);
        if (method_exists($this, $setter)) {
            $this->{$setter}($value);
            return;
        }

        $this->{$name} = $value;
    }

    private function getDefaultValue($name) {
        if (property_exists($this, "defaults") && array_key_exists($name, $this->defaults)) {
            return $this->defaults[$name];
        }

        return null;
    }

    public function __isset($name) {
        return (property_exists($this, $name) && $this->{$name} !== null);
    }

    public function __get($name) {
        $getter = 'get' . $this->camelize($name);
        if (!property_exists($this, $name) && !method_exists($this, $name) && !method_exists($this, $getter)) {
            throw new \InvalidArgumentException(sprintf("Not exist property or method in %s [%s].", get_class($this), $name));
        }

        if (method_exists($this, $getter)) {
            return $this->{$getter};
        }

        return $this->{$name};
    }

    public function __clone() {
        foreach (get_object_vars($this) as $name => $value) {
            if (is_object($value)) {
                $this->{$name} = clone $value;
            }
        }
    }

    public function __sleep() {
        return array_keys(get_object_vars($this));
    }

    public function __set_state($properties) {
        return new self($properties);
    }

    public function __set($name, $value) {
        throw new \LogicException(sprintf("Unsupported set method in %s class. [%s: %s].", get_class($this), $name, $value));
    }

    public function __unset($name) {
        throw new \LogicException(sprintf("Unsupported unset method in %s class. [%s].", get_class($this), $name));
    }

    private function camelize($string) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

}
