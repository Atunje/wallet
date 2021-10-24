<?php

    namespace Nobelatunje\Wallet\Traits;

    use Exception;

    trait HasPrivateProperties {

        /**
         * sets the property of the class
         * will not set if property is in the privateProperties array
         *
         * @param string $key
         * @param mixed $value
         *
         * @throws Exception
         */
        public function __set($key, $value) {

            $this->isPrivate($key);

            parent::__set($key, $value);
        }

        /**
         * checks if property is in the privateProperties array
         *
         * @param string $key
         *
         * @throws Exception
         */
        private function isPrivate(string $key) {

            if(in_array($key, $this->privateProperties)) {
                throw new Exception("Property " . $key . " of class " . get_class($this) . " cannot be set outside the class");
            }
        }

    }
