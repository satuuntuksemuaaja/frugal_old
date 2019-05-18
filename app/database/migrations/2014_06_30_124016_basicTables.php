<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BasicTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->string('color')->default('4080B3');
			$t->string('email');
			$t->string('password');
			$t->integer('level_id');
			$t->integer('designation_id');
			$t->bigInteger('mobile');
			$t->boolean('superuser');
			$t->boolean('manager');
			$t->string('bypass');
			$t->string('remember_token');
			$t->text('google')->nullable();
		});

		Schema::create('levels', function($t)
		{
				$t->increments('id');
				$t->timestamps();
				$t->string('name');
		});

		Schema::create('designations', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
		});

		Schema::create('contacts', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('customer_id');
			$t->string('name');
			$t->string('email');
			$t->bigInteger('mobile');
			$t->bigInteger('home');
			$t->bigInteger('alternate');
			$t->boolean('primary');	//Primary contact for customer?
		});

		Schema::create('customers', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name'); // Customer/Company Name
			$t->string('address');
			$t->string('city');
			$t->string('state');
			$t->string('zip');
			$t->boolean('archived');
			$t->text('notes');
		});

		Schema::create('leads', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('status_id');
			$t->integer('customer_id');
			$t->integer('source_id');
			$t->integer('user_id');
			$t->integer('lead_source_id');
			$t->string('title');
			$t->boolean('closed');
			$t->boolean('archived');
			$t->text('notes');
		});

		Schema::create('stages', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');	 // Lead, Quote, Job, etc.
		});

		Schema::create('statuses', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->string('name');
			$t->integer('stage_id');
		});

		Schema::create('status_expirations', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('status_id');
			$t->string('name');
			$t->integer('expires'); // in seconds.
		});

		Schema::create('status_expiration_actions', function($t)
		{
			$t->increments('id');
			$t->timestamps();
			$t->integer('status_expiration_id'); // at 60 seconds we should
			$t->string('description');
			$t->boolean('sms');
			$t->string('email_subject');
			$t->boolean('email');
			$t->text('email_content');
			$t->text('sms_content');
			$t->integer('designation_id');
			$t->text('meta');	 // Serialized special instructions for this expiration.
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
		Schema::drop('levels');
		Schema::drop('designations');
		Schema::drop('contacts');
		Schema::drop('customers');
		Schema::drop('leads');
		Schema::drop('stages');
		Schema::drop('statuses');
		Schema::drop('status_expirations');
		Schema::drop('status_expiration_actions');
	}

}
