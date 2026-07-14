<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->integer('snf_beds')->after('name')->nullable();
            $table->string('snf_status')->after('snf_beds')->nullable();
            $table->string('price')->after('snf_status')->nullable();
            $table->text('description')->after('price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn('snf_beds');
            $table->dropColumn('snf_status');
            $table->dropColumn('price');
            $table->dropColumn('description');
        });
    }
};
