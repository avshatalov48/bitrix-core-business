<?php
namespace Bitrix\Im\Update;

use Bitrix\Im\Counter;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\UserTable;

Loc::loadMessages(__FILE__);

final class ChatReadRecount extends Stepper
{
	private const OPTION_NAME = 'im_chat_read_recount';

	protected static $moduleId = 'im';

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result): bool
	{
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
				'count' => UserTable::getCount([
					'=ACTIVE' => true,
					'=IS_REAL_USER' => true,
				]),
			];
		}

		if ($params['count'] > 0)
		{
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$cursor = UserTable::getList([
				'select' => ['ID'],
				'filter' => [
					'>ID' => $params['lastId'],
					'=ACTIVE' => true,
					'=IS_REAL_USER' => true,
				],
				'order' => ['ID' => 'ASC'],
				'limit' => 500
			]);

			$found = false;
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();

			$users = [];
			$batch = [];
			while ($row = $cursor->fetch())
			{
				if (count($users) === 100)
				{
					$batch[] = $users;
					$users = [];
				}

				$users[] = (int)$row['ID'];

				$params['lastId'] = (int)$row['ID'];
				$params['number']++;
				$found = true;
			}
			if (!empty($users))
			{
				$batch[] = $users;
			}

			foreach ($batch as $users)
			{
				$sqlUserIds = implode(', ', $users);

				$connection->query("
					UPDATE
						b_im_relation R USE INDEX (IX_IM_REL_2)
						INNER JOIN b_im_chat C
						INNER JOIN b_im_message M
					SET
						R.COUNTER = 0
					WHERE
						R.MESSAGE_TYPE = 'C'
						and R.STATUS != 2
						and R.USER_ID IN (".$sqlUserIds.")
						and C.ID = R.CHAT_ID
						and M.ID = C.LAST_MESSAGE_ID
						and M.DATE_CREATE < DATE_SUB(NOW(), INTERVAL 30 DAY)
				");

				$connection->query("
					UPDATE
						b_im_relation R USE INDEX (IX_IM_REL_2)
						INNER JOIN b_im_chat C
						INNER JOIN b_im_message M
					SET
						R.COUNTER = 0
					WHERE
						R.MESSAGE_TYPE = 'P'
						and R.STATUS != 2
						and R.USER_ID IN (".$sqlUserIds.")
						and C.ID = R.CHAT_ID
						and M.ID = C.LAST_MESSAGE_ID
						and M.DATE_CREATE < DATE_SUB(NOW(), INTERVAL 30 DAY)
				");

				$connection->query("
					UPDATE
						b_im_relation R USE INDEX (IX_IM_REL_2)
						INNER JOIN b_im_chat C
						INNER JOIN b_im_message M
					SET
						R.COUNTER = 0
					WHERE
						R.MESSAGE_TYPE = 'O'
						and R.STATUS != 2
						and R.USER_ID IN (".$sqlUserIds.")
						and C.ID = R.CHAT_ID
						and M.ID = C.LAST_MESSAGE_ID
						and M.DATE_CREATE < DATE_SUB(NOW(), INTERVAL 30 DAY)
				");
				
				foreach ($users as $userId)
				{
					Counter::clearCache($userId);
				}
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}
			else
			{
				Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);
			}

			$result['steps'] = $params['number'];
		}

		return $return;
	}
}