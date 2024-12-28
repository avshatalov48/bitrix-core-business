<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Log;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Throwable;

class Logger
{
	public static function log(mixed $data, string $marker = 'DEBUG_SOCIALNETWORK'): void
	{
		$log = new Log($marker);

		if ($data instanceof Throwable)
		{
			$collectedData = static::collectThrowable($data);
		}
		elseif ($data instanceof Error)
		{
			$collectedData = static::collectError($data);
		}
		elseif ($data instanceof ErrorCollection)
		{
			$collectedData = static::collectErrorCollection($data);
		}
		else
		{
			$collectedData = $data;
		}

		$log->collect($collectedData);
	}

	private static function collectThrowable(Throwable $throwable): array
	{
		return [
			'type' => 'throwable',
			'message' => $throwable->getMessage(),
			'file' => $throwable->getFile(),
			'line' => $throwable->getLine(),
			'backtrace' => $throwable->getTraceAsString(),
		];
	}

	private static function collectError(Error $error): array
	{
		return [
			'type' => 'error',
			'message' => $error->getMessage(),
			'code' => $error->getCode(),
			'customData' => $error->getCustomData(),
		];
	}

	private static function collectErrorCollection(ErrorCollection $errorCollection): array
	{
		$errors = [];
		foreach ($errorCollection as $error)
		{
			$errors[] = static::collectError($error);
		}

		return [
			'type' => 'errorCollection',
			'errors' => $errors,
		];
	}
}