<?php
namespace Bitrix\Landing;

use Bitrix\Main\Loader;
use Bitrix\Landing\Subtype;
use Bitrix\Landing\Internals\BlockTable;
use Bitrix\Crm\WebForm;
use Bitrix\Main\Type\DateTime;

class Agent
{
	/**
	 * Tech method for adding new unique agent.
	 * @param string $funcName Function name from this class.
	 * @param array $params Some params for agent function.
	 * @param int $time Time in seconds for executing period.
	 * @return void
	 */
	public static function addUniqueAgent(string $funcName, array $params = [], int $time = 7200): void
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
	 * Agent to remove one not resolved domain. Removes agent if such domains not exists.
	 * @return string
	 */
	public static function removeBadDomain(): string
	{
		$maxFailCount = 7;

		Rights::setGlobalOff();

		// only custom domain
		$filterDomains = array_map(function($domain)
		{
			return '%.' . $domain;
		}, Domain::B24_DOMAINS);
		$filterDomains[] = '%' . Manager::getHttpHost();

		$customDomainExist = false;
		$resDomain = Domain::getList([
			'select' => [
				'ID', 'DOMAIN', 'FAIL_COUNT'
			],
			'filter' => [
				'!DOMAIN' => $filterDomains
			],
			'limit' => 5,
			'order' => [
				'DATE_MODIFY' => 'asc'
			]
		]);
		while ($domain = $resDomain->fetch())
		{
			$customDomainExist = true;
			if (Domain\Register::isDomainActive($domain['DOMAIN']))
			{
				Domain::update($domain['ID'], [
					'FAIL_COUNT' => null
				])->isSuccess();
			}
			else
			{
				// remove domain
				if ($domain['FAIL_COUNT'] >= $maxFailCount - 1)
				{
					// wee need site for randomize domain
					$resSite = Site::getList([
						'select' => [
							'ID', 'DOMAIN_ID', 'DOMAIN_NAME' => 'DOMAIN.DOMAIN'
						],
						'filter' => [
							'DOMAIN_ID' => $domain['ID']
						]
					]);
					if ($rowSite = $resSite->fetch())
					{
						Debug::log('removeBadDomain-randomizeDomain', var_export($rowSite, true));
						Site::randomizeDomain($rowSite['ID']);
					}
					// site not exist, delete domain
					/*else
					{
						Debug::log('removeBadDomain-Domain::delete', var_export($rowSite, true));
						Domain::delete($domain['ID'])->isSuccess();
					}*/
				}
				else
				{
					Domain::update($domain['ID'], [
						'FAIL_COUNT' => intval($domain['FAIL_COUNT']) + 1
					])->isSuccess();
				}
			}
		}

		Rights::setGlobalOn();

		return $customDomainExist ? __CLASS__ . '::' . __FUNCTION__ . '();' : '';
	}

	/**
	 * Clear recycle bin for scope.
	 * @param string $scope Scope code.
	 * @param int|null $days After this time items will be deleted.
	 * @return string
	 */
	public static function clearRecycleScope(string $scope, ?int $days = null): string
	{
		Site\Type::setScope($scope);

		self::clearRecycle($days);

		return __CLASS__ . '::' . __FUNCTION__ . '(\'' . $scope . '\');';
	}

	/**
	 * Returns all sub folders for the folder.
	 * @param int $folderId Folder id.
	 * @return array
	 */
	protected static function getSubFolders(int $folderId): array
	{
		$folders = [];
		$res = Folder::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'PARENT_ID' => $folderId
			]
		]);
		while ($row = $res->fetch())
		{
			$folders[] = $row['ID'];
			$folders = array_merge($folders, self::getSubFolders($row['ID']));
		}
		return $folders;
	}

	/**
	 * Clear recycle bin.
	 * @param int|null $days After this time items will be deleted.
	 * @return string
	 */
	public static function clearRecycle(?int $days = null): string
	{
		Rights::setGlobalOff();

		$days = !is_null($days)
				? $days
				: (int) Manager::getOption('deleted_lifetime_days');

		$date = new \Bitrix\Main\Type\DateTime;
		$date->add('-' . $days . ' days');

		// check folders to delete
		$foldersToDelete = [-1];
		$res = Folder::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=DELETED' => 'Y',
				'<DATE_MODIFY' => $date
			]
		]);
		while ($row = $res->fetch())
		{
			$foldersToDelete[] = $row['ID'];
			$foldersToDelete = array_merge($foldersToDelete, self::getSubFolders($row['ID']));
		}

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
					],
					[
						'FOLDER_ID' => $foldersToDelete
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
			Lock::lockDeleteLanding($row['ID'], false);
			Landing::delete($row['ID'], true)->isSuccess();
		}

		// delete folders
		foreach (array_unique($foldersToDelete) as $folderId)
		{
			if ($folderId > 0)
			{
				Folder::delete($folderId)->isSuccess();
			}
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
			Lock::lockDeleteSite($row['ID'], false);
			Site::delete($row['ID'])->isSuccess();
		}

		Rights::setGlobalOn();

		return __CLASS__ . '::' . __FUNCTION__ . '();';
	}

	/**
	 * Remove marked for deleting files.
	 * @param int|null $count Count of files wich will be deleted per once.
	 * @return string
	 */
	public static function clearFiles(?int $count = null): string
	{
		$count = !is_null($count) ? $count : 30;

		File::deleteFinal($count);

		return __CLASS__ . '::' . __FUNCTION__ . '(' . $count . ');';
	}

	/**
	 * Send used rest statistic.
	 * @return string
	 */
	public static function sendRestStatistic(): string
	{
		if (
			\Bitrix\Main\Loader::includeModule('rest')
			&& is_callable(['\Bitrix\Rest\UsageStatTable', 'logLanding'])
		)
		{
			$statCode = [
				\Bitrix\Landing\PublicAction::REST_USAGE_TYPE_BLOCK => 'LANDING_BLOCK',
				\Bitrix\Landing\PublicAction::REST_USAGE_TYPE_PAGE => 'LANDING_PAGE',
			];
			$data = PublicAction::getRestStat(false, true);
			foreach ($data as $type => $stat)
			{
				if ($statCode[$type])
				{
					foreach ($stat as $clientId => $count)
					{
						\Bitrix\Rest\UsageStatTable::logLanding($clientId, $statCode[$type], $count);
					}
				}
			}
			\Bitrix\Rest\UsageStatTable::finalize();
		}

		return __CLASS__ . '::' . __FUNCTION__ . '();';
	}

	/**
	 * Marks all temporary files to delete.
	 * @return string
	 */
	public static function clearTempFiles(): string
	{
		$dateTime = new DateTime();

		$res = Internals\FileTable::getList([
			'select' => [
				'ID', 'FILE_ID'
			],
			'filter' => [
				'>FILE_ID' => 0,
				'=TEMP' => 'Y',
				'<FILE.TIMESTAMP_X' => $dateTime->add('-60 minute')
			]
		]);
		while ($row = $res->fetch())
		{
			Internals\FileTable::update($row['ID'], [
				'FILE_ID' => -1 * $row['FILE_ID']
			]);
		}

		return __CLASS__ . '::' . __FUNCTION__ . '();';
	}

	/**
	 * Tmp agent for rebuild form's blocks.
	 * @param int $lastLid Last item id.
	 * @return string
	 */
	public static function repairFormUrls(int $lastLid = 0): string
	{
		if (Loader::includeModule('crm'))
		{
			$formQuery = WebForm\Internals\LandingTable::query()
				->addSelect('FORM_ID')
				->addSelect('LANDING_ID')
				->addOrder('LANDING_ID')
				->setLimit(50)
				->where('LANDING_ID', '>', $lastLid)
				->exec()
			;
			$lastLid = 0;
			while ($form = $formQuery->fetch())
			{
				$blocksQuery = BlockTable::query()
					->addSelect('ID')
					->where('LID', $form['LANDING_ID'])
					->where('CODE', '66.90.form_new_default')
					->exec()
				;
				while ($block = $blocksQuery->fetch())
				{
					Subtype\Form::setFormIdToBlock($block['ID'], $form['FORM_ID']);
				}
				$lastLid = (int)$form['LANDING_ID'];
			}

			if ($lastLid > 0)
			{
				return __CLASS__ . '::' . __FUNCTION__ . '(' . $lastLid . ');';
			}
		}

		return '';
	}
}
