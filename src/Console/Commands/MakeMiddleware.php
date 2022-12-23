<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:telegram-middleware')]
class MakeMiddleware extends GeneratorCommand
{
	use CreatesMatchingTest;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:telegram-middleware';
	
	/**
	 * The name of the console command.
	 *
	 * This name is used to identify the command during lazy loading.
	 *
	 * @var string|null
	 *
	 * @deprecated
	 */
	protected static $defaultName = 'make:telegram-middleware';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Telegram middleware.';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Telegram middleware';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		$relativePath = '/../stubs/middleware.stub';
		
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
		return $rootNamespace.'\Telegram\Middlewares';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the middleware'],
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
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Telegram middleware already exists'],
		];
	}
}
