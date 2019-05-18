<?php
namespace vl\libraries\bootstrap;
use Illuminate\Support\ServiceProvider;

class BootstrapServiceProvider extends ServiceProvider
{

	public function register()
	{
		$this->app->bind('Table', function()
		{
			return new \vl\libraries\bootstrap\Table;
		});

		$this->app->bind('Panel', function()
		{
			return new \vl\libraries\bootstrap\Panel;
		});


		$this->app->bind('Modal', function()
		{
			return new \vl\libraries\bootstrap\Modal;
		});

		$this->app->bind('BS', function()
		{
			return new \vl\libraries\bootstrap\BS;
		});


		$this->app->bind('Tile', function()
		{
			return new \vl\libraries\bootstrap\Tile;
		});

		$this->app->bind('Button', function()
		{
			return new \vl\libraries\bootstrap\Button;
		});

		$this->app->bind('Forms', function()
		{
			return new \vl\libraries\bootstrap\Forms;
		});

		$this->app->bind('Editable', function()
		{
			return new \vl\libraries\bootstrap\Editable;
		});

	}



}