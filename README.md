# Wallet

A simple laravel package for wallet implementation.

This package can basically be plugged into a laravel project and it will handle wallet transactions. It allows for a user to have multiple wallets that can be given different names.

### How to install
Install via composer

    $ composer install nobelatunje/wallet

Copy the database migrations to your migrations folder and run
    
    $ php artisan migrate

### To create a wallet
```php
$wallet = Wallet::create($user_id, "Car Savings Wallet");
```

### To get user's wallets
```php
$wallet = $this->hasMany(Wallet::class, 'user_id');
```

### To credit a wallet
```php
$wallet->credit(2000, "Payment for order #849494");
```

### To debit a wallet
```php
$wallet->debit(1000, "Purchase of airtime");
```

### To reverse a transaction
```php
$wallet = Wallet::find(2); 
$transaction = Transaction::find(3);

$wallet->reverseTransaction($transaction);
```

### To view wallet transactions
```php
Wallet::find(2)->transactions();
```

### To retrieve wallets
```php
Wallet::all();
```

### To delete a wallet
```php
Wallet::find(2)->delete();
```

