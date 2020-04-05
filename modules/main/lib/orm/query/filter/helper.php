<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter;

use Bitrix\Main\Text\Encoding;

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
	 * @param $phrase
	 *
	 * @return string
	 */
	public static function matchAgainstWildcard($phrase)
	{
		$config = \Bitrix\Main\Application::getConnection()->getConfiguration();
		$ftMinTokenSize = isset($config['ft_min_token_size']) ? $config['ft_min_token_size'] : static::FT_MIN_TOKEN_SIZE;

		$orValues = array();
		$wildcard = '*';

		//split to words by any non-word symbols
		$andValues = static::splitWords($phrase);

		if(!empty($andValues))
		{
			$andValues = array_filter($andValues,
				function($val) use ($ftMinTokenSize)
				{
					return (strlen($val) >= $ftMinTokenSize);
				}
			);

			if(!empty($andValues))
			{
				$orValues[] = "+".implode($wildcard." +", $andValues).$wildcard;
			}
		}

		if(!empty($orValues))
		{
			return "(".implode(") (", $orValues).")";
		}

		return '';
	}

	public static function getMinTokenSize()
	{
		static $ftMinTokenSize = null;
		if($ftMinTokenSize === null)
		{
			$config = \Bitrix\Main\Application::getConnection()->getConfiguration();
			$ftMinTokenSize = (isset($config["ft_min_token_size"])? $config["ft_min_token_size"] : self::FT_MIN_TOKEN_SIZE);
		}
		return $ftMinTokenSize;
	}

	protected static function splitWords($string)
	{
		static $encoding = null;
		if($encoding === null)
		{
			$encoding = \Bitrix\Main\Context::getCurrent()->getCulture()->getCharset();
		}

		if($encoding <> "UTF-8")
		{
			$string = Encoding::convertEncoding($string, $encoding, "UTF-8");
		}

		//split to words by any non-word symbols
		$values = preg_split("/[^\\p{L}\\d_]/u", $string);

		$values = array_filter($values,
			function($val)
			{
				return ($val <> '');
			}
		);
		$values = array_unique($values);

		if($encoding <> "UTF-8")
		{
			$values = Encoding::convertEncoding($values, "UTF-8", $encoding);
		}
		return $values;
	}
}
