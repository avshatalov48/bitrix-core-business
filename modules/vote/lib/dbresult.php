<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage vote
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Vote;

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class DBResult extends \CDBResult
{
	/**
	 * @return array|bool|false|mixed|null
	 */
	function fetch()
	{
		if ($res = parent::fetch())
		{
			$prefix = null;
			foreach ($res as $k => $v)
			{
				if (strpos($k, "LAMP") !== false)
				{
					$prefix = substr($k, 0, strpos($k, "LAMP"));
					break;
				}
			}
			if ($prefix !== null && $res[$prefix."LAMP"] == "yellow" && !empty($res[$prefix."CHANNEL_ID"]))
			{
				$res[$prefix."LAMP"] = ($res[$prefix."ID"] == \CVote::getActiveVoteId($res[$prefix."CHANNEL_ID"]) ? "green" : "red");
			}
		}
		return $res;
	}
}