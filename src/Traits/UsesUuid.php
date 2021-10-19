<?php

    namespace Nobelatunje\Wallet\Traits;

    use Illuminate\Support\Str;

    trait UsesUuid
    {

        /**
         * boot
         *
         * The "booting" method of the model, This help to magically create uuid for all new models
         *
         * @return void
         */
        public static function boot(): void {

            parent::boot();

            self::creating(function ($model) {
                $model->id = Str::uuid()->toString();
            });

        }

        /**
         * getIncrementing
         *
         * Get the value indicating whether the IDs are incrementing.
         *
         * @return bool
         */
        public function getIncrementing(): bool {
            return false;
        }

        /**
         * getKeyName
         *
         * Get the primary key for the model.
         *
         * @return string
         */
        public function getKeyName(): string {
            return 'id';
        }

        /**
         * getKeyType
         *
         * Get the auto-incrementing key type.
         *
         * @return string
         */
        public function getKeyType(): string {
            return 'string';
        }
    }
