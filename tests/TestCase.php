<?php

    namespace Nobelatunje\Wallet\Tests;

    use Orchestra\Testbench\TestCase as BaseTestCase;

    abstract class TestCase extends BaseTestCase
    {

        /**
         * Setup the test environment.
         */
        protected function setUp(): void
        {
            parent::setUp();
        }
    }
