<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:telegram-command-handler')]
class MakeCommandHandler extends GeneratorCommand
{
	use CreatesMatchingTest;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:telegram-command-handler';
	
	/**
	 * The name of the console command.
	 *
	 * This name is used to identify the command during lazy loading.
	 *
	 * @var string|null
	 *
	 * @deprecated
	 */
	protected static $defaultName = 'make:telegram-command-handler';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Telegram command handler.';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Telegram command handler';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		$relativePath = $this->option('anonymous') ? '/../stubs/anonymous-command-handler.stub' : '/../stubs/command-handler.stub';

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
		return $rootNamespace.'\Telegram\CommandHandlers';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the command handler'],
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
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Telegram command handler already exists'],
            ['anonymous', 'a', InputOption::VALUE_NONE, 'Create an anonymous command handler'],
		];
	}
}
