<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\Json;

class Logger
{
	private const MODULE = 'im';

	private string $uniqueString;

	public function __construct(string $uniqueString)
	{
		$this->uniqueString = $uniqueString;
	}

	public function log(string $text): void
	{
		$text = 'unique-string: ' . $this->uniqueString . "\n" . $text;
		AddMessage2Log($text, self::MODULE);
	}

	public function logArray(array $array): void
	{
		$text = Json::encode($array);
		$this->log($text);
	}

	public function logThrowable(\Throwable $throwable): void
	{
		$array = [
			'type' => 'exception',
			'message' => $throwable->getMessage(),
			'code' => $throwable->getCode(),
			'trace' => $throwable->getTraceAsString(),
		];
		$this->logArray($array);
	}

	public function logErrors(ErrorCollection $errors): void
	{
		$array = [];

		/** @var \Bitrix\Main\Error $error */
		foreach ($errors as $error)
		{
			$array[] = [
				'type' => 'error',
				'message' => $error->getMessage(),
				'code' => $error->getCode(),
				'customData' => $error->getCustomData(),
			];
		}

		$this->logArray($array);
	}
}