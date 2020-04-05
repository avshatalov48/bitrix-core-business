<?php
/**
 * Created by PhpStorm.
 * User: sigurd
 * Date: 17.01.17
 * Time: 10:43
 */

namespace Bitrix\Rest;


class HandlerHelper
{
	const ERROR_UNSUPPORTED_PROTOCOL = 'ERROR_UNSUPPORTED_PROTOCOL';
	const ERROR_WRONG_HANDLER_URL = 'ERROR_WRONG_HANDLER_URL';
	const ERROR_HANDLER_URL_MATCH = 'ERROR_HANDLER_URL_MATCH';

	protected static $applicationList = array();

	/**
	 * Checks callback URL validity.
	 *
	 * @param string $handlerUrl Callback URL.
	 * @param array $appInfo Application info.
	 * @param bool|true $checkInstallUrl Flag, whether to check URL_INSTALL field.
	 *
	 * @return bool
	 *
	 * @throws RestException
	 */
	public static function checkCallback($handlerUrl, $appInfo, $checkInstallUrl = true)
	{
		$callbackData = parse_url($handlerUrl);

		if(is_array($callbackData)
			&& strlen($callbackData['host']) > 0
			&& strpos($callbackData['host'], '.') > 0
		)
		{
			if($callbackData['scheme'] !== 'http' && $callbackData['scheme'] !== 'https')
			{
				throw new RestException('Unsupported handler protocol', static::ERROR_UNSUPPORTED_PROTOCOL);
			}
		}
		else
		{
			throw new RestException('Wrong handler URL', static::ERROR_WRONG_HANDLER_URL);
		}

		return true;
	}

	public static function storeApplicationList($PLACEMENT, $applicationList)
	{
		static::$applicationList[$PLACEMENT] = $applicationList;
	}

	public static function getApplicationList($PLACEMENT)
	{
		return is_array(static::$applicationList[$PLACEMENT])
			? static::$applicationList[$PLACEMENT]
			: array();
	}
}