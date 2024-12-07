<?php

namespace Bitrix\Main\Diag;

use Bitrix\Main;

class ExceptionHandlerFormatter
{
	const MAX_CHARS = 30;

	const SHOW_PARAMETERS = 1;
	const DELIMITER = '----------';

	/**
	 * @param \Throwable $exception
	 * @param bool $htmlMode
	 * @param int $level
	 * @return string
	 */
	public static function format($exception, $htmlMode = false, $level = 0)
	{
		$formatter = new LogFormatter((bool)($level & static::SHOW_PARAMETERS), static::MAX_CHARS);

		$result = '';
		do
		{
			if ($result != '')
			{
				$result .= "Previous exception: ";
			}
			$result .= $formatter->format("{exception}{trace}{delimiter}\n", [
				'exception' => $exception,
				'trace' => static::getTrace($exception),
			]);
		}
		while (($exception = $exception->getPrevious()) !== null);

		if ($htmlMode)
		{
			$result = '<pre>'.Main\Text\HtmlFilter::encode($result).'</pre>';
		}

		return $result;
	}

	/**
	 * @param \Throwable $exception
	 * @return array
	 */
	protected static function getTrace($exception)
	{
		$backtrace = $exception->getTrace();

		$skip = "Bitrix\\Main\\Diag\\ExceptionHandler";

		$result = [];
		foreach ($backtrace as $item)
		{
			if (!isset($item['class']) || ($item['class'] != $skip))
			{
				$result[] = $item;
			}
		}

		return $result;
	}

	/**
	 * @deprecated
	 */
	protected static function getMessage($exception)
	{
		return $exception->getMessage().' ('.$exception->getCode().')';
	}

	/**
	 * @deprecated
	 */
	public static function severityToString($severity)
	{
		return LogFormatter::severityToString($severity);
	}

	/**
	 * @deprecated
	 */
	protected static function getArguments($args, $level)
	{
		$formatter = new LogFormatter((bool)($level & static::SHOW_PARAMETERS), static::MAX_CHARS);

		return $formatter->formatArguments($args);
	}

	/**
	 * @deprecated
	 */
	protected static function convertArgumentToString($arg, $level)
	{
		$formatter = new LogFormatter((bool)($level & static::SHOW_PARAMETERS), static::MAX_CHARS);

		return $formatter->formatArgument($arg);
	}

	/**
	 * @deprecated
	 */
	protected static function getFileLink($file, $line)
	{
		if (!empty($file))
		{
			return $file . ':' . $line;
		}
		return '';
	}
}
