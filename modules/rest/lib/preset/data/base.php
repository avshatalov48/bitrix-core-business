<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\Localization\Loc;

/**
 * Class Base
 * @package Bitrix\Rest\Preset\Data
 */
abstract class Base
{
	protected const CACHE_TIME = 86400;
	private const POSTFIX_MESSAGE_CODE = '.MESSAGE_CODE';

	/**
	 * Changes messages for local if exists.
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	protected static function changeMessage($data)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$data[$key] = static::changeMessage($value);
				}
				elseif (mb_strpos($key, self::POSTFIX_MESSAGE_CODE) !== false)
				{
					[$code] = explode(self::POSTFIX_MESSAGE_CODE, $key, 2);
					$message = Loc::getMessage($value);
					if (!empty($message))
					{
						$data[$code] = $message;
					}
				}
			}
		}

		return $data;
	}
}