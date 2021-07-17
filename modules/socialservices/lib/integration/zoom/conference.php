<?php


namespace Bitrix\SocialServices\Integration\Zoom;

use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Socialservices\ZoomMeetingRecordingTable;
use Bitrix\Socialservices\ZoomMeetingTable;

class Conference
{
	public const MEETING_SCHEDULED_TYPE = 2;
	public const ACTIVITY_ENTITY_TYPE = 'activity';
	public const ZOOM_AUDIO_ONLY = 'audio_only';

	public static function isAvailable(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return false;
		}

		$authManager = new \CSocServAuthManager();
		$activeSocServ = $authManager->GetActiveAuthServices([]);
		if (!isset($activeSocServ['zoom']))
		{
			return false;
		}

		return \Bitrix\Bitrix24\Feature::isFeatureEnabled("crm_zoom_integration");
	}

	public static function create($userId, $data): Result
	{
		$result = new Result();
		$preparedDataResult = self::prepareConferenceParams($data);
		if (!$preparedDataResult->isSuccess())
		{
			return $result->addErrors($preparedDataResult->getErrors());
		}

		$preparedData = $preparedDataResult->getData();
		$zoomOAuth = new \CSocServZoom($userId);
		$createResult = $zoomOAuth->createConference($preparedData);
		if(!$createResult->isSuccess())
		{
			return $result->addErrors($createResult->getErrors());
		}

		return $createResult;
	}

	public static function update(int $userId, array $updateParams): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Socialservices module is not installed'));
		}

		$zoomOAuth = new \CSocServZoom($userId);
		$updateResult = $zoomOAuth->updateConference($updateParams);

		if (!$updateResult->isSuccess())
		{
			return $result->addErrors($updateResult->getErrors());
		}
		$conferenceData = $updateResult->getData();

		return $result->setData($conferenceData);
	}

	public static function delete(int $conferenceId): Result
	{
		$result = new Result();

		if ($conferenceId <= 0)
		{
			return $result->addError(new Error('Incorrect conference id'));
		}

		$deleteResult = ZoomMeetingTable::delete($conferenceId);
		if (!$deleteResult->isSuccess())
		{
			return $result->addErrors($deleteResult->getErrors());
		}

		$deleteRecordingsResult = Recording::delete($conferenceId);
		if (!$deleteRecordingsResult->isSuccess())
		{
			return $result->addErrors($deleteRecordingsResult->getErrors());
		}

		return $result;
	}

	private static function prepareConferenceParams(array $data): Result
	{
		$result = new Result();

		if (empty($data['conferenceTitle']))
		{
			$result->addError(new Error('Invalid entity type'));
		}

		$timestampStart = $data['timestampStart'] / 1000;
		if ($timestampStart < time())
		{
			$result->addError(new Error('Invalid date'));
		}

		$data['duration'] = (int)$data['duration'];
		if ($data['duration'] <= 0)
		{
			$result->addError(new Error('Invalid duration'));
		}

		if (!$result->isSuccess())
		{
			return $result;
		}

		$dateTimeStart = \Bitrix\Main\Type\DateTime::createFromTimestamp($timestampStart);
		$data['start_time'] = $dateTimeStart->setTimeZone(new \DateTimeZone('UTC'))->format(DATE_ATOM);

		if ($data['durationType'] === 'h')
		{
			$data['duration'] *= 60;
		}

		$randomSequence = new \Bitrix\Main\Type\RandomSequence($data['conferenceTitle'].$data['start_time']);
		$password = $randomSequence->randString(10);

		$result->setData([
			'topic' => $data['conferenceTitle'],
			'type' => self::MEETING_SCHEDULED_TYPE,
			'start_time' => $data['start_time'],
			'duration' => $data['duration'],
			'password' => $password,
			'timezone' => 'UTC',
		]);

		return $result;
	}

	public static function getInfo($conferenceId): Result
	{
		$result = new Result();

		$conferenceRecord = ZoomMeetingTable::getRowByExternalId($conferenceId);
		if (!empty($conferenceRecord))
		{
			$result->setData($conferenceRecord);
		}
		else
		{
			$result->addError(new Error('No conference data'));
		}

		return $result;
	}

	public static function setJoin(int $conferenceId): Result
	{
		$result = new Result();

		$params = [
			'JOINED' => true,
		];

		$getListResult = ZoomMeetingTable::getList([
			'filter' => [
				'=CONFERENCE_EXTERNAL_ID' => $conferenceId,
				'!=JOINED' => 'Y',
			],
			'select' => ['ENTITY_ID','ENTITY_TYPE_ID','ID']
		]);

		if ($meeting = $getListResult->fetch())
		{
			$updateResult = ZoomMeetingTable::update($meeting['ID'], $params);
			if (!$updateResult->isSuccess())
			{
				$result->addError(new Error('Error while update join status.'));
			}
			if ($updateResult->isSuccess() && $updateResult->getAffectedRowsCount() === 0)
			{
				$result->addError(new Error('Error: status has already been updated.'));
			}

			$result->setData($meeting);
		}
		else
		{
			$result->addError(new Error('No conference to update'));
		}


		return $result;
	}

	public static function setEnd(int $conferenceId): Result
	{
		$result = new Result();

		$params = [
			'CONFERENCE_ENDED' => (new DateTime()),
		];

		$meeting = ZoomMeetingTable::getRowByExternalId($conferenceId);
		if (!$meeting)
		{
			return $result->addError(new Error("Meeting {$conferenceId} is not found"));
		}
		$updateResult = ZoomMeetingTable::update($meeting['ID'], $params);
		if (!$updateResult->isSuccess())
		{
			$result->addError(new Error('Error while update end status.'));
		}
		else
		{
			$result->setData($meeting);
		}

		return $result;
	}


	public static function bindActivity(array $conferenceData, int $activityId): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Module socialservices is not installed.'));
		}

		$conferenceInfo = self::getInfo($conferenceData['id']);
		if ($conferenceInfo->isSuccess())
		{
			$conference = $conferenceInfo->getData();
			$params = [
				'ENTITY_TYPE_ID' => self::ACTIVITY_ENTITY_TYPE,
				'ENTITY_ID' => $activityId,
			];

			$result = ZoomMeetingTable::update($conference['ID'], $params);
			if (!$result->isSuccess())
			{
				$result->addError(new Error('Error while saving new zoom conference.'));
			}
		}
		else
		{
			$result->addError(new Error('Could not get conference info'));
		}

		return $result;
	}

	/**
	 * @param int $conferenceId
	 * @param $recordingsData
	 * @param $downloadToken
	 * @return Result
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function saveRecordings(int $conferenceId, array $recordingsData, $downloadToken): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Module socialservices is not installed.'));
		}

		if (!is_array($recordingsData['recording_files']))
		{
			return $result->addError(new Error('Error: recording_files key is not found in recording data'));
		}

		$meeting = ZoomMeetingTable::getRowByExternalId($conferenceId);
		if (!$meeting)
		{
			return $result->addError(new Error("Meeting {$conferenceId} is not found"));
		}
		$meetingId = $meeting['ID'];
		if ($meeting['HAS_RECORDING'] !== 'Y')
		{
			ZoomMeetingTable::update($meetingId, [
				'HAS_RECODING' => 'Y'
			]);
		}
		$crmInstalled = Loader::includeModule('crm');

		foreach ($recordingsData['recording_files'] as $record)
		{
			$startDateTimestamp = \DateTime::createFromFormat(DATE_ATOM, $record['recording_start'])->getTimestamp();
			$endDateTimestamp = \DateTime::createFromFormat(DATE_ATOM, $record['recording_end'])->getTimestamp();

			$recordFields = [
				'EXTERNAL_ID' => $record['id'],
				'MEETING_ID' => $meetingId, //?
				'START_DATE' => DateTime::createFromTimestamp($startDateTimestamp),
				'END_DATE' => DateTime::createFromTimestamp($endDateTimestamp),
				'FILE_TYPE' => $record['file_type'],
				'FILE_SIZE' => (int)$record['file_size'],
				'PLAY_URL' => $record['play_url'],
				'DOWNLOAD_URL' => $record['download_url'],
				'RECORDING_TYPE' => $record['recording_type'],
				'DOWNLOAD_TOKEN' => $downloadToken,
				'PASSWORD' => $recordingsData['password']
			];

			$addResult = ZoomMeetingRecordingTable::add($recordFields);
			if (!$addResult->isSuccess())
			{
				return $result->addErrors($addResult->getErrors());
			}
			$recordingId = $addResult->getId();
			if($crmInstalled && $meeting['ENTITY_TYPE_ID'] === static::ACTIVITY_ENTITY_TYPE && $meeting['ENTITY_ID'] > 0)
			{
				$activityId = (int)$meeting['ENTITY_ID'];
				if ($record['recording_type'] === static::ZOOM_AUDIO_ONLY)
				{
					DownloadAgent::scheduleDownload($activityId, $recordingId);
				}
			}
		}

		return $result;
	}
}