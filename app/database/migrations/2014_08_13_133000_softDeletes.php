<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SoftDeletes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('accessories', function($t){ $t->softDeletes(); });
		Schema::table('appliances', function($t){ $t->softDeletes(); });
		Schema::table('cabinets', function($t){ $t->softDeletes(); });
		Schema::table('closings', function($t){ $t->softDeletes(); });
		Schema::table('conditions', function($t){ $t->softDeletes(); });
		Schema::table('contacts', function($t){ $t->softDeletes(); });
		Schema::table('customers', function($t){ $t->softDeletes(); });
		Schema::table('designations', function($t){ $t->softDeletes(); });
		Schema::table('extras', function($t){ $t->softDeletes(); });
		Schema::table('ffts', function($t){ $t->softDeletes(); });
		Schema::table('files', function($t){ $t->softDeletes(); });
		Schema::table('granites', function($t){ $t->softDeletes(); });
		Schema::table('hardwares', function($t){ $t->softDeletes(); });
		Schema::table('job_items', function($t){ $t->softDeletes(); });
		Schema::table('job_schedules', function($t){ $t->softDeletes(); });
		Schema::table('jobs', function($t){ $t->softDeletes(); });
		Schema::table('leads', function($t){ $t->softDeletes(); });
		Schema::table('measures', function($t){ $t->softDeletes(); });
		Schema::table('notifications', function($t){ $t->softDeletes(); });
		Schema::table('punch_answers', function($t){ $t->softDeletes(); });
		Schema::table('punches', function($t){ $t->softDeletes(); });
		Schema::table('questions', function($t){ $t->softDeletes(); });
		Schema::table('quote_cabinets', function($t){ $t->softDeletes(); });
		Schema::table('quote_questions', function($t){ $t->softDeletes(); });
		Schema::table('quotes', function($t){ $t->softDeletes(); });
		Schema::table('showrooms', function($t){ $t->softDeletes(); });
		Schema::table('sinks', function($t){ $t->softDeletes(); });
		Schema::table('sources', function($t){ $t->softDeletes(); });
		Schema::table('stages', function($t){ $t->softDeletes(); });
		Schema::table('status_expiration_actions', function($t){ $t->softDeletes(); });
		Schema::table('status_expirations', function($t){ $t->softDeletes(); });
		Schema::table('statuses', function($t){ $t->softDeletes(); });
		Schema::table('task_notes', function($t){ $t->softDeletes(); });
		Schema::table('tasks', function($t){ $t->softDeletes(); });
		Schema::table('users', function($t){ $t->softDeletes(); });
		Schema::table('vendors', function($t){ $t->softDeletes(); });
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
