<?php

namespace MohammadZarifiyan\Telegram;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use MohammadZarifiyan\Telegram\Exceptions\TelegramAuthorizationException;
use MohammadZarifiyan\Telegram\Exceptions\TelegramValidationException;

class FormUpdate extends Update implements ValidatesWhenResolved
{
	use ValidatesWhenResolvedTrait;
	
	/**
	 * The container instance.
	 *
	 * @var \Illuminate\Contracts\Container\Container
	 */
	protected $container;
	
	/**
	 * The key to be used for the view error bag.
	 *
	 * @var string
	 */
	protected $errorBag = 'default';
	
	/**
	 * Indicates whether validation should stop after the first rule failure.
	 *
	 * @var bool
	 */
	protected $stopOnFirstFailure = false;
	
	/**
	 * The validator instance.
	 *
	 * @var \Illuminate\Contracts\Validation\Validator
	 */
	protected $validator;
	
	/**
	 * Get the validator instance for the request.
	 *
	 * @return \Illuminate\Contracts\Validation\Validator
	 * @throws BindingResolutionException
	 */
	protected function getValidatorInstance()
	{
		if ($this->validator) {
			return $this->validator;
		}
		
		$factory = $this->container->make(ValidationFactory::class);
		
		if (method_exists($this, 'validator')) {
			$validator = $this->container->call([$this, 'validator'], compact('factory'));
		} else {
			$validator = $this->createDefaultValidator($factory);
		}
		
		if (method_exists($this, 'withValidator')) {
			$this->withValidator($validator);
		}
		
		$this->setValidator($validator);
		
		return $this->validator;
	}
	
	/**
	 * Create the default validator instance.
	 *
	 * @param  \Illuminate\Contracts\Validation\Factory  $factory
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function createDefaultValidator(ValidationFactory $factory)
	{
		return $factory->make(
			$this->validationData(), $this->container->call([$this, 'rules']),
			$this->messages(), $this->attributes()
		)->stopOnFirstFailure($this->stopOnFirstFailure);
	}
	
	/**
	 * Get data to be validated from the request.
	 *
	 * @return array
	 */
	public function validationData(): array
	{
		return $this->all();
	}
	
	/**
	 * Determine if the request passes the authorization check.
	 *
	 * @return bool
	 *
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	protected function passesAuthorization()
	{
		if (method_exists($this, 'authorize')) {
			$result = $this->container->call([$this, 'authorize']);
			
			return $result instanceof Response ? $result->authorize() : $result;
		}
		
		return true;
	}
	
	/**
	 * Get a validated input container for the validated input.
	 *
	 * @param  array|null  $keys
	 * @return \Illuminate\Support\ValidatedInput|array
	 */
	public function safe(array $keys = null)
	{
		return is_array($keys)
			? $this->validator->safe()->only($keys)
			: $this->validator->safe();
	}
	
	/**
	 * Get the validated data from the request.
	 *
	 * @return array
	 */
	public function validated()
	{
		return $this->validator->validated();
	}
	
	/**
	 * Get custom messages for validator errors.
	 *
	 * @return array
	 */
	public function messages()
	{
		return [];
	}
	
	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array
	 */
	public function attributes()
	{
		return [];
	}
	
	/**
	 * Set the Validator instance.
	 *
	 * @param  \Illuminate\Contracts\Validation\Validator  $validator
	 * @return static
	 */
	public function setValidator(Validator $validator)
	{
		$this->validator = $validator;
		
		return $this;
	}
	
	/**
	 * Set the container implementation.
	 *
	 * @param  \Illuminate\Contracts\Container\Container  $container
	 * @return static
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;
		
		return $this;
	}
	
	/**
	 * Handle a failed authorization attempt.
	 *
	 * @return void
	 *
	 * @throws TelegramAuthorizationException
	 */
	public function failedAuthorization()
	{
		throw new TelegramAuthorizationException;
	}
	
	/**
	 * @throws TelegramValidationException
	 */
	public function failedValidation(Validator $validator)
	{
		throw new TelegramValidationException($validator);
	}
}
