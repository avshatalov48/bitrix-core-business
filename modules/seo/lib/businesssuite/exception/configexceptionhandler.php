<?php

namespace Bitrix\Seo\BusinessSuite\Exception;

use Bitrix\Main;

final class ConfigExceptionHandler
{
	/** @var ConfigException */
	private $exception;

	/** @var Main\ErrorCollection */
	private $errorCollection;

	private function getErrorQueueGenerator() : \Generator
	{
		$currentException = $this->exception;
		do
		{
			yield new Main\Error(
				$currentException->getMessage(),
				$currentException->getCode()
			);
		}
		while($currentException = $currentException->getPrevious());
	}

	/**
	 * ConfigExceptionHandler constructor.
	 *
	 * @param ConfigException $exception
	 */
	public function __construct(ConfigException $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * @return Main\ErrorCollection
	 */
	public function getErrorCollection() : Main\ErrorCollection
	{
		return $this->errorCollection = $this->errorCollection ?? new Main\ErrorCollection(
			array_filter(iterator_to_array($this->getErrorQueueGenerator()))
			);
	}

	/**
	 * @return array
	 */
	public function getCustomData()
	{
		return $this->exception->getCustomData();
	}

	/**
	 * @param ConfigException $exception
	 *
	 * @return ConfigExceptionHandler
	 */
	public static function handle(ConfigException $exception)
	{
		return new self($exception);
	}
}