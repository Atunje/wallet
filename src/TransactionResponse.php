<?php

    namespace Nobelatunje\Wallet;

    use Nobelatunje\Wallet\Transaction;

    class TransactionResponse {

        public bool $status;

        public Transaction $transaction;

        public string $status_message;

        /**
         * TransactionResponse
         * 
         * initialize instance
         */
        public function __construct(bool $status, string $status_message, Transaction $transaction = null) {

            $this->status = $status;

            if($transaction == null) {
                //set an empty transaction
                $transaction = new Transaction();
                
            } else {

                $this->transaction = $transaction;

            }

            $this->status_message = $status_message;

        }

    }

?>
