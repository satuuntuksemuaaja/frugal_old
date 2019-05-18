<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServiceFFTWarrantyToPos extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('po_items', function ($t) {
            $t->integer('fft_id')->nullable();
            $t->integer('service_id')->nullable();
            $t->integer('warranty_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('po_items', function ($t) {
            $t->dropColumn('fft_id');
            $t->dropColumn('service_id');
            $t->dropColumn('warranty_id');
        });
    }

}
