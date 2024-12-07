<?php

namespace Bitrix\Bizproc\Worker\Task;

use Bitrix\Main;
use Bitrix\Main\Application;

class ClearSearchContentAgent
{
	protected const CLEAR_LOG_SELECT_LIMIT = 50000;
	protected const CLEAR_LOG_DELETE_LIMIT = 1000;

	public static function getName()
	{
		return static::class . '::execute();';
	}

	public static function execute()
	{
		$days = 180;
		if (!Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$days = (int)Main\Config\Option::get('bizproc', 'search_cleanup_days', 180);
		}

		static::clear($days);

		return static::getName();
	}

	private static function clear(int $days): void
	{
		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		$limit = static::CLEAR_LOG_SELECT_LIMIT;
		$partLimit = static::CLEAR_LOG_DELETE_LIMIT;
		$sqlIntervalLt = $helper->addDaysToDateTime(-1 * $days);
		$sqlIntervalGt = $helper->addDaysToDateTime(-1 * ($days + 1));

		$strSql = "SELECT st.TASK_ID FROM b_bp_task_search_content st "
			. "INNER JOIN b_bp_task t ON (st.TASK_ID = t.ID) "
			. "WHERE t.MODIFIED < {$sqlIntervalLt} "
			. "AND t.MODIFIED > {$sqlIntervalGt} LIMIT {$limit}";
		$ids = $connection->query($strSql)->fetchAll();

		if (!$ids)
		{
			return;
		}

		while ($partIds = array_splice($ids, 0, $partLimit))
		{
			$inSql = implode(",", array_column($partIds, 'TASK_ID'));
			$connection->query(
				sprintf(
					'DELETE from b_bp_task_search_content WHERE TASK_ID IN(%s)',
					$inSql,
				)
			);
		}
	}
}
