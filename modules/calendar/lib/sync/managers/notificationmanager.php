<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use CAgent;
use CIMNotify;
use CTimeZone;

use function ConvertTimeStamp;

class NotificationManager
{
	private const FINISHED_SYNC_NOTIFICATION_DELAY = 60;

	/**
	 * @param int $userId
	 * @param string $vendorName
	 *
	 * @return void
	 *
	 * @throws LoaderException
	 */
	public static function sendFinishedSyncNotification(int $userId, string $vendorName): void
	{
		if (
			Main\Loader::includeModule("im")
			&& $userId
			&& !empty($vendorName)
		)
		{
			CIMNotify::Add([
				'TO_USER_ID' => $userId,
				'FROM_USER_ID' => $userId,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				'NOTIFY_MODULE' => 'calendar',
				'NOTIFY_TAG' => 'CALENDAR|SYNC_FINISH|'.$userId,
				'NOTIFY_SUB_TAG' => 'CALENDAR|SYNC_FINISH|'.$userId,
				'NOTIFY_MESSAGE' => Loc::getMessage('FINISHED_SYNC_NOTIFICATION_'.mb_strtoupper($vendorName))
			]);
		}
	}

	/**
	 * @param int $userId
	 * @param string $vendorName
	 *
	 * @return void
	 *
	 * @throws LoaderException
	 */
	public static function sendRollbackSyncNotification(int $userId, string $vendorName): void
	{
		if (
			Main\Loader::includeModule("im")
			&& $userId
			&& !empty($vendorName)
		)
		{
			CIMNotify::Add([
				'TO_USER_ID' => $userId,
				'FROM_USER_ID' => $userId,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				'NOTIFY_MODULE' => 'calendar',
				'NOTIFY_TAG' => 'CALENDAR|SYNC_ROLLBACK|'.$userId,
				'NOTIFY_SUB_TAG' => 'CALENDAR|SYNC_ROLLBACK|'.$userId,
				'NOTIFY_MESSAGE' => Loc::getMessage('ROLLBACK_SYNC_NOTIFICATION_'.mb_strtoupper($vendorName))
			]);
		}
	}

	/**
	 * @param int $userId
	 * @param string $vendorName
	 *
	 * @return void
	 */
	public static function clearFinishedSyncNotificationAgent(int $userId, string $vendorName): void
	{
		CAgent::RemoveAgent(
			'\\Bitrix\\Calendar\\Sync\\Managers\\NotificationManager::sendFinishedSyncNotification('
			. $userId . ', '
			. "'" . $vendorName . "'" . ');',
			'calendar'
		);
	}

	/**
	 * @param int $userId
	 * @param string $vendorName
	 *
	 * @return void
	 */
	public static function addFinishedSyncNotificationAgent(int $userId, string $vendorName): void
	{
		CAgent::AddAgent(
			'\\Bitrix\\Calendar\\Sync\\Managers\\NotificationManager::sendFinishedSyncNotification('
			. $userId . ', '
			. "'" . $vendorName . "'" . ');',
			'calendar',
			'Y',
			0,
			'',
			'Y',
			ConvertTimeStamp(time() + CTimeZone::GetOffset() + self::FINISHED_SYNC_NOTIFICATION_DELAY, 'FULL')
		);
	}

	/**
	 * @param int $userId
	 * @param $messageCode
	 * @param $vars
	 * @return void
	 *
	 * @throws LoaderException
	 */
	public static function sendBlockChangeNotification(int $userId, $messageCode, $vars)
	{
		if (
			Main\Loader::includeModule("im")
			&& $userId
			&& !empty(Loc::getMessage($messageCode))
		)
		{
			$message = Loc::getMessage($messageCode, $vars);

			CIMNotify::Add([
				'TO_USER_ID' => $userId,
				'FROM_USER_ID' => $userId,
				'NOTIFY_TYPE' => IM_NOTIFY_SYSTEM,
				'NOTIFY_MODULE' => 'calendar',
				'NOTIFY_TAG' => 'CALENDAR|SYNC_ROLLBACK|' . $userId . '|' . ($vars['EVENT_ID'] ?? rand(1,100)),
				'NOTIFY_SUB_TAG' => 'CALENDAR|SYNC_ROLLBACK|'.$userId,
				'NOTIFY_MESSAGE' => $message
			]);
		}
	}
}
