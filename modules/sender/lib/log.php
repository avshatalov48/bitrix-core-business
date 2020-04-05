<?php

namespace Bitrix\Sender;

class Log
{
	/**
	 * Save statistic data
	 * @param string $event Event.
	 * @param string $tag Tag.
	 * @param string $label Label.
	 * @return void
	 */
	public static function stat($event, $tag = '', $label = '')
	{
		if (function_exists('AddEventToStatFile'))
		{
			AddEventToStatFile('sender', $event, $tag, $label);
		}
	}
}