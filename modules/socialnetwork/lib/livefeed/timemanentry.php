<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

final class TimemanEntry extends Provider
{
	const PROVIDER_ID = 'TIMEMAN_ENTRY';
	const CONTENT_TYPE_ID = 'TIMEMAN_ENTRY';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('timeman_entry');
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
		$timemanEntryId = $this->entityId;

		if (
			$timemanEntryId > 0
			&& Loader::includeModule('timeman')
		)
		{
			$res = \CTimeManEntry::getById(intval($timemanEntryId));
			if ($timemanEntry = $res->fetch())
			{
				$this->setSourceFields($timemanEntry);

				$userName = '';
				$res = \CUser::getById($timemanEntry["USER_ID"]);
				if ($userFields = $res->fetch())
				{
					$userName = \CUser::formatName(
						\CSite::getNameFormat(),
						$userFields,
						true,
						false
					);
				}

				$this->setSourceTitle(Loc::getMessage('SONET_LIVEFEED_TIMEMAN_ENTRY_TITLE', array(
					'#USER_NAME#' => $userName,
					'#DATE#' => FormatDate('j F', MakeTimeStamp($timemanEntry['DATE_START']))
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