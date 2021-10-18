<?php

    namespace Nobelatunje\Wallet;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Support\Facades\DB;

    use Nobelatunje\Wallet\Factories\WalletFactory;
    use Nobelatunje\Wallet\Transaction;
    use Nobelatunje\Wallet\TransactionResponse;

    use Exception;

    class Wallet extends Model {

        //disable laravel mass assignment
        protected $guarded = [];

        //table name
        protected $table = "wallets";


        /**
         * Create
         *
         * Creates a new wallet
         *
         * @return Wallet
         */
        public static function create(int $user_id): Wallet {

            if(self::dbTableExists()) {

                //check if wallet exists
                $wallet = Wallet::where(['user_id'=>$user_id])->first();

                if($wallet == null) {

                    $wallet = new Wallet();
                    $wallet->user_id = $user_id;
                    $wallet->balance = 0;

                    $wallet->save();

                }

                return $wallet;

            }

        }


        /**
         * Credit
         *
         * Credits the wallet
         *
         * @return TransactionResponse
         */
        public function credit(float $amount, string $description, string $entity="", int $entityid=0): TransactionResponse {

            if(!Transaction::transactionExists($entity, $entityid)) {

                $transaction = Transaction::createCreditTransaction($this, $amount, $description, $entity, $entityid);

                $this->updateBalance($transaction);

                return new TransactionResponse(true, "Credit Transaction was successful", $transaction);

            } else {

                return new TransactionResponse(false, "This transaction exists");

            }

            

        }


        /**
         * Debit
         *
         * Debits the wallet
         *
         * @return TransactionResponse
         */
        public function debit(float $amount, string $description, string $entity="", int $entityid=0): TransactionResponse {

            if(!Transaction::transactionExists($entity, $entityid)) {

                if($this->balance >= $amount) {

                    $transaction = Transaction::createDebitTransaction($this, $amount, $description, $entity, $entityid);

                    $this->updateBalance($transaction);

                    return new TransactionResponse(true, "Debit Transaction was successful", $transaction);

                } else {

                    return new TransactionResponse(false, "Insufficient wallet balance!");

                }

            } else {

                return new TransactionResponse(false, "This transaction exists");

            }

        }

        /**
         * Update Balance
         *
         * Updates the balance on the wallet
         *
         * @return bool
         */
        private function updateBalance(Transaction $transaction): bool {

            $this->balance = $transaction->balance;
            return $this->save();

        }


        /**
         * Reverse Transaction
         *
         * Reverses a previous transaction
         *
         * @return TransactionResponse
         */
        public function reverseTransaction(Transaction $transaction): TransactionResponse {

            if($this->verifyTransaction($transaction)) {

                if(!$transaction->isReversed()) {

                    $new_transaction = $transaction->reverse();

                    if($new_transaction != null) {

                        $this->updateBalance($new_transaction);

                        return new TransactionResponse(true, "Transaction was successfully reversed", $new_transaction);

                    } else {

                        return new TransactionResponse(false, "There was an error reversing transaction");

                    }

                } else {

                    return new TransactionResponse(false, "Specified Transaction has already been reversed");

                }

            } else {

                return new TransactionResponse(false, "Specified Transaction could not be found");

            }

        }


        /**
         * Verify Transaction
         *
         * checks if transaction truely exists and belongs to this wallet
         *
         * @return bool
         */
        private function verifyTransaction(Transaction $transaction): bool {

            //check if the transaction truely exists
            $transaction = Transaction::find($transaction->id);

            if($transaction!=null) {

                //get the wallet the transaction belongs to
                $wallet = $transaction->wallet();

                //check if it's the same wallet
                if($wallet->id == $this->id) {
                    return true;
                }
            }

            return false;
        }


        /**
         * DB Table Exists
         *
         * confirms if wallets table exists
         *
         * @throws Exception
         *
         * @return bool
         */
        private static function dbTableExists(): bool {

            if(!DB::getSchemaBuilder()->hasTable('wallets')) {
                throw new Exception('Wallets table does not exist in the database');
            }

            return true;
        }


        /**
         * Exists
         *
         * Checks if wallet really exists
         *
         * @return bool
         */
        public function exists():bool {

            if(isset($this->id)) {

                $wallet = self::find($this->id);

                if($wallet != null) {
                    return true;
                }
            }

            return false;
        }


    }
