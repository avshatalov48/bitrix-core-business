<?php
namespace Bitrix\Im\Update;

use Bitrix\Im\Model\RelationTable;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class NotifyReadRecount  extends Stepper
{
	private const OPTION_NAME = 'im_notify_read_recount';

	protected static $moduleId = 'im';

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result): bool
	{
		Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);
		return false;
		global $DB;

		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME);
		$params = ($params !== '' ? @unserialize($params, ['allowed_classes' => false]) : []);
		$params = (is_array($params) ? $params : []);

		if (empty($params))
		{
			$params = [
				'lastId' => 0,
				'number' => 0,
				'count' => RelationTable::getCount([
					'>COUNTER' => 0,
					'=MESSAGE_TYPE' => \Bitrix\Im\Chat::TYPE_SYSTEM
				]),
			];
		}

		if ($params['count'] > 0)
		{
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$cursor = RelationTable::getList([
				'select' => ['ID', 'CHAT_ID'],
				'filter' => [
					'>ID' => $params['lastId'],
					'>COUNTER' => 0,
					'=MESSAGE_TYPE' => \Bitrix\Im\Chat::TYPE_SYSTEM
				],
				'order' => ['ID' => 'ASC'],
				'limit' => 1000
			]);

			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			$sqlDate = $connection->getSqlHelper()->addDaysToDateTime(-30);

			$found = false;
			while ($row = $cursor->fetch())
			{
				$DB->Query("
					UPDATE b_im_message M
					SET M.NOTIFY_READ = 'Y'
					WHERE M.CHAT_ID = " . $row['CHAT_ID'] . "
					AND M.NOTIFY_READ <> 'Y'
					AND M.DATE_CREATE < {$sqlDate}
				");

				$counterResult = $DB->Query("
					SELECT COUNT(1) as CNT
                    FROM b_im_message M
					WHERE M.CHAT_ID = " . $row['CHAT_ID'] . "
					AND NOTIFY_READ <> 'Y'
				")->GetNext();

				RelationTable::update($row['ID'], [
					'COUNTER' => $counterResult['CNT']
				]);

				$params['lastId'] = $row['ID'];
				$params['number']++;
				$found = true;
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}
			else
			{
				\Bitrix\Im\Counter::clearCache();
				Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);
			}

			$result['steps'] = $params['number'];
		}

		return $return;
	}
}