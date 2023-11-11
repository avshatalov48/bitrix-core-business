<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter;

use Bitrix\Main\Text;

/**
 * Filter helper for specific values preparation.
 *
 * @package    bitrix
 * @subpackage main
 */
class Helper
{
	const FT_MIN_TOKEN_SIZE = 3;

	/**
	 * Places + and * for each word in a phrase.
	 *
	 * @param string $phrase
	 * @param string $wildcard '*' or '' actually
	 * @param int|null    $minTokenSize
	 *
	 * @return string
	 */
	public static function matchAgainstWildcard($phrase, $wildcard = '*', $minTokenSize = null)
	{
		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$orValues = static::parseFulltextPhrase($phrase, $minTokenSize);

		foreach ($orValues as $i => $andValues)
		{
			$orValues[$i] = '(' . $helper->getMatchAndExpression($andValues, $wildcard == '*') . ')';
		}

		return $helper->getMatchOrExpression($orValues);
	}

	public static function parseFulltextPhrase($phrase, $minTokenSize = null)
	{
		$ftMinTokenSize = $minTokenSize ?: static::getMinTokenSize();
		$orValues = array();

		//split to words by any non-word symbols
		$andValues = static::splitWords($phrase);

		if (!empty($andValues))
		{
			$andValues = array_filter(
				$andValues,
				function($val) use ($ftMinTokenSize)
				{
					return (mb_strlen($val) >= $ftMinTokenSize);
				}
			);

			if (!empty($andValues))
			{
				$orValues[] = $andValues;
			}
		}

		return $orValues;
	}

	/**
	 * @return int
	 */
	public static function getMinTokenSize()
	{
		static $ftMinTokenSize = null;
		if($ftMinTokenSize === null)
		{
			$config = \Bitrix\Main\Application::getConnection()->getConfiguration();
			$ftMinTokenSize = ($config["ft_min_token_size"] ?? self::FT_MIN_TOKEN_SIZE);
		}
		return $ftMinTokenSize;
	}

	/**
	 * Splits a string to words by any non-word symbols.
	 * @param $string
	 * @return array
	 */
	public static function splitWords($string)
	{
		static $encoding = null;
		if($encoding === null)
		{
			$encoding = strtolower(\Bitrix\Main\Context::getCurrent()->getCulture()->getCharset());
		}

		if($encoding <> "utf-8")
		{
			$string = Text\Encoding::convertEncoding($string, $encoding, "UTF-8");
		}
		else
		{
			//mysql [1064] syntax error, unexpected $end
			$string = Text\UtfSafeString::escapeInvalidUtf($string);
		}

		//split to words by any non-word symbols
		$values = preg_split("/[^\\p{L}\\d_]/u", $string);

		$values = array_filter(
			$values,
			function($val)
			{
				return ($val <> '');
			}
		);
		$values = array_unique($values);

		if($encoding <> "utf-8")
		{
			$values = Text\Encoding::convertEncoding($values, "UTF-8", $encoding);
		}
		return $values;
	}
}
