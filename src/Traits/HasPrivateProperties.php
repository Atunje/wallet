<?php

    namespace Nobelatunje\Wallet\Traits;

    use Exception;

    trait HasPrivateProperties {

        /**
         * @throws Exception
         */
        public function __set($key, $value) {

            $this->isPrivate($key);

            parent::__set($key, $value);
        }

        /**
         * @throws Exception
         */
        private function isPrivate($key) {

            if(in_array($key, $this->privateProperties)) {
                throw new Exception("Property " . $key . " of class " . get_class($this) . " is a private property");
            }
        }

    }
