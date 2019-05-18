<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveStates extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('accessories', function($t){$t->boolean('active')->default(1);});
		Schema::table('appliances', function($t){$t->boolean('active')->default(1);});
		Schema::table('cabinets', function($t){$t->boolean('active')->default(1);});
		Schema::table('conditions', function($t){$t->boolean('active')->default(1);});
		Schema::table('designations', function($t){$t->boolean('active')->default(1);});
		Schema::table('extras', function($t){$t->boolean('active')->default(1);});
		Schema::table('granites', function($t){$t->boolean('active')->default(1);});
		Schema::table('hardwares', function($t){$t->boolean('active')->default(1);});
		Schema::table('punches', function($t){$t->boolean('active')->default(1);});
		Schema::table('questions', function($t){$t->boolean('active')->default(1);});
		Schema::table('sinks', function($t){$t->boolean('active')->default(1);});
		Schema::table('sources', function($t){$t->boolean('active')->default(1);});
		Schema::table('status_expiration_actions', function($t){$t->boolean('active')->default(1);});
		Schema::table('status_expirations', function($t){$t->boolean('active')->default(1);});
		Schema::table('statuses', function($t){$t->boolean('active')->default(1);});
		Schema::table('users', function($t){$t->boolean('active')->default(1);});
		Schema::table('vendors', function($t){$t->boolean('active')->default(1);});
		Schema::table('levels', function($t){$t->boolean('active')->default(1);});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
