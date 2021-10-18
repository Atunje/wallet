<?php

    namespace Nobelatunje\Wallet\Factories;

    use Nobelatunje\Wallet\Wallet;
    use Nobelatunje\Wallet\Transaction;
    use Nobelatunje\Wallet\Exceptions\InvalidTransactionTypeException;
    use Nobelatunje\Wallet\Exceptions\WalletNotFoundException;
    use Illuminate\Support\Facades\DB;
    use Exception;

    class TransactionFactory {

        public const TYPE_CREDIT = "credit";
        public const TYPE_DEBIT = "debit";

        /**
         * Create Transaction
         *
         * Creates a new transaction
         *
         * @throws Exception
         *
         * @return Transaction
         */
        public static function createTransaction(Wallet $wallet, float $amount, string $description, string $type): Transaction {

            if(self::dbTablesExists() && $wallet->exists()) {

                $transaction = new Transaction();
                $transaction->wallet_id = $wallet->id;
                $transaction->amount = $amount;
                $transaction->description = $description;
                $transaction->reference = self::generateReference();

                if($type === self::TYPE_CREDIT) {

                    $transaction->balance = $wallet->balance += $amount;

                } else if($type === self::TYPE_DEBIT) {

                    $transaction->balance = $wallet->balance -= $amount;

                } else {

                    throw new Exception('Invalid transaction type: ' . $type);
                }

                $transaction->type = $type;

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
         * confirms if wallets table exists
         *
         * @throws TransactionsDBTableNotFoundException
         * @throws WalletsDBTableNotFoundException
         *
         * @return bool
         */
        private static function dbTablesExists(): bool {

            if(!DB::getSchemaBuilder()->hasTable('wallet_transactions')) {

                throw new Exception('Transactions table not found in the database');

            } else if(!DB::getSchemaBuilder()->hasTable('wallets')) {

                throw new Exception('Wallets table now found in the database');

            }

            return true;
        }


    }
