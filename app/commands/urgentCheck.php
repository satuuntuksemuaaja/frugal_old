<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UrgentCheck extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'urgentCheck';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fires Texts to Agents that have urgent texts to respond to.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$tasks = Task::whereUrgent(true)->whereSatisfied(false)->get();
		foreach ($tasks AS $task)
		{
				// These tasks' agents need to be reminded.
				if ($task->assigned && $task->assigned->mobile)
					\vl\core\SMS::command('directory.send',
						['target' => $task->assigned->mobile,
                'message' => "($task->id) Reminder for: $task->subject : $task->body - REPLY 'TC or LM' when resolved."]);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
