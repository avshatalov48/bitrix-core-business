<?php
/**
* Bitrix Framework
* @package bitrix
* @subpackage socialnetwork
* @copyright 2001-2019 Bitrix
*/
namespace Bitrix\Socialnetwork\Integration\Bitrix24;

use Bitrix\Socialnetwork\Livefeed\Provider;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Bitrix24Event
{
	public static function OnManualModuleAddDelete(Event $event)
	{
		$result = new EventResult(
			EventResult::UNDEFINED,
			array(),
			'socialnetwork'
		);

		$modulesList = $event->getParameter('modulesList');

		if (
			!is_array($modulesList)
			|| empty($modulesList)
		)
		{
			return $result;
		}

		$connection = \Bitrix\Main\HttpApplication::getConnection();

		foreach($modulesList as $moduleId => $status)
		{
			if (!in_array($status, ['Y', 'N']))
			{
				continue;
			}

			$inactive = (
				$status == 'N'
					? 'Y'
					: 'N'
			);

			switch($moduleId)
			{
				case "crm":
					$sql = "UPDATE b_sonet_log SET INACTIVE='".$inactive."' WHERE MODULE_ID in ('crm', 'crm_shared')";
					break;
				case "timeman":
					$sql = "UPDATE b_sonet_log SET INACTIVE='".$inactive."' WHERE EVENT_ID in ('timeman_entry', 'report')";
					break;
				case "lists":
					$sql = "UPDATE b_sonet_log SET INACTIVE='".$inactive."' WHERE EVENT_ID = 'lists_new_element'";
					break;
				default:
					$sql = '';
			}
			if (!empty($sql))
			{
				$connection->query($sql);
			}
		}

		$result = new EventResult(
			EventResult::SUCCESS,
			array(),
			'socialnetwork'
		);

		return $result;
	}
}
?>