<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class TimemanReport extends Provider
{
	const PROVIDER_ID = 'TIMEMAN_REPORT';
	const CONTENT_TYPE_ID = 'TIMEMAN_REPORT';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('report');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		return $provider;
	}

	public function initSourceFields()
	{
		$timemanReportId = $this->entityId;

		if (
			$timemanReportId > 0
			&& Loader::includeModule('timeman')
		)
		{
			$res = \CTimeManReportFull::getById(intval($timemanReportId));
			if ($timemanReport = $res->fetch())
			{
				$this->setSourceFields($timemanReport);

				$userName = '';
				$res = \CUser::getById($timemanReport["USER_ID"]);
				if ($userFields = $res->fetch())
				{
					$userName = \CUser::formatName(
						\CSite::getNameFormat(),
						$userFields,
						true,
						false
					);
				}

				$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_TIMEMAN_REPORT_TITLE', array(
					'#USER_NAME#' => $userName,
					'#DATE#' => FormatDate('j F', MakeTimeStamp($timemanReport['DATE_FROM']))." - ".FormatDate('j F', MakeTimeStamp($timemanReport['DATE_TO']))
				)));

//				$this->setSourceDescription();
			}
		}
	}

	public static function canRead($params)
	{
		return true;
	}

	protected function getPermissions(array $post)
	{
		$result = self::PERMISSION_READ;

		return $result;
	}

	public function getLiveFeedUrl()
	{
		$pathToLogEntry = '';

		$logId = $this->getLogId();
		if ($logId)
		{
			$pathToLogEntry = Option::get('socialnetwork', 'log_entry_page', '', $this->getSiteId());
			if (!empty($pathToLogEntry))
			{
				$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, array("log_id" => $logId));
			}
		}
		return $pathToLogEntry;
	}
}