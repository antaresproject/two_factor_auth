<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Two factor auth
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */






use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTwoFactorAuthTables extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            $this->down();

            Schema::create('tbl_two_factor_auth_providers', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('area');
                $table->boolean('enabled')->default(0)->index();
                $table->boolean('forced')->default(0);
                $table->text('settings')->nullable();
                $table->timestamps();

                $table->unique(['name', 'area']);
            });

            Schema::create('tbl_two_factor_auth_users', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('provider_id')->unsigned()->index();
                $table->integer('user_id')->unsigned()->index();
                $table->text('settings')->nullable();
                $table->boolean('enabled')->default(0);
                $table->boolean('configured')->default(0);
                $table->timestamps();

                $table->foreign('provider_id')->references('id')->on('tbl_two_factor_auth_providers')->onUpdate('NO ACTION')->onDelete('CASCADE');
                $table->foreign('user_id')->references('id')->on('tbl_users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            });
        }
        catch(Exception $e) {
            Log::emergency($e);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_two_factor_auth_users');
        Schema::dropIfExists('tbl_two_factor_auth_providers');
    }

}
