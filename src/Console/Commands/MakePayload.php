<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:telegram-payload')]
class MakePayload extends GeneratorCommand
{
	use CreatesMatchingTest;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:telegram-payload';
	
	/**
	 * The name of the console command.
	 *
	 * This name is used to identify the command during lazy loading.
	 *
	 * @var string|null
	 *
	 * @deprecated
	 */
	protected static $defaultName = 'make:telegram-payload';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Telegram payload.';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Telegram payload';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		$relativePath = $this->hasOption('reply-markup') ? '/../stubs/payload.reply-markup.stub' : '/../stubs/payload.stub';
		
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
		return $rootNamespace.'\Telegram\Payloads';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the payload'],
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
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Telegram payload already exists'],
			['reply-markup', null, InputOption::VALUE_NONE, 'Determines if payload has reply markup or not.']
		];
	}
}
