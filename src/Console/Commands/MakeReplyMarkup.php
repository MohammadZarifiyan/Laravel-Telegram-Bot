<?php

namespace MohammadZarifiyan\Telegram\Console\Commands;

use Illuminate\Console\Concerns\CreatesMatchingTest;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:telegram-reply-markup')]
class MakeReplyMarkup extends GeneratorCommand
{
	use CreatesMatchingTest;
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'make:telegram-reply-markup';
	
	/**
	 * The name of the console command.
	 *
	 * This name is used to identify the command during lazy loading.
	 *
	 * @var string|null
	 *
	 * @deprecated
	 */
	protected static $defaultName = 'make:telegram-reply-markup';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new Telegram reply markup.';
	
	/**
	 * The type of class being generated.
	 *
	 * @var string
	 */
	protected $type = 'Telegram reply markup';
	
	/**
	 * Get the stub file for the generator.
	 *
	 * @return string
	 */
	protected function getStub()
	{
		$relativePath = '/../stubs/reply-markup.stub';
		
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
		return $rootNamespace.'\Telegram\ReplyMarkups';
	}
	
	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'The name of the reply markup'],
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
			['force', 'f', InputOption::VALUE_NONE, 'Create the class even if the Telegram reply markup already exists'],
		];
	}
}
