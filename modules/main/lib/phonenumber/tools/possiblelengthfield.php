<?php

namespace Bitrix\Main\PhoneNumber\Tools;

use Bitrix\Main\SystemException;

class PossibleLengthField extends XmlField
{
	public function decodeValue($value)
	{
		return static::parsePossibleLength($value);
	}

	/**
	 * Parses possible length field and returns corresponding array of possible lengths.
	 * @param string $possibleLength Something like "3,5,7", or "[7-9]", or  "6,[8-10]"
	 * @return array Returns array of possible lengths. I.e. [3, 5, 7], or [7, 8, 9], or [6, 8, 10].
	 */
	public static function parsePossibleLength($possibleLength)
	{
		$result = array();
		$tokens = explode(',', $possibleLength);
		foreach ($tokens as $token)
		{
			if(preg_match('/^\d+$/', $token))
			{
				$result[] = (int)$token;
			}
			else if(preg_match('/^\[(\d+)-(\d+)\]$/', $token, $matches))
			{
				$start = $matches[1];
				$end = $matches[2];
				$result = array_merge($result, range($start, $end));
			}
			else
			{
				throw new SystemException("Unrecognized token: ", $token);
			}
		}
		return $result;
	}

}