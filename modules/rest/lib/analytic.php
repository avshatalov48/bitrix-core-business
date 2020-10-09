<?php

namespace Bitrix\Rest;

/**
 * Class Analytic
 *
 * @package Bitrix\Rest
 */
class Analytic
{
	/**
	 * Logs data for analytic to file.
	 *
	 * @param string $action
	 * @param string $tag
	 * @param string $label
	 * @param string $actionType
	 */
	public static function logToFile($action, $tag = '', $label = '', $actionType = '')
	{
		if (function_exists('AddEventToStatFile'))
		{
			AddEventToStatFile('rest', $action, $tag, $label, $actionType);
		}
	}
}