<?php


namespace Bitrix\SocialServices\Integration\Zoom;


use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\Uri;
use Bitrix\Socialservices\ZoomMeetingRecordingTable;
use Bitrix\Socialservices\ZoomMeetingTable;

class Recording
{
	public const RECORDING_KIND_VIDEO = 'VIDEO';
	public const RECORDING_KIND_AUDIO = 'AUDIO';

	public const LENGTH_FORMAT_SHORT = 'short';
	public const LENGTH_FORMAT_FULL = 'full';

	public static function getRecordings($conferenceId): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Module socialservices is not installed.'));
		}

		$meetingResult = ZoomMeetingTable::getById($conferenceId);

		if ($meetingData = $meetingResult->fetch())
		{
			$recordingsResult = ZoomMeetingRecordingTable::getList([
				'select' => ['*'],
				'filter' => [
					'=MEETING_ID' => $meetingData['ID'],
				],
				'order' => [
					'START_DATE' => 'ASC'
				]
			]);
			$currentStartDate = '';
			while ($recording = $recordingsResult->fetch())
			{
				if ($currentStartDate != $recording['START_DATE']->format(DATE_ATOM))
				{
					$currentStartDate = $recording['START_DATE']->format(DATE_ATOM);
					$allRecordings[$currentStartDate] = [];
				}
				$recording['LENGTH'] = static::getRecordingLength($recording['START_DATE'], $recording['END_DATE']);
				$recording['LENGTH_FORMATTED'] = static::formatLength($recording['LENGTH']);
				$recording['LENGTH_HUMAN'] = static::formatLength($recording['LENGTH'], static::LENGTH_FORMAT_FULL);

				$recording['END_DATE_TS'] = $recording['END_DATE']->getTimestamp();

				if ($recording['FILE_ID'] > 0 && Loader::includeModule('disk') && $file = \Bitrix\Disk\File::loadById($recording['FILE_ID']))
				{
					$recording['DOWNLOAD_URL'] = \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlForDownloadFile($file, true);
				}
				else
				{
					$parsedDownloadUrl = new Uri($recording['DOWNLOAD_URL']);
					$recording['DOWNLOAD_URL'] = $parsedDownloadUrl->addParams(['access_token' => $recording['DOWNLOAD_TOKEN']])->__toString();
				}
				$allRecordings[$currentStartDate][static::getRecordingKind($recording['FILE_TYPE'])] = $recording;
			}
		}
		if (!empty($allRecordings))
		{
			$result->setData(array_values($allRecordings));
		}

		return $result;
	}

	public static function getRecordingKind($fileType): ?string
	{
		switch ($fileType)
		{
			case 'MP4':
				return static::RECORDING_KIND_VIDEO;
			case 'M4A':
				return static::RECORDING_KIND_AUDIO;
			default:
				return null;
		}
	}

	/**
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @return int
	 */
	public static function getRecordingLength(DateTime $startDate, DateTime $endDate): int
	{
		return $endDate->getTimestamp() - $startDate->getTimestamp();
	}

	public static function formatLength(int $lengthSeconds, $format = self::LENGTH_FORMAT_SHORT): string
	{
		$hours = intdiv($lengthSeconds,  3600);
		$lengthSeconds -= $hours * 3600;
		$minutes = intdiv($lengthSeconds, 60);
		$seconds = $lengthSeconds - $minutes * 60;

		if ($format === self::LENGTH_FORMAT_FULL)
		{
			if($hours)
			{
				$result = Loc::getMessage("CRM_ZOOM_CONFERENCE_HOUR_F" . static::getNumericSuffix($hours), ["#VALUE#" => $hours]) . " ";
			}
			else
			{
				$result = "";
			}
			$result .= Loc::getMessage("CRM_ZOOM_CONFERENCE_MINUTE_F" . static::getNumericSuffix($minutes), ["#VALUE#" => $minutes]) . " ";
			$result .= Loc::getMessage("CRM_ZOOM_CONFERENCE_SECOND_F" . static::getNumericSuffix($seconds), ["#VALUE#" => $seconds]);
		}
		else
		{
			$result = $hours ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : "";
			$minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT);
			$seconds = str_pad($seconds, 2, "0", STR_PAD_LEFT);
			$result .= "$minutes:$seconds";
		}

		return $result;
	}

	protected static function getNumericSuffix($number): int
	{
		$keys = [2, 0, 1, 1, 1, 2];
		$mod = $number % 100;
		return $mod > 4 && $mod < 20 ? 2 : $keys[min($mod%10, 5)];
	}

	public static function onRecordingStopped(int $conferenceId, array $recordingsData): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Module socialservices is not installed.'));
		}
		$conferenceRecord = ZoomMeetingTable::getRowByExternalId($conferenceId);
		if (!$conferenceRecord)
		{
			return $result->addError(new Error('Conference is not found'));
		}
		$updateResult = ZoomMeetingTable::update($conferenceRecord['ID'], [
			'HAS_RECORDING' => 'Y'
		]);
		if (!$updateResult->isSuccess())
		{
			return $result->addErrors($updateResult->getErrors());
		}
		return $result;
	}

	public static function delete(int $conferenceId): Result
	{
		$result = new Result();
		if (!Loader::includeModule('socialservices'))
		{
			return $result->addError(new Error('Module socialservices is not installed.'));
		}

		$recordingsResult = ZoomMeetingRecordingTable::getList([
			'select' => ['*'],
			'filter' => [
				'=MEETING_ID' => $conferenceId,
			],
		]);

		while ($recording = $recordingsResult->fetch())
		{
			$deleteRecordingsResult = ZoomMeetingRecordingTable::delete($recording['ID']);
			if (!$deleteRecordingsResult->isSuccess())
			{
				return $result->addErrors($deleteRecordingsResult->getErrors());
			}
		}

		return $result;
	}
}