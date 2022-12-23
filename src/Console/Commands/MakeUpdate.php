<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:telegram-update')]
class MakeUpdate extends GeneratorCommand
{
	use CreatesMatchingTest;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:telegram-update';
	
	/**
	 * The name of the console command.
	 *
	 * This name is used to identify the command during lazy loading.
	 *
	 * @var string|null
	 *
	 * @deprecated
	 */
	protected static $defaultName = 'make:telegram-update';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Telegram update.';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Telegram update';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		$relativePath = '/../stubs/update.stub';
		
		return file_exists($customPath = $this->laravel->basePath(trim($relativePath, '/')))
			? $customPath
			: __DIR__.$relativePath;
	}
	
	/**
	 * Get the default namespace for the class.
	 *
	 * @param  string  $rootNamespace
	 * @return string
	 */
	protected function getDefaultNamespace($rootNamespace)
	{
		return $rootNamespace.'\Telegram\Updates';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the update'],
		];
	}
	
	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Telegram update already exists'],
		];
	}
}
