<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

final class LogEvent extends Provider
{
	public const PROVIDER_ID = 'SONET_LOG';
	public const CONTENT_TYPE_ID = 'LOG_ENTRY';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'data' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getCommentProvider(): Provider
	{
		return new LogComment();
	}

	public function initSourceFields()
	{
		static $cache = [];
		static $schemeCache = [];

		$logId = $this->entityId;

		if ($logId <= 0)
		{
			return;
		}

		if (isset($cache[$logId]))
		{
			$logEntryFields = $cache[$logId];
		}
		else
		{
			$res = LogTable::getList([
				'filter' => [
					'=ID' => $logId,
					'@EVENT_ID' => $this->getEventId(),
				],
				'select' => [ 'ID', 'TITLE', 'MESSAGE', 'PARAMS' ]
			]);

			$logEntryFields = $res->fetch();
			$cache[$logId] = $logEntryFields;
		}

		if (empty($logEntryFields))
		{
			return;
		}

		$entryParams = unserialize($logEntryFields['PARAMS'], [ 'allowed_classes' => false ]);

		if (
			!is_array($entryParams)
			&& !empty($logEntryFields['PARAMS'])
		)
		{
			$tmp = explode("&", $logEntryFields['PARAMS']);
			if (is_array($tmp) && count($tmp) > 0)
			{
				$entryParams = array();
				foreach($tmp as $pair)
				{
					[$key, $value] = explode("=", $pair);
					$entryParams[$key] = $value;
				}
			}
		}

		$html = false;
		$logEntryFields['SCHEME_FIELDS'] = [];

		$schemeId = (is_array($entryParams) && isset($entryParams['SCHEME_ID']) ? (int)$entryParams['SCHEME_ID'] : 0);
		if ($schemeId > 0)
		{
			$schemeFields = [];
			if (isset($schemeCache[$schemeId]))
			{
				$schemeFields = $schemeCache[$schemeId];
			}
			elseif (Loader::includeModule('xdimport'))
			{
				$res = \CXDILFScheme::getById($schemeId);
				$schemeFields = $res->fetch();
				$schemeCache[$schemeId] = $schemeFields;
			}

			$logEntryFields['SCHEME_FIELDS'] = $schemeFields;
		}

		$this->setLogId($logEntryFields['ID']);
		$this->setSourceFields($logEntryFields);
		$this->setSourceTitle($logEntryFields['TITLE']);

		if (
			!empty($logEntryFields['SCHEME_FIELDS'])
			&& isset($logEntryFields['SCHEME_FIELDS']['IS_HTML'])
		)
		{
			$html = ($logEntryFields['SCHEME_FIELDS']['IS_HTML'] === "Y");
		}

		if ($html)
		{
			$description = htmlspecialcharsback($logEntryFields['MESSAGE']);
			$sanitizer = new \CBXSanitizer();
			$sanitizer->applyDoubleEncode(false);
			$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
			$this->setSourceDescription($sanitizer->sanitizeHtml($description));
		}
		else
		{
			$this->setSourceDescription(htmlspecialcharsEx($logEntryFields['MESSAGE']));
		}
	}

	public function getPinnedTitle(): string
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$logEntryFields = $this->getSourceFields();
		if (empty($logEntryFields))
		{
			return $result;
		}

		$result = Loc::getMessage('SONET_LIVEFEED_LOG_DATA_PINNED_TITLE');

		if (
			!empty($logEntryFields['SCHEME_FIELDS'])
			&& isset($logEntryFields['SCHEME_FIELDS']['NAME'])
		)
		{
			$result = Loc::getMessage('SONET_LIVEFEED_LOG_DATA_PINNED_TITLE2', [
				'#TITLE#' => $logEntryFields['SCHEME_FIELDS']['NAME']
			]);
		}

		return $result;
	}

	public function getPinnedDescription()
	{
		$result = '';

		if (empty($this->sourceFields))
		{
			$this->initSourceFields();
		}

		$logEntryFields = $this->getSourceFields();
		if (empty($logEntryFields))
		{
			return $result;
		}

		$html = false;

		if (
			!empty($logEntryFields['SCHEME_FIELDS'])
			&& isset($logEntryFields['SCHEME_FIELDS']['IS_HTML'])
		)
		{
			$html = ($logEntryFields['SCHEME_FIELDS']['IS_HTML'] === "Y");
		}

		if ($html)
		{
			$result = htmlspecialcharsback($logEntryFields['MESSAGE']);
			$result = truncateText(\CTextParser::clearAllTags($result), 100);
		}
		else
		{
			$result = truncateText(htmlspecialcharsEx($logEntryFields['MESSAGE']), 100);
		}

		return $result;
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

	public function getSuffix(): string
	{
		return '2';
	}
}