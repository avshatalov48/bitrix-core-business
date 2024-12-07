<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\ArgumentException;

class Json
{
	public const DEFAULT_OPTIONS = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE;

	/**
	 * Returns a string containing the JSON representation of $data.
	 *
	 * @param mixed $data The value being encoded.
	 * @param int | null $options Bitmasked options. Default is JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE.
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @see json_encode
	 */
	public static function encode($data, $options = null)
	{
		if ($options === null)
		{
			$options = self::DEFAULT_OPTIONS;
		}

		try
		{
			$res = json_encode($data, $options | JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $e)
		{
			self::checkException($e, $options);
		}

		return $res;
	}

	/**
	 * Takes a JSON encoded string and converts it into a PHP variable.
	 *
	 * @param string $data The json string being decoded.
	 *
	 * @return mixed
	 * @throws ArgumentException
	 * @see json_decode
	 */
	public static function decode($data)
	{
		try
		{
			$res = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $e)
		{
			self::checkException($e);
		}

		return $res;
	}

	/**
	 * Converts $data to UTF-8 charset.
	 *
	 * @deprecated Does nothing.
	 * @param mixed $data Input data.
	 * @return mixed
	 */
	protected static function convertData($data)
	{
		return $data;
	}

	/**
	 * Checks global error flag and raises exception if needed.
	 *
	 * @param \JsonException $exception
	 * @param integer $options Bitmasked options. When JSON_PARTIAL_OUTPUT_ON_ERROR passed no exception is raised.
	 *
	 * @return void
	 * @throws ArgumentException
	 */
	protected static function checkException(\JsonException $exception, $options = 0)
	{
		$e = $exception->getCode();

		if ($e == JSON_ERROR_UTF8 && ($options & JSON_PARTIAL_OUTPUT_ON_ERROR))
		{
			return;
		}

		$message = sprintf('%s [%d]', $exception->getMessage(), $e);
		self::throwException($message);
	}

	/**
	 * Throws exception with message given.
	 *
	 * @param string $e Exception message.
	 *
	 * @return void
	 * @throws ArgumentException
	 */
	protected static function throwException($e)
	{
		throw new ArgumentException('JSON error: ' . $e, 'data');
	}
}
