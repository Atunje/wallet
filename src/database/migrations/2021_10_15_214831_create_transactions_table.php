<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateTransactionsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->uuid('id');
                $table->uuid('wallet_id');
                $table->decimal('amount');
                $table->enum('type', ['credit', 'debit']);
                $table->text('description');
                $table->string('reference', 15);
                $table->decimal('balance');
                $table->tinyInteger('reversed')->default(0);
                $table->text('entity')->nullable(true);
                $table->string('entity_id', 36)->nullable(true);
                $table->timestamps();
                $table->softDeletes('deleted_at');
                $table->primary('id');
                $table->foreign('wallet_id')->references('id')->on('wallets');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('wallet_transactions');
        }
    }
