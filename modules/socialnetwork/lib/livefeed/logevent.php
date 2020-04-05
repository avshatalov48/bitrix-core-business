<?
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Main\Config\Option;

final class LogEvent extends Provider
{
	const PROVIDER_ID = 'SONET_LOG';
	const CONTENT_TYPE_ID = 'LOG_ENTRY';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('data');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
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

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\LogComment();
		return $provider;
	}

	public function initSourceFields()
	{
		$logId = $this->entityId;

		if ($logId > 0)
		{
			$res = LogTable::getList(array(
				'filter' => array(
					'=ID' => $logId,
					'@EVENT_ID' => $this->getEventId(),
				),
				'select' => array('ID', 'TITLE', 'MESSAGE', 'PARAMS')
			));

			if ($logEntryFields = $res->fetch())
			{
				$this->setLogId($logEntryFields['ID']);
				$this->setSourceFields($logEntryFields);
				$this->setSourceTitle($logEntryFields['TITLE']);

				$html = false;

				$entryParams = unserialize($logEntryFields['PARAMS']);

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
							list ($key, $value) = explode("=", $pair);
							$entryParams[$key] = $value;
						}
					}
				}

				if (
					!empty($entryParams["SCHEME_ID"])
					&& Loader::includeModule('xdimport')
				)
				{
					$res = \CXDILFScheme::getById($entryParams["SCHEME_ID"]);
					if ($schemeFields = $res->fetch())
					{
						$html = ($schemeFields["IS_HTML"] == "Y");
					}
				}

				if ($html)
				{
					$sanitizer = new \CBXSanitizer();
					$sanitizer->applyHtmlSpecChars(false);
					$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
					$this->setSourceDescription(htmlspecialcharsEx($sanitizer->sanitizeHtml(htmlspecialcharsback($logEntryFields['MESSAGE']))));
				}
				else
				{
					$this->setSourceDescription(htmlspecialcharsEx($logEntryFields["MESSAGE"]));
				}
			}
		}
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