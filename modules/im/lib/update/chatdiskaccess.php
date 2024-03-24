<?php
namespace Bitrix\Im\Update;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Im\Model\ChatTable;


final class ChatDiskAccess extends \Bitrix\Main\Update\Stepper
{
	const OPTION_NAME = 'disk_access_convert_stepper';
	protected static $moduleId = 'im';

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return false;
		}

		global $pPERIOD;
		$pPERIOD = 30; /** Increase agent delay. @see \CAgent::ExecuteAgents */

		$startTime = time();
		$isCronRun =
			!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24') &&
			(php_sapi_name() === 'cli');

		$return = false;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, '');
		$params = $params !== '' ? @unserialize($params, ['allowed_classes' => false]) : [];
		$params = is_array($params) ? $params : [];

		if (empty($params))
		{
			$params = [
				'lastId' => 0,
				'number' => 0,
				'count' => ChatTable::getCount([
					'>DISK_FOLDER_ID' => 0,
				]),
			];
		}

		if ($params['count'] > 0)
		{
			$result['title'] = Loc::getMessage('IM_UPDATE_CHAT_DISK_ACCESS');
			$result['progress'] = 1;
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$filter = [
				'>DISK_FOLDER_ID' => 0,
			];
			if (isset($params['lastId']) && (int)$params['lastId'] > 0)
			{
				$filter['<ID'] = (int)$params['lastId'];
			}
			$chatList = ChatTable::getList([
				'select' => ['ID', 'DISK_FOLDER_ID'],
				'filter' => $filter,
				'order' => ['ID' => 'DESC'],
				'offset' => 0,
				'limit' => 1000,
			]);

			$connection = \Bitrix\Main\Application::getConnection();

			$accessProvider = new \Bitrix\Im\Access\ChatAuthProvider;

			$found = false;
			while ($row = $chatList->fetch())
			{
				$chatId = (int)$row['ID'];
				$folderId = (int)$row['DISK_FOLDER_ID'];

				$accessProvider->updateChatCodesByRelations($chatId);

				$accessCode = $accessProvider->generateAccessCode($chatId);

				$connection->queryExecute("
					INSERT INTO b_disk_simple_right (OBJECT_ID, ACCESS_CODE)
					SELECT P.OBJECT_ID, '{$accessCode}'
					FROM 
						b_disk_object_path P
					WHERE
						P.OBJECT_ID != {$folderId}
						AND P.PARENT_ID = {$folderId}
				");

				$params['lastId'] = $chatId;
				$params['number']++;
				$found = true;

				if (!$isCronRun && (time() - $startTime >= 30))
				{
					break;
				}
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = true;
			}

			$result['progress'] = floor($params['number'] * 100 / $params['count']);
			$result['steps'] = $params['number'];

			if ($found === false)
			{
				Option::delete(self::$moduleId, array('name' => self::OPTION_NAME));
			}
		}
		
		return $return;
	}
}
