<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2016 Bitrix
 */

namespace Bitrix\Im\Bot;

class ContextMenu
{
	private $botId = 0;
	private $items = Array();
	
	function __construct($botId = 0)
	{
		$this->botId = intval($botId);
	}
	
	public function addItem($params)
	{
		$button = Array();
		$button['BOT_ID'] = $this->botId;
		if (!isset($params['TEXT']) || strlen(trim($params['TEXT'])) <= 0)
			return false;

		if (isset($params['LINK']) && preg_match('#^(?:/|https?://)#', $params['LINK']))
		{
			$button['LINK'] = htmlspecialcharsbx($params['LINK']);
		}
		else if (isset($params['FUNCTION']))
		{
			$button['FUNCTION'] = htmlspecialcharsbx($params['FUNCTION']);
		}
		else if (isset($params['APP_ID']))
		{
			$button['APP_ID'] = intval($params['APP_ID']);
			if (isset($params['APP_PARAMS']) && strlen(trim($params['APP_PARAMS'])) > 0)
			{
				$button['APP_PARAMS'] = $params['APP_PARAMS'];
			}
		}
		else if ($this->botId > 0 && isset($params['COMMAND']) && strlen(trim($params['COMMAND'])) > 0)
		{
			$button['COMMAND'] = substr($params['COMMAND'], 0, 1) == '/'? substr($params['COMMAND'], 1): $params['COMMAND'];
			$button['COMMAND_PARAMS'] = isset($params['COMMAND_PARAMS']) && strlen(trim($params['COMMAND_PARAMS'])) > 0? $params['COMMAND_PARAMS']: '';
		}
		else
		{
			return false;
		}

		$button['TEXT'] = htmlspecialcharsbx(trim($params['TEXT']));

		$button['CONTEXT'] = in_array($params['CONTEXT'], Array('MOBILE', 'DESKTOP'))? $params['CONTEXT']: 'ALL';

		$button['DISABLED'] = $params['DISABLED'] == 'Y'? 'Y': 'N';

		$this->items[] = $button;

		return false;
	}

	public static function getByJson($params, $textReplace = array(), $options = Array())
	{
		if (is_string($params))
		{
			$params = \CUtil::JsObjectToPhp($params);
		}
		if (!is_array($params))
		{
			return null;
		}

		$menu = new self($params['BOT_ID']);
		foreach ($params['ITEMS'] as $button)
		{
			if (isset($button['FUNCTION']) && $options['ENABLE_FUNCTIONS'] != 'Y')
			{
			}
			else
			{
				if (isset($button['TEXT']))
				{
					foreach ($textReplace as $key => $value)
					{
						$button['TEXT'] = str_replace($key, $value, $button['TEXT']);
					}
				}
				$menu->addItem($button);
			}
		}
		
		return $menu->isEmpty()? null: $menu;
	}

	public function isEmpty()
	{
		return empty($this->items);
	}

	public function isAllowSize()
	{
		return $this->getJson()? true: false;
	}

	public function getArray()
	{
		return $this->items;
	}

	public function getJson()
	{
		$result = \Bitrix\Main\Web\Json::encode($this->items);
		return strlen($result) < 60000? $result: "";
	}
}