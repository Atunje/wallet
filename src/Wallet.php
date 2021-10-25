<?php

    namespace Nobelatunje\Wallet;

    use Exception;
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\DB;

    use Nobelatunje\Wallet\Traits\HasPrivateProperties;
    use Nobelatunje\Wallet\Transaction;
    use Nobelatunje\Wallet\TransactionResponse;
    use Nobelatunje\Wallet\Traits\UsesUuid;

    class Wallet extends Model {

        use SoftDeletes, UsesUuid, HasPrivateProperties;

        //disable laravel mass assignment
        protected $guarded = [];

        //table name
        protected $table = "wallets";

        /**
         * Private Properties
         *
         * These properties may be visible but they cannot be set from outside the class
         * These properties are set to make it impossible to create wallets without using the create method
         */
        protected array $privateProperties = ['user_id', 'balance'];

        /**
         * set the wallet balance
         *
         * @throws Exception
         */
        private function setBalance($balance) {

            parent::__set("balance", $balance);

        }

        /**
         * @throws Exception
         */
        private function setUserId(string $user_id) {

            parent::__set("user_id", $user_id);
        }


        /**
         * Create
         *
         * Creates a new wallet
         *
         * @return Wallet
         * @throws Exception
         */
        public static function create(int $user_id, string $name=null): Wallet {

            $wallet = null;

            if(self::dbTableExists()) {

                //check if wallet exists
                $wallet = self::where(['user_id'=>$user_id, 'name'=>$name])->first();

                if($wallet == null) {

                    $wallet = new Wallet();
                    $wallet->setUserId($user_id);
                    $wallet->setBalance(0);
                    $wallet->name = $name;

                    $wallet->save();

                }

            }

            return $wallet;

        }


        /**
         * Transactions
         *
         * Get all the transactions of this wallet
         *
         * @param bool $paginate
         * @param string|null $start_date - if end_date is not set, fetch all transactions created on the start_date
         * @param string|null $end_date
         *
         * @return LengthAwarePaginator | Collection
         */
        public function transactions(bool $paginate = false, string $start_date = null, string $end_date = null) {

            $transactions = $this->hasMany(Transaction::class)->orderBy('created_at', 'desc');

            if($start_date != null) {

                if($end_date == null) {

                    $transactions->whereDate('created_at', $start_date);

                } else {

                    $transactions->whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<=', $end_date);

                }

            }

            if($paginate) {

                return $transactions->paginate(50);
            }

            return $transactions->get();
        }


        /**
         * Credit
         *
         * Credits the wallet by creating a credit transaction and updating the wallet balance
         *
         * @param float $amount - The amount to be credited into the wallet
         * @param string $description - The narration of the transaction
         * @param object|null $entity - The unique entity that is attached to this transaction, could be a payment object
         *
         * @return TransactionResponse
         * @throws Exception
         */
        public function credit(float $amount, string $description, object $entity = null): TransactionResponse {

            if($amount > 0) {

                if (!Transaction::transactionExists($entity)) {

                    $transaction = Transaction::createCreditTransaction($this, $amount, $description, $entity);

                    if ($transaction != null) {

                        $this->updateBalance($transaction);

                        return new TransactionResponse(true, "Credit Transaction was successful", $transaction);

                    } else {

                        return new TransactionResponse(false, "There was an error creating this transaction");

                    }

                } else {

                    return new TransactionResponse(false, "This transaction exists");

                }

            } else {
                return new TransactionResponse(false, "Invalid amount supplied for transaction");
            }

        }


        /**
         * Debit
         *
         * Debits the wallet by creating a debit transacton and updating the wallet balance
         *
         * @param float $amount - The amount to be credited into the wallet
         * @param string $description - The narration of the transaction
         * @param object|null $entity - The unique entity that is attached to this transaction, could be a payment object
         *
         * @return TransactionResponse
         * @throws Exception
         */
        public function debit(float $amount, string $description, object $entity = null): TransactionResponse {

            if(!Transaction::transactionExists($entity)) {

                if($this->balance >= $amount) {

                    $transaction = Transaction::createDebitTransaction($this, $amount, $description, $entity);

                    if($transaction != null) {

                        $this->updateBalance($transaction);

                        return new TransactionResponse(true, "Debit Transaction was successful", $transaction);

                    } else {

                        return new TransactionResponse(false, "There was an error creating this transaction");

                    }

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

            $this->setBalance($transaction->balance);
            return $this->save();

        }


        /**
         * Reverse Transaction
         *
         * Reverses a previous transaction
         *
         * @param Transaction $transaction - the transaction to be reversed
         * @param object|null $entity - the unique entity to be attached to this transaction
         *
         *
         * @return TransactionResponse
         * @throws Exception
         */
        public function reverseTransaction(Transaction $transaction, object $entity = null): TransactionResponse {

            if($transaction->isValid($this)) {

                if(!$transaction->wasReversed()) {

                    $new_transaction = $transaction->reverse($entity);

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

                return $wallet != null;
            }

            return false;
        }


        /**
         * Delete
         *
         * deletes a wallet.
         * If $force_delete is set to true, the wallet is deleted even though the balance may be greater than 0
         *
         * @param bool $force_delete
         * @return TransactionResponse
         */
        public function delete(bool $force_delete=false): TransactionResponse {

            if($force_delete==true || $this->balance == 0) {

                $this->deleted_at = date('Y-m-d H:i:s');

                if($this->save()) {

                    return new TransactionResponse(true, "Wallet was successfully deleted!");

                }

                return new TransactionResponse(false, "There was an error deleting the specified wallet");

            } else {

                return new TransactionResponse(false, "Wallet cannot be deleted because it is still has a balance of " . number_format($this->balance));

            }

        }

    }
