<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Timeman\Model\Worktime\Record\WorktimeRecordTable;

Loc::loadMessages(__FILE__);

final class TimemanEntry extends Provider
{
	public const PROVIDER_ID = 'TIMEMAN_ENTRY';
	public const CONTENT_TYPE_ID = 'TIMEMAN_ENTRY';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'timeman_entry' ];
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

		$timemanEntryId = $this->entityId;

		if ($timemanEntryId <= 0)
		{
			return;
		}

		if (isset($cache[$timemanEntryId]))
		{
			$timemanEntry = $cache[$timemanEntryId];
		}
		elseif (Loader::includeModule('timeman'))
		{
			$res = WorktimeRecordTable::getList([
				'filter' => [
					'ID' => $timemanEntryId
				],
				'select' => [ 'ID', 'USER_ID', 'DATE_START', 'APPROVED_BY' ]
			]);
			$timemanEntry = $res->fetch();
			$cache[$timemanEntryId] = $timemanEntry;
		}

		if (!empty($timemanEntry))
		{
			$this->setSourceFields($timemanEntry);

			$userName = '';
			$res = \CUser::getById($timemanEntry['USER_ID']);
			if ($userFields = $res->fetch())
			{
				$userName = \CUser::formatName(
					\CSite::getNameFormat(),
					$userFields,
					true,
					false
				);
			}

			$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_TIMEMAN_ENTRY_TITLE', [
				'#USER_NAME#' => $userName,
				'#DATE#' => FormatDate('j F', makeTimeStamp($timemanEntry['DATE_START']))
			]));
		}
		else
		{
			$this->setSourceTitle($this->getUnavailableTitle());
		}
	}

	public function getPinnedDescription(): string
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$timemanEntry = $this->getSourceFields();
		if (empty($timemanEntry))
		{
			return $result;
		}

		return (string)Loc::getMessage((int)$timemanEntry['APPROVED_BY'] <= 0 ? 'SONET_LIVEFEED_TIMEMAN_ENTRY_PINNED_DESCRIPTION' : 'SONET_LIVEFEED_TIMEMAN_ENTRY_PINNED_DESCRIPTION2');
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
				$pathToLogEntry = \CComponentEngine::makePathFromTemplate($pathToLogEntry, [ 'log_id' => $logId ]);
			}
		}
		return $pathToLogEntry;
	}
}
