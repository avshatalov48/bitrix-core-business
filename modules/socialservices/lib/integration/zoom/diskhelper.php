<?php

namespace Bitrix\SocialServices\Integration\Zoom;

use Bitrix\Disk\Storage;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

class DiskHelper
{
	const ROOT_FOLDER_CODE = "ZOOM_RECORDS";

	public static function saveFile(array $arFile, int $userId, $siteId = SITE_ID): Result
	{
		$result = new Result();
		if (!Loader::includeModule("disk"))
		{
			return $result->addError(new Error("Disk module is not installed"));
		}

		$subFolder = self::getRecordsFolder((new DateTime())->format("Y-m"), $siteId);
		if (!$subFolder)
		{
			return $result->addError(new Error("Could not create records folder"));
		}

		$accessCodes = [];
		$rightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();
		$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);

		$accessCodes[] = [
			'ACCESS_CODE' => 'U' . $userId,
			'TASK_ID' => $fullAccessTaskId,
		];
		$accessCodes[] = [
			'ACCESS_CODE' => 'G1',
			'TASK_ID' => $fullAccessTaskId,
		];

		$fileModel = $subFolder->uploadFile(
			$arFile,
			['CREATED_BY' => $userId,],
			$accessCodes,
			true
		);

		if(!$fileModel)
		{
			if(count($subFolder->getErrors()) > 0)
			{
				return $result->addErrors($subFolder->getErrors());
			}
			return $result->addError(new Error("Unknown error while saving file"));
		}
		return $result->setData([
			'fileId' => $fileModel->getId()
		]);
	}

	public static function getRecordsFolder($folderName, $siteId = SITE_ID)
	{
		if(!\Bitrix\Main\Loader::includeModule('disk'))
			return false;

		$rootFolder = self::getRootFolder($siteId);
		if (!$rootFolder)
		{
			return false;
		}

		$subFolder = \Bitrix\Disk\Folder::load(array(
			'=NAME' => $folderName,
			'PARENT_ID' => $rootFolder->getId(),
		));

		if (!$subFolder)
		{
			$subFolder = $rootFolder->addSubFolder(array(
				'NAME' => $folderName,
				'CREATED_BY' => \Bitrix\Disk\SystemUser::SYSTEM_USER_ID
			));
		}

		return $subFolder;
	}

	public static function getRootFolder($siteId = SITE_ID)
	{
		$storageModel = self::getStorageModel($siteId);
		if (!$storageModel)
		{
			return false;
		}

		$folderModel = \Bitrix\Disk\Folder::load(array(
			'STORAGE_ID' => $storageModel->getId(),
			'PARENT_ID' => $storageModel->getRootObjectId(),
			'TYPE' => \Bitrix\Disk\Internals\ObjectTable::TYPE_FOLDER,
			'=CODE' => static::ROOT_FOLDER_CODE,
		));
		if ($folderModel)
		{
			return $folderModel;
		}

		// Creating root folder
		$folderModel = $storageModel->addFolder([
			'NAME' => static::getRootFolderName($siteId),
			'CODE' => static::ROOT_FOLDER_CODE,
			'CREATED_BY' => \Bitrix\Disk\SystemUser::SYSTEM_USER_ID
		], static::createRootFolderAccessCodes($storageModel));

		return $folderModel;
	}

	/**
	 * @param string $siteId
	 * @return \Bitrix\Disk\Storage || null
	 */
	public static function getStorageModel($siteId = SITE_ID): ?Storage
	{
		if ($siteId === '')
			return null;

		$storageModel = \Bitrix\Disk\Driver::getInstance()->getStorageByCommonId("shared_files_{$siteId}");
		return $storageModel ?: null;
	}

	public static function getRootFolderName($siteId): string
	{
		// Folder name
		$dbSite = \CSite::GetByID($siteId);
		$arSite = $dbSite->Fetch();
		IncludeModuleLangFile(__FILE__, $arSite && isset($arSite['LANGUAGE_ID']) ? $arSite['LANGUAGE_ID'] : false);

		return  Loc::getMessage("SOCSERV_ZOOM_RECORDS_ROOT_FOLDER");
	}

	public static function createRootFolderAccessCodes(Storage $storageModel)
	{
		// Access codes
		$rightsManager = \Bitrix\Disk\Driver::getInstance()->getRightsManager();
		$fullAccessTaskId = $rightsManager->getTaskIdByName($rightsManager::TASK_FULL);
		$rights = $rightsManager->getAllListNormalizeRights($storageModel->getRootObject());

		$accessCodes = array();
		foreach	($rights as $right)
		{
			$accessCodes[] = Array(
				'ACCESS_CODE' => $right['ACCESS_CODE'],
				'TASK_ID' => $right['TASK_ID'],
				'NEGATIVE' => 1
			);
		}
		$accessCodes[] = Array(
			'ACCESS_CODE' => 'G1',
			'TASK_ID' => $fullAccessTaskId,
		);

		return $accessCodes;
	}
}