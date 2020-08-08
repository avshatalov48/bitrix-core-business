<?php
namespace Bitrix\Landing;

class Agent
{
	/**
	 * Tech method for adding new unique agent.
	 * @param string $funcName Function name from this class.
	 * @param array $params Some params for agent function.
	 * @param int $time Time in seconds for executing period.
	 * @return void
	 */
	public static function addUniqueAgent($funcName, array $params = [], $time = 7200)
	{
		if (!method_exists(__CLASS__, $funcName))
		{
			return;
		}

		$funcName = __CLASS__ . '::' . $funcName . '(';
		foreach ($params as $value)
		{
			if (is_int($value))
			{
				$funcName .= $value . ',';
			}
			else if (is_string($value))
			{
				$funcName .= '\'' . $value . '\'' . ',';
			}
		}
		$funcName = trim($funcName, ',');
		$funcName .= ');';
		$res = \CAgent::getList(
			[],
			[
				'MODULE_ID' => 'landing',
				'NAME' => $funcName
			]
		);
		if (!$res->fetch())
		{
			\CAgent::addAgent($funcName, 'landing', 'N', $time);
		}
	}

	/**
	 * Clear recycle bin for scope.
	 * @param string $scope Scope code.
	 * @param int $days After this time items will be deleted.
	 * @return string
	 */
	public static function clearRecycleScope($scope, $days = null)
	{
		Site\Type::setScope($scope);

		self::clearRecycle($days);

		return __CLASS__ . '::' . __FUNCTION__ . '(\'' . $scope . '\');';
	}

	/**
	 * Clear recycle bin.
	 * @param int $days After this time items will be deleted.
	 * @return string
	 */
	public static function clearRecycle($days = null)
	{
		Rights::setGlobalOff();

		$days = !is_null($days)
				? (int) $days
				: (int) Manager::getOption('deleted_lifetime_days');

		$date = new \Bitrix\Main\Type\DateTime;
		$date->add('-' . $days . ' days');

		// first delete landings
		$res = Landing::getList([
			'select' => [
				'ID', 'FOLDER_ID'
			],
			'filter' => [
				[
					'LOGIC' => 'OR',
					[
						'=DELETED' => 'Y',
						'<DATE_MODIFY' => $date
					],
					[
						'=SITE.DELETED' => 'Y',
						'<SITE.DATE_MODIFY' => $date
					]
				],
				'=DELETED' => ['Y', 'N'],
				'=SITE.DELETED' => ['Y', 'N'],
				'CHECK_PERMISSIONS' => 'N'
			],
			'order' => [
				'DATE_MODIFY' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			if ($row['FOLDER_ID'])
			{
				Landing::update($row['ID'], [
					'FOLDER_ID' => 0
				]);
			}
			$resDel = Landing::delete($row['ID'], true);
			$resDel->isSuccess();// for trigger
		}

		// then delete sites
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=DELETED' => 'Y',
				'<DATE_MODIFY' => $date,
				'CHECK_PERMISSIONS' => 'N'
			],
			'order' => [
				'DATE_MODIFY' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			$resDel = Site::delete($row['ID']);
			$resDel->isSuccess();// for trigger
		}

		Rights::setGlobalOn();

		return __CLASS__ . '::' . __FUNCTION__ . '();';
	}

	/**
	 * Remove marked for deleting files.
	 * @param int $count Count of files wich will be deleted per once.
	 * @return string
	 */
	public static function clearFiles($count = null)
	{
		$count = !is_null($count) ? (int) $count : 30;

		File::deleteFinal($count);

		return __CLASS__ . '::' . __FUNCTION__ . '(' . $count . ');';
	}
}