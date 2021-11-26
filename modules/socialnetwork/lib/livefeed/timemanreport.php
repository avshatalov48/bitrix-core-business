<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class TimemanReport extends Provider
{
	public const PROVIDER_ID = 'TIMEMAN_REPORT';
	public const CONTENT_TYPE_ID = 'TIMEMAN_REPORT';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'report' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public function getCommentProvider(): Provider
	{
		return new ForumPost();
	}

	public function initSourceFields()
	{
		static $cache = [];

		$timemanReportId = $this->entityId;

		if ($timemanReportId <= 0)
		{
			return;
		}

		if (isset($cache[$timemanReportId]))
		{
			$timemanReport = $cache[$timemanReportId];
		}
		elseif (Loader::includeModule('timeman'))
		{
			$res = \CTimeManReportFull::getById($timemanReportId);
			$timemanReport = $res->fetch();
			$cache[$timemanReportId] = $timemanReport;
		}

		if (!empty($timemanReport))
		{
			$this->setSourceFields($timemanReport);

			$userName = '';
			$res = \CUser::getById($timemanReport['USER_ID']);
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
		}
		else
		{
			$this->setSourceTitle($this->getUnavailableTitle());
		}
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getLiveFeedUrl(): string
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
