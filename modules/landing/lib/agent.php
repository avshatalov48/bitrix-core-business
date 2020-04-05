<?php
namespace Bitrix\Landing;

class Agent
{
	/**
	 * Clear recycle bin.
	 * @param int $days After this time items will be deleted.
	 * @return string
	 */
	public static function clearRecycle($days = null)
	{
		$days = !is_null($days)
				? (int) $days
				: (int) Manager::getOption('deleted_lifetime_days');

		$date = new \Bitrix\Main\Type\DateTime;
		$date->add('-' . $days . ' days');
		$folders = [];

		// first delete landings
		$res = Landing::getList([
			'select' => [
				'ID', 'FOLDER'
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
				'=SITE.DELETED' => ['Y', 'N']
			],
			'order' => [
				'DATE_MODIFY' => 'desc'
			]
		]);
		while ($row = $res->fetch())
		{
			if ($row['FOLDER'] == 'Y')
			{
				$folders[] = $row['ID'];
				continue;
			}
			$resDel = Landing::delete($row['ID'], true);
			$resDel->isSuccess();// for trigger
		}

		// delete from folders
		if ($folders)
		{
			$res = Landing::getList([
				'select' => [
					'ID'
				],
				'filter' => [
					'FOLDER_ID' => $folders,
					'=DELETED' => ['Y', 'N'],
					'=SITE.DELETED' => ['Y', 'N']
				],
				'order' => [
					'DATE_MODIFY' => 'desc'
				]
			]);
			while ($row = $res->fetch())
			{
				array_unshift($folders, $row['ID']);
			}
			foreach ($folders as $folderId)
			{
				$resDel = Landing::delete($folderId, true);
				$resDel->isSuccess();// for trigger
			}
		}

		// then delete sites
		$res = Site::getList([
			'select' => [
				'ID'
			],
			'filter' => [
				'=DELETED' => 'Y',
				'<DATE_MODIFY' => $date
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