<?php
namespace Bitrix\Translate\Update;

use Bitrix\Main\Loader;
use Bitrix\Main\DB\SqlException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Translate\Index;


final class PhraseFtsIndexConverter extends \Bitrix\Main\Update\Stepper
{
	const OPTION_NAME = 'fts_index_converter_stepper';
	protected static $moduleId = 'translate';

	/**
	 * @inheritdoc
	 */
	public function execute(array &$result)
	{
		if (!Loader::includeModule(self::$moduleId))
		{
			return self::FINISH_EXECUTION;
		}

		global $pPERIOD;
		$pPERIOD = 30; /** Increase agent delay. @see \CAgent::ExecuteAgents */

		$connection = \Bitrix\Main\Application::getConnection();
		if (!$connection->isTableExists('b_translate_phrase'))
		{
			return self::FINISH_EXECUTION;
		}

		try
		{
			$connection->query("SELECT `PHRASE` FROM `b_translate_phrase` WHERE 1=0");
		}
		catch (SqlException $exception)
		{
			return self::FINISH_EXECUTION;
		}

		$startTime = time();
		$isCronRun =
			!\Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24')
			&& (php_sapi_name() === 'cli');

		$return = self::FINISH_EXECUTION;

		$params = Option::get(self::$moduleId, self::OPTION_NAME, '');
		$params = $params !== '' ? @unserialize($params, ['allowed_classes' => false]) : [];
		$params = is_array($params) ? $params : [];

		if (empty($params))
		{
			$params = [
				'languages' => [],
				'index' => false,
				'field' => false,
				'number' => 0,
				'count' => LanguageTable::getCount() + 2,
			];
		}

		if ($params['count'] > 0)
		{
			$result['title'] = Loc::getMessage('IM_UPDATE_CHAT_DISK_ACCESS');
			$result['progress'] = 1;
			$result['steps'] = '';
			$result['count'] = $params['count'];

			$filter = [];
			if (!empty($params['languages']))
			{
				$filter['!=ID'] = $params['languages'];
			}
			$found = false;
			$langList = LanguageTable::getList([
				'select' => ['ID'],
				'filter' => $filter,
				'order' => ['SORT' => 'ASC'],
			]);
			while ($row = $langList->fetch())
			{
				$langId = mb_strtolower($row['ID']);
				if (!preg_match("/[a-z0-9]{2}/i", $langId))
				{
					continue;
				}

				try
				{
					Index\Internals\PhraseFts::createTable($langId);
					$partitionTable = Index\Internals\PhraseFts::getPartitionTableName($langId);

					$connection->queryExecute("
						INSERT IGNORE INTO `{$partitionTable}` 
						SELECT `ID`, `FILE_ID`, `PATH_ID`, `CODE`, `PHRASE` FROM `b_translate_phrase` WHERE `LANG_ID` = '{$langId}'
					");
				}
				catch (SqlException $exception)
				{}

				$params['languages'][] = $langId;
				$params['number']++;
				$found = true;
				if (!$isCronRun && (time() - $startTime >= 30))
				{
					break;
				}
			}

			if ($found === false)
			{
				if ($params['index'] === false)
				{
					try
					{
						$connection->queryExecute("ALTER TABLE `b_translate_phrase` DROP INDEX `IXF_TRNSL_PHR`");
					}
					catch (SqlException $exception)
					{}
					$params['index'] = true;
					$params['number']++;
					$found = true;
				}
				elseif ($params['field'] === false)
				{
					try
					{
						$connection->queryExecute("ALTER TABLE `b_translate_phrase` DROP COLUMN `PHRASE`");
					}
					catch (SqlException $exception)
					{}
					$params['field'] = true;
					$params['number']++;
					$found = true;
				}
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($params));
				$return = self::CONTINUE_EXECUTION;
			}

			$result['progress'] = floor($params['number'] * 100 / $params['count']);
			$result['steps'] = $params['number'];

			if ($found === false)
			{
				Option::delete(self::$moduleId, ['name' => self::OPTION_NAME]);
			}
		}
		
		return $return;
	}
}
