<?php

namespace Bitrix\Socialservices\Integration\Zoom;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\IO;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Socialservices\ZoomMeetingRecordingTable;

class DownloadAgent
{
	protected const MAX_ATTEMPTS = 3;

	public static function scheduleDownload(int $activityId, int $recordingId, $siteId = SITE_ID)
	{
		if ($activityId > 0 && $recordingId > 0)
		{
			$siteId = Application::getConnection()->getSqlHelper()->forSql($siteId);
			\CAgent::AddAgent(
				__CLASS__."::run({$activityId}, {$recordingId}, '{$siteId}');",
				'socialservices',
				'N',
				3600,
				'',
				'Y',
				\ConvertTimeStamp(time() + \CTimeZone::GetOffset(), 'FULL')
			);
		}
	}

	public static function run($activityId, $recordingId, $siteId = SITE_ID, $attempt = 0)
	{
		$recordingFields = ZoomMeetingRecordingTable::getRowById($recordingId);
		if (!is_array($recordingFields))
		{
			return '';
		}

		$attachResult = static::attach($activityId, $recordingFields, $siteId);
		if ($attachResult->isSuccess())
		{
			return '';
		}

		$attempt++;
		if ($attempt > static::MAX_ATTEMPTS)
		{
			return '';
		}
		$siteId = Application::getConnection()->getSqlHelper()->forSql($siteId);
		return static::class."::run({$activityId}, {$recordingId}, '{$siteId}', {$attempt});";
	}

	public static function attach($activityId, array $recordingFields, $siteId = SITE_ID)
	{
		$result = new Result();

		$validSymbolsPattern = '/^[a-z0-9\-]+$/i';
		if (!preg_match($validSymbolsPattern, $recordingFields['EXTERNAL_ID']))
		{
			return $result->addError(new Error('Can not create recording file name, external_id contains invalid symbols'));
		}
		if (!preg_match($validSymbolsPattern, $recordingFields['FILE_TYPE']))
		{
			return $result->addError(new Error('Can not create recording file name, external_id contains invalid symbols'));
		}
		if (!Loader::includeModule('crm'))
		{
			return $result->addError(new Error('CRM module is not installed'));
		}
		$fileName = mb_strtolower("{$recordingFields['EXTERNAL_ID']}.{$recordingFields['FILE_TYPE']}");
		$downloadResult = static::download($recordingFields['DOWNLOAD_URL'], $recordingFields['DOWNLOAD_TOKEN'], $fileName);
		if(!$downloadResult->isSuccess())
		{
			return $result->addErrors($downloadResult->getErrors());
		}

		$activityFields = \CCrmActivity::GetByID($activityId, false);
		if(!$activityFields)
		{
			return $result->addError(new Error('Activity is not found'));
		}
		$responsible = $activityFields['RESPONSIBLE_ID'];

		$tempPath = $downloadResult->getData()['file'];
		$recordFile = \CFile::MakeFileArray($tempPath, $recordingFields['FILE_TYPE']);
		$recordFile['MODULE_ID'] = 'crm';
		$saveResult = DiskHelper::saveFile($recordFile, $responsible, $siteId);

		if(!$saveResult->isSuccess())
		{
			return $result->addErrors($saveResult->getErrors());
		}

		$fileId = $saveResult->getData()['fileId'];
		ZoomMeetingRecordingTable::update($recordingFields['ID'], [
			'FILE_ID' => $fileId
		]);

		$storageElementIds = \unserialize($activityFields['STORAGE_ELEMENT_IDS'], ['allowed_classes' => false]) ?: [];
		$storageElementIds[] = $fileId;

		$activityFields['STORAGE_TYPE_ID'] = \Bitrix\Crm\Integration\StorageType::Disk;
		$activityFields['STORAGE_ELEMENT_IDS'] = $storageElementIds;

		$updateResult = \CCrmActivity::Update($activityId, $activityFields, false);
		if (!$updateResult)
		{
			return $result->addError(new Error(\CCrmActivity::GetLastErrorMessage()));
		}
		return $result;
	}

	public static function download(string $recordingUrl, string $downloadToken, string $fileName): Result
	{
		$result = new Result();

		$tempPath = \CFile::GetTempName('', $fileName);
		IO\Directory::createDirectory(IO\Path::getDirectory($tempPath));
		if(IO\Directory::isDirectoryExists(IO\Path::getDirectory($tempPath)) === false)
		{
			return $result->addError(new Error("Error creating temporary directory {$tempPath}"));
		}
		$tempFile = new IO\File($tempPath);
		$handler = $tempFile->open("w+");
		if($handler === false)
		{
			return $result->addError(new Error("Error opening temporary file {$tempPath}"));
		}

		$http = new HttpClient();
		$http->setHeader("Authorization",  "Bearer {$downloadToken}");
		$http->setOutputStream($handler);
		$http->query("GET", $recordingUrl);
		$statusCode = $http->getStatus();
		if($statusCode !== 200)
		{
			return $result->addError(new Error("Service response status code is {$statusCode}"));
		}

		$http->getResult();
		$tempFile->close();

		return $result->setData([
			'file' => $tempPath
		]);
	}
}