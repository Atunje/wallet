<?php

    namespace Nobelatunje\Wallet;

    use Illuminate\Database\Eloquent\Model;
    use Nobelatunje\Wallet\Factories\TransactionFactory;
    use Nobelatunje\Wallet\Wallet;

    class Transaction extends Model {

        //disable laravel mass assignment
        protected $guarded = [];

        //table name
        protected $table = "wallet_transactions";


        /**
         * Get the wallet that owns the WalletTransaction
         *
         * @return Wallet
         */
        public function wallet(): Wallet
        {
            return $this->belongsTo(Wallet::class, 'wallet_id')->first();
        }



        /**
         * Create Credit Transaction
         *
         * Creates a credit transaction and return same
         *
         * @return Transaction
         */
        public static function createCreditTransaction(Wallet $wallet, float $amount, string $description): Transaction {

            return TransactionFactory::createTransaction($wallet, $amount, $description, TransactionFactory::TYPE_CREDIT);

        }


        /**
         * Create Debit Transaction
         *
         * Creates a debit transaction and return same
         *
         * @return Transaction
         */
        public static function createDebitTransaction(Wallet $wallet, float $amount, string $description): Transaction {

            return TransactionFactory::createTransaction($wallet, $amount, $description, TransactionFactory::TYPE_DEBIT);

        }


        /**
         * Reverse
         *
         * Reverse a transaction by creating a counter transaction
         *
         * @return Transaction
         */
        public function reverse(): Transaction {

            $wallet = $this->wallet();

            $new_description = "Transaction reversal of " . $this->description;

            if($this->type == TransactionFactory::TYPE_CREDIT) {

                return self::createDebitTransaction($wallet, $this->amount, $new_description);

            } else if($this->type == TransactionFactory::TYPE_DEBIT) {

                return self::createCreditTransaction($wallet, $this->amount, $new_description);

            } else {

                throw new Exception('Invalid transaction type: ' . $this->type);

            }
        }




    }
