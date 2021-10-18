<?php

    namespace Nobelatunje\Wallet;

    use Illuminate\Database\Eloquent\Model;
    use Nobelatunje\Wallet\Factories\TransactionFactory;
    use Nobelatunje\Wallet\Wallet;

    use Exception;

    class Transaction extends Model {

        //disable laravel mass assignment
        protected $guarded = [];

        //table name
        protected $table = "wallet_transactions";

        protected $hidden = ['reversed'];

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
        public static function createCreditTransaction(Wallet $wallet, float $amount, string $description, object $entity): Transaction {

            return TransactionFactory::createTransaction($wallet, $amount, $description, TransactionFactory::TYPE_CREDIT, $entity);

        }


        /**
         * Create Debit Transaction
         *
         * Creates a debit transaction and return same
         *
         * @return Transaction
         */
        public static function createDebitTransaction(Wallet $wallet, float $amount, string $description, object $entity): Transaction {

            return TransactionFactory::createTransaction($wallet, $amount, $description, TransactionFactory::TYPE_DEBIT, $entity);

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

            $transaction = null;

            $new_description = "Transaction reversal of " . $this->description;

            if($this->type == TransactionFactory::TYPE_CREDIT) {

                $transaction = self::createDebitTransaction($wallet, $this->amount, $new_description);

            } else if($this->type == TransactionFactory::TYPE_DEBIT) {

                $transaction = self::createCreditTransaction($wallet, $this->amount, $new_description);

            } else {

                throw new Exception('Invalid transaction type: ' . $this->type);

            }

            if($transaction != null) {
                $this->setReversed();
            }

            return $transaction;

        }


        /**
         * Is Reversed
         *
         * checks if transaction has been reversed
         *
         * @return bool
         */
        public function isReversed() {

            return $this->reversed == 1;
        }


        /**
         * Reversed
         *
         * updates transaction that it has been reversed
         *
         * @return void
         */
        private function setReversed() {

            $this->reversed = 1;
            $this->save();

        }


        /**
         * Transaction Exists
         *
         * Checks if transaction exists if entity and entityid are not empty
         *
         * @return bool
         */
        public static function transactionExists(object $entity) {

            if(!empty($entity)) {

                //get the classname of the object
                $entity_name = get_class($entity);

                if(!isset($entity->id)) {
                    throw new Exception("Could not get unique identifier for the entity to be attached to transaction");
                } else {

                    //get the id
                    $entity_id = $entity->id;

                    $transaction = Transaction::where(['entity' => $entity_name, 'entity_id' => $entity_id])->first();

                    if ($transaction != null) {
                        return true;
                    }

                }

            }

            return false;
        }




    }
