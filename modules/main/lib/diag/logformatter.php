<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Diag;

use Bitrix\Main\Type;
use Bitrix\Main\DB;

class LogFormatter implements LogFormatterInterface
{
	protected const DELIMITER = '----------';

	protected $showArguments;
	protected $argMaxChars;

	public function __construct($showArguments = false, $argMaxChars = 30)
	{
		$this->showArguments = $showArguments;
		$this->argMaxChars = $argMaxChars;
	}

	/**
	 * Basic formatter to a string. Supports placeholders: {exception}, {trace}, {date}, {delimiter}.
	 * @inheritDoc
	 */
	public function format($message, array $context = []): string
	{
		// Implementors MAY have special handling for the passed objects. If that is not the case, implementors MUST cast it to a string.
		$message = $this->castToString($message);

		if (!isset($context['delimiter']))
		{
			$context['delimiter'] = static::DELIMITER;
		}

		// Placeholder names MUST be delimited with a single opening brace { and a single closing brace }. There MUST NOT be any whitespace between the delimiters and the placeholder name.
		$replace = [];
		foreach ($context as $key => $val)
		{
			$replace['{' . $key . '}'] = $this->castToString($val, $key);
		}

		return strtr($message, $replace);
	}

	/**
	 * Magic is here.
	 * @param mixed $value
	 * @param null $placeholder
	 * @return string
	 */
	protected function castToString($value, $placeholder = null): string
	{
		if (!is_string($value))
		{
			if (is_object($value))
			{
				if ($placeholder == 'date' && $value instanceof Type\Date)
				{
					$value = $this->formatDate($value);
				}
				elseif ($placeholder == 'exception' && $value instanceof \Throwable)
				{
					$value = $this->formatException($value);
				}
				elseif (method_exists($value, '__toString'))
				{
					$value = (string)$value;
				}
				else
				{
					$value = $this->formatMixed($value);
				}
			}
			else
			{
				if ($placeholder == 'trace' && is_array($value))
				{
					$value = $this->formatTrace($value);
				}
				else
				{
					$value = $this->formatMixed($value);
				}
			}
		}
		return $value;
	}

	/**
	 * Formats an exception.
	 * @param \Throwable $exception
	 * @return string
	 */
	protected function formatException(\Throwable $exception): string
	{
		$result = '[' . get_class($exception) . '] ';

		if ($exception instanceof \ErrorException)
		{
			$result .= static::severityToString($exception->getSeverity());
		}

		$result .= "\n" . $exception->getMessage() . ' (' . $exception->getCode() . ')' . "\n";

		if ($exception instanceof DB\SqlQueryException)
		{
			$result .= $exception->getQuery() . "\n";
		}

		$file = $exception->getFile();
		if ($file)
		{
			$result .= $file . ':' . $exception->getLine() . "\n";
		}

		return $result;
	}

	/**
	 * Formats a backtrace array.
	 * @param array $trace
	 * @return string
	 */
	protected function formatTrace(array $trace): string
	{
		$result = '';

		foreach ($trace as $traceNum => $traceInfo)
		{
			$traceLine = '#'.$traceNum.': ';

			if (isset($traceInfo['class']))
			{
				$traceLine .= $traceInfo['class'] . $traceInfo['type'];
			}

			if (isset($traceInfo['function']))
			{
				$traceLine .= $traceInfo['function'];
				if(isset($traceInfo['args']))
				{
					$traceLine .= $this->formatArguments($traceInfo['args']);
				}
			}

			$traceLine .= "\n\t";
			if (isset($traceInfo['file']))
			{
				$traceLine .= $traceInfo['file'] . ':' . $traceInfo['line'];
			}

			$result .= $traceLine . "\n";
		}

		return $result;
	}

	/**
	 * @internal
	 * @param $args
	 * @return string
	 */
	public function formatArguments($args)
	{
		if ($args !== null)
		{
			$arguments = [];
			foreach ($args as $arg)
			{
				$arguments[] = $this->formatArgument($arg);
			}

			return '(' . implode(', ', $arguments) . ')';
		}
		return '()';
	}

	/**
	 * @internal
	 * @param $arg
	 * @return array|string|string[]
	 */
	public function formatArgument($arg)
	{
		if (!$this->showArguments)
		{
			return gettype($arg);
		}

		switch (gettype($arg))
		{
			case 'boolean':
				$result = $arg ? 'true' : 'false';
				break;
			case 'NULL':
				$result = 'null';
				break;
			case 'integer':
			case 'double':
			case 'float':
				$result = (string)$arg;
				break;
			case 'string':
				if (is_callable($arg, false, $callableName))
				{
					$result = 'fs:' . $callableName;
				}
				elseif (class_exists($arg, false))
				{
					$result = 'c:' . $arg;
				}
				elseif (interface_exists($arg, false))
				{
					$result = 'i:' . $arg;
				}
				else
				{
					if (mb_strlen($arg) > $this->argMaxChars)
					{
						$result = '"' . mb_substr($arg, 0, $this->argMaxChars / 2) . '...' . mb_substr($arg, -$this->argMaxChars / 2) . '" (' . mb_strlen($arg) . ')';
					}
					else
					{
						$result = '"' . $arg . '"';
					}
				}
				break;
			case 'array':
				if (is_callable($arg, false, $callableName))
				{
					$result = 'fa:' . $callableName;
				}
				else
				{
					$result = 'array(' . count($arg) . ')';
				}
				break;
			case 'object':
				$result = '[' . get_class($arg) . ']';
				break;
			case 'resource':
				$result = 'r:' . get_resource_type($arg);
				break;
			default:
				$result = 'unknown type';
				break;
		}

		return str_replace("\n", '\n', $result);
	}

	/**
	 * Formats a date.
	 * @param Type\DateTime $dateTime
	 * @return string
	 */
	protected function formatDate(Type\Date $dateTime): string
	{
		return $dateTime->format('Y-m-d H:i:s');
	}

	/**
	 * Formats a mixed value.
	 * @param mixed $value
	 * @return string
	 */
	protected function formatMixed($value): string
	{
		return var_export($value, true);
	}

	public static function severityToString($severity)
	{
		switch ($severity)
		{
			case 1:
				return 'E_ERROR';
			case 2:
				return 'E_WARNING';
			case 4:
				return 'E_PARSE';
			case 8:
				return 'E_NOTICE';
			case 16:
				return 'E_CORE_ERROR';
			case 32:
				return 'E_CORE_WARNING';
			case 64:
				return 'E_COMPILE_ERROR';
			case 128:
				return 'E_COMPILE_WARNING';
			case 256:
				return 'E_USER_ERROR';
			case 512:
				return 'E_USER_WARNING';
			case 1024:
				return 'E_USER_NOTICE';
			case 2048:
				return 'E_STRICT';
			case 4096:
				return 'E_RECOVERABLE_ERROR';
			case 8192:
				return 'E_DEPRECATED';
			case 16384:
				return 'E_USER_DEPRECATED';
			case 30719:
				return 'E_ALL';
			default:
				return 'UNKNOWN';
		}
	}
}
