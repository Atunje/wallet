<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateWalletsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('wallets', function (Blueprint $table) {
                $table->uuid('id');
                $table->string('name')->nullable(true);
                $table->string('user_id', 36);
                $table->decimal('balance');
                $table->timestamps();
                $table->softDeletes('deleted_at');
                $table->primary('id');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('wallets');
        }
    }
