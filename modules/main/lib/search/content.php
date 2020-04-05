<?php

namespace Bitrix\Main\Search;

class Content
{
	const TYPE_STRING = 1;
	const TYPE_INTEGER = 2;
	const TYPE_MIXED = 3;

	/**
	 * Applies ROT13 transform to string search token, in order to bypass default mysql search blacklist.
	 * @param string $token Search token.
	 * @return string
	 */
	public static function prepareStringToken($token)
	{
		return str_rot13($token);
	}

	/**
	 * Method adds zeros to integer search token, in order to bypass current mysql minimum of token size.
	 * @param integer $token Search token.
	 * @return string
	 */
	public static function prepareIntegerToken($token)
	{
		$token = intval($token);
		return str_pad($token, \CSQLWhere::GetMinTokenSize(), '0', STR_PAD_LEFT);
	}

	/**
	 * Method checks whether token is a number.
	 * @param integer $token Search token.
	 * @return bool
	 */
	public static function isIntegerToken($token)
	{
		return preg_match('/^[0-9]{1,}$/i', $token);
	}

	/**
	 * Method check whether you can use the full-text search.
	 * @param integer|string $token Search token.
	 * @param integer $type Type of content.
	 * @return bool
	 */
	public static function canUseFulltextSearch($token, $type = self::TYPE_STRING)
	{
		if (intval($type) > 1)
		{
			$result = \Bitrix\Main\Search\Content::isIntegerToken($token) || strlen($token) >= \CSQLWhere::GetMinTokenSize();
		}
		else
		{
			$result = strlen($token) >= \CSQLWhere::GetMinTokenSize();
		}

		return $result;
	}
}