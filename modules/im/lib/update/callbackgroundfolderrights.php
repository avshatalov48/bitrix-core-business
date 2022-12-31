<?php

namespace Bitrix\Im\Update;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Update\Stepper;


final class CallBackgroundFolderRights extends Stepper
{
	public const OPTION_NAME = 'im_call_background_folder_rights';
	public const LIMIT = 100;
	protected static $moduleId = "im";

	public function execute(array &$option)
	{
		$stepResult = Stepper::FINISH_EXECUTION;

		if (!\Bitrix\Main\Loader::includeModule('disk'))
		{
			return $stepResult;
		}

		$storageId = (int)Option::get('im', 'disk_storage_id', 0);
		if ($storageId <= 0)
		{
			return $stepResult;
		}

		$topFolder = \Bitrix\Disk\Internals\FolderTable::getRow([
			'select' => ['ID'],
			'filter' => [
				'=STORAGE_ID' => $storageId,
				'=TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER,
				'=CODE' => 'CALL_BACKGROUND',
			]
		]);
		if (!$topFolder || (int)$topFolder['ID'] <= 0)
		{
			return $stepResult;
		}
		$topFolderId = (int)$topFolder['ID'];

		$stepVars = Option::get(self::$moduleId, self::OPTION_NAME, '');
		$stepVars = ($stepVars !== "" ? @unserialize($stepVars, ['allowed_classes' => false]) : []);
		$stepVars = (is_array($stepVars) ? $stepVars : []);

		$filter = [
			'=STORAGE_ID' => $storageId,
			'=TYPE' => \Bitrix\Disk\Internals\FolderTable::TYPE_FOLDER,
			'=PARENT_ID' => $topFolderId,
			'CODE' => 'CALL_BACKGROUND_%',
		];

		if (empty($stepVars))
		{
			$stepVars = [
				'lastId' => 0,
				'number' => 0,
				'count' => (int)\Bitrix\Disk\Internals\FolderTable::getCount($filter),
			];
		}

		if ($stepVars['count'] > 0)
		{
			$option['progress'] = 1;
			$option['steps'] = '';
			$option['count'] = $stepVars['count'];

			$filter['>ID'] = $stepVars['lastId'];

			$diskRightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();
			$accessTaskFull = $diskRightsManager->getTaskIdByName(\Bitrix\Disk\RightsManager::TASK_FULL);

			$cursor = \Bitrix\Disk\Folder::getList([
				'select' => ['ID', 'CREATED_BY'],
				'filter' => $filter,
				'order' => ['ID' => 'ASC'],
				'limit' => self::LIMIT,
			]);
			$found = false;
			foreach ($cursor as $row)
			{
				$folder = \Bitrix\Disk\Folder::loadById((int)$row['ID']);
				if ($folder instanceof \Bitrix\Disk\Folder)
				{
					$diskRightsManager->append(
						$folder,
						[
							[
								'ACCESS_CODE' => 'U'. (int)$row['CREATED_BY'],
								'TASK_ID' => $accessTaskFull,
							]
						]
					);
				}
				$stepVars['number'] ++;
				$stepVars['lastId'] = (int)$row['ID'];
				$found = true;
			}

			if ($found)
			{
				Option::set(self::$moduleId, self::OPTION_NAME, serialize($stepVars));
				$stepResult = Stepper::CONTINUE_EXECUTION;
			}
			else
			{
				Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);
			}

			$option['progress'] = round($stepVars['number'] * 100 / $stepVars['count']);
			$option['steps'] = $stepVars['number'];
		}
		elseif ($stepVars['count'] == 0)
		{
			Option::delete(self::$moduleId, ["name" => self::OPTION_NAME]);
		}

		return $stepResult;
	}
}