<?php

    namespace Nobelatunje\Wallet;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Support\Facades\DB;

    use Nobelatunje\Wallet\Traits\HasPrivateProperties;
    use Nobelatunje\Wallet\Wallet;
    use Nobelatunje\Wallet\Traits\UsesUuid;

    use Exception;

    class Transaction extends Model {

        use UsesUuid, HasPrivateProperties;

        //disable laravel mass assignment
        protected $guarded = [];

        //table name
        protected $table = "wallet_transactions";

        /**
         * Private Properties
         *
         * These properties may be visible but they cannot be set from outside the class
         * These properties are set to make it impossible to create transactions without using the create methods
         */
        protected array $privateProperties = ['wallet_id'];

        private const TYPE_CREDIT = "credit";
        private const TYPE_DEBIT = "debit";


        /**
         * set the wallet_id
         *
         * @throws Exception
         */
        private function setWalletID($wallet_id) {

            parent::__set("wallet_id", $wallet_id);

        }


        /**
         * Get the wallet that owns the WalletTransaction
         *
         * @return BelongsTo
         */
        public function wallet(): BelongsTo
        {
            return $this->belongsTo(Wallet::class);
        }

        /**
         * Create Transaction
         *
         * Creates a new transaction
         *
         * @throws Exception
         *
         * @return Transaction
         */
        private static function create(Wallet $wallet, float $amount, string $description, string $type, object $entity=null): Transaction {

            self::dbTableExists();

            if($wallet->exists()) {

                $transaction = new self();
                $transaction->description = $description;
                $transaction->reference = self::generateReference();
                $transaction->amount = $amount;

                //attach the unique entity associated with this transaction
                if(isset($entity->id)) {
                    $transaction->entity = get_class($entity);;
                    $transaction->entity_id = $entity->id;
                }

                $transaction->type = $type;

                $transaction->setWalletID($wallet->id);

                if($type === self::TYPE_CREDIT) {

                    $transaction->balance = $wallet->balance + $amount;

                } else if($type === self::TYPE_DEBIT) {

                    $transaction->balance = $wallet->balance - $amount;

                } else {

                    throw new Exception('Invalid transaction type: ' . $type);
                }

                $transaction->save();

                return $transaction;

            } else {

                throw new Exception('Wallet specified does not exist');

            }

        }


        /**
         * Generate Reference
         *
         * Generates random string of 15 characters
         *
         * @return string
         */
        private static function generateReference(): string {

            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            $reference = '';

            for ($i = 0; $i < 15; $i++) {
                $reference .= $characters[rand(0, strlen($characters) - 1)];
            }

            return $reference;

        }


        /**
         * DB Tables Exists
         *
         * confirms if wallets and transactions table exist
         *
         * @throws Exception
         */
        private static function dbTableExists() {

            if(!DB::getSchemaBuilder()->hasTable('wallet_transactions')) {

                throw new Exception('Transactions table not found in the database');

            }
        }


        /**
         * Create Credit Transaction
         *
         * Creates a credit transaction and return same
         *
         * @return Transaction
         * @throws Exception
         */
        public static function createCreditTransaction(Wallet $wallet, float $amount, string $description, object $entity=null): Transaction {

            return self::create($wallet, $amount, $description, self::TYPE_CREDIT, $entity);

        }


        /**
         * Create Debit Transaction
         *
         * Creates a debit transaction and return same
         *
         * @return Transaction
         * @throws Exception
         */
        public static function createDebitTransaction(Wallet $wallet, float $amount, string $description, object $entity=null): Transaction {

            return self::create($wallet, $amount, $description, self::TYPE_DEBIT, $entity);

        }


        /**
         * Reverse
         *
         * Reverse a transaction by creating a counter transaction
         *
         * @return Transaction
         * @throws Exception
         */
        public function reverse(object $entity=null): Transaction {

            $transaction = null;

            //set the description of the transaction
            $new_description = "Transaction reversal of " . $this->description;

            if($this->type == self::TYPE_CREDIT) {

                $transaction = self::createDebitTransaction($this->wallet, $this->amount, $new_description, $entity);

            } else if($this->type == self::TYPE_DEBIT) {

                $transaction = self::createCreditTransaction($this->wallet, $this->amount, $new_description, $entity);

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
        public function wasReversed(): bool {

            return $this->reversed == 1;

        }


        /**
         * Reversed
         *
         * updates transaction that it has been reversed
         *
         * @return bool
         */
        private function setReversed(): bool {

            $this->reversed = 1;
            return $this->save();

        }


        /**
         * Transaction Exists
         *
         * Checks if transaction exists by the unique entity supplied
         *
         * @param object|null $entity - unique entity to be attached to the transaction
         *
         * @return bool
         * @throws Exception
         */
        public static function transactionExists(object $entity = null): bool
        {

            if($entity!=null) {

                //get the classname of the object
                $entity_name = get_class($entity);

                if(!isset($entity->id)) {

                    throw new Exception("Could not get unique identifier for the entity to be attached to transaction");

                } else {

                    //get the id
                    $entity_id = $entity->id;

                    $transaction = self::where(['entity' => $entity_name, 'entity_id' => $entity_id])->first();

                    return $transaction != null;

                }

            }

            return false;
        }

        /**
         * Is Valid
         *
         * checks if a transaction validly exists and belongs to a wallet
         *
         * @param Wallet $wallet
         *
         * @return bool
         */
        public function isValid(Wallet $wallet): bool {

            if(isset($this->id)) {

                //check if the transaction truely exists
                $transaction = self::where(['id' => $this->id, 'wallet_id' => $wallet->id])->first();

                return $transaction != null;

            }

            return false;

        }

        /**
         * Delete
         *
         * make it impossible for transactions to be deleted when delete method is called
         *
         * @throws Exception
         */
        public function delete() {

            throw new Exception("Transaction cannot be deleted!");
        }




    }
