<?php

namespace Nobelatunje\Wallet\Tests\Unit;

use Nobelatunje\Wallet\Wallet;
use Nobelatunje\Wallet\Transaction;
use Nobelatunje\Wallet\TransactionResponse;
use Nobelatunje\Wallet\Tests\TestCase;

class WalletTest extends TestCase {

    public function test_wallet_lifecycle() {

        $user_id = 12;
        $wallet_name = "New Wallet";

        //wallet creation
        $wallet = Wallet::create($user_id, $wallet_name);
        $this->assertInstanceOf(Wallet::class, $wallet);
        $this->assertEquals($user_id, $wallet->user_id);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals($wallet_name, $wallet->name);

        //wallet credit transaction
        $response = $wallet->credit(10, "Test credit");
        $this->assertInstanceOf(TransactionResponse::class, $response);
        $this->assertTrue($response->status);
        $this->assertInstanceOf(Transaction::class, $response->transaction);
        $this->assertEquals($response->transaction->balance, $wallet->balance);
        $this->assertEquals(10, $wallet->balance);

        //wallet debit transaction
        $response = $wallet->debit(6, "Test debit");
        $this->assertInstanceOf(TransactionResponse::class, $response);
        $this->assertTrue($response->status);
        $this->assertInstanceOf(Transaction::class, $response->transaction);
        $this->assertEquals($response->transaction->balance, $wallet->balance);
        $this->assertEquals(4, $wallet->balance);

        //wallet transaction reversal
        $response = $wallet->reverseTransaction($response->transaction);
        $this->assertInstanceOf(TransactionResponse::class, $response);
        $this->assertTrue($response->status);
        $this->assertInstanceOf(Transaction::class, $response->transaction);
        $this->assertEquals($response->transaction->balance, $wallet->balance);
        $this->assertEquals(10, $wallet->balance);

        //wallet debit more than wallet balance
        $response = $wallet->debit(20, "Test debit");
        $this->assertInstanceOf(TransactionResponse::class, $response);
        $this->assertFalse($response->status);
        $this->assertEquals(10, $wallet->balance);

        //wallet credit negative amount
        $response = $wallet->credit(-10, "Test credit");
        $this->assertInstanceOf(TransactionResponse::class, $response);
        $this->assertFalse($response->status);
        $this->assertEquals(10, $wallet->balance);


        //count transactions
        $this->assertEquals(3, $wallet->transactions()->count());

        //wallet delete
        $wallet->delete();
        $this->assertTrue($wallet->exists());
        $wallet->delete(true);
        $this->assertFalse($wallet->exists());

    }

}
