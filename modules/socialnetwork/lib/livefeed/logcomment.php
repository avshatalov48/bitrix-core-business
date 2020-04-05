<?
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Main\Config\Option;

final class LogComment extends Provider
{
	const PROVIDER_ID = 'SONET_COMMENT';
	const CONTENT_TYPE_ID = 'LOG_COMMENT';

	protected $logEventId = null;
	protected $logEntityType = null;
	protected $logEntityId = null;

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('data_comment', 'photoalbum_comment', 'intranet_new_user_comment', 'bitrix24_new_user_comment');
	}

	public function getType()
	{
		return Provider::TYPE_COMMENT;
	}

	public function initSourceFields()
	{
		$commentId = $this->entityId;

		if ($commentId > 0)
		{
			$logId = false;

			$res = LogCommentTable::getList(array(
				'filter' => array(
					'=ID' => $commentId,
					'@EVENT_ID' => $this->getEventId(),
				),
				'select' => array('LOG_ID', 'MESSAGE', 'SHARE_DEST')
			));
			if ($logComentFields = $res->fetch())
			{
				$logId = intval($logComentFields['LOG_ID']);
				$message = $logComentFields['MESSAGE'];
			}

			if ($logId)
			{
				$res = \CSocNetLog::getList(
					array(),
					array(
						'=ID' => $logId
					),
					false,
					false,
					array('ID', 'EVENT_ID'),
					array(
						"CHECK_RIGHTS" => "Y",
						"USE_FOLLOW" => "N",
						"USE_SUBSCRIBE" => "N"
					)
				);
				if ($logFields = $res->fetch())
				{
					$this->setLogId($logFields['ID']);
					$this->setSourceFields(array_merge($logComentFields, array('LOG_EVENT_ID' => $logFields['EVENT_ID'])));
					$this->setSourceDescription($logComentFields['MESSAGE']);

					$title = htmlspecialcharsback($logComentFields['MESSAGE']);
					$title = preg_replace(
						"/\[USER\s*=\s*([^\]]*)\](.+?)\[\/USER\]/is".BX_UTF_PCRE_MODIFIER,
						"\\2",
						$title
					);
					$CBXSanitizer = new \CBXSanitizer;
					$CBXSanitizer->delAllTags();
					$title = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", $CBXSanitizer->sanitizeHtml($title));
					$this->setSourceTitle(truncateText($title, 100));
					$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($commentId));
					$this->setSourceDiskObjects($this->getDiskObjects($commentId, $this->cloneDiskObjects));
					$this->setSourceOriginalText($logComentFields['MESSAGE']);
					$this->setSourceAuxData($logComentFields);
				}
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		global $USER_FIELD_MANAGER;
		static $cache = array();

		$messageId = $this->entityId;

		$result = array();
		$cacheKey = $messageId.$clone;

		if (isset($cache[$cacheKey]))
		{
			$result = $cache[$cacheKey];
		}
		else
		{
			$messageUF = $USER_FIELD_MANAGER->getUserFields("SONET_COMMENT", $messageId, LANGUAGE_ID);
			if (
				!empty($messageUF['UF_SONET_COM_DOC'])
				&& !empty($messageUF['UF_SONET_COM_DOC']['VALUE'])
				&& is_array($messageUF['UF_SONET_COM_DOC']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($messageUF['UF_SONET_COM_DOC']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $messageUF['UF_SONET_COM_DOC']['VALUE'];
				}
			}
		}

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
				$pathToLogEntry .= (strpos($pathToLogEntry, '?') === false ? '?' : '&').'commentId='.$this->getEntityId().'#com'.$this->getEntityId();
			}
		}
		return $pathToLogEntry;
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

	public function getSuffix()
	{
		$logEventId = $this->getLogEventId();

		if (!empty($logEventId))
		{
			$providerIntranetNewUser = new IntranetNewUser();
			if (in_array($logEventId, $providerIntranetNewUser->getEventId()))
			{
				return 'INTRANET_NEW_USER';
			}

			$providerBitrix24NewUser = new Bitrix24NewUser();
			if (in_array($logEventId, $providerBitrix24NewUser->getEventId()))
			{
				return 'BITRIX24_NEW_USER';
			}
		}
		return '';
	}

	public function add($params = array())
	{
		global $USER, $DB;

		static $parser = null;

		$authorId = (
			isset($params['AUTHOR_ID'])
			&& intval($params['AUTHOR_ID']) > 0
				? intval($params['AUTHOR_ID'])
				: $USER->getId()
		);

		$message = (
			isset($params['MESSAGE'])
			&& strlen($params['MESSAGE']) > 0
				? $params['MESSAGE']
				: ''
		);

		if (strlen($message) <= 0)
		{
			return false;
		}

		$logId = $this->getLogId();

		if (!$logId)
		{
			return false;
		}

		$this->setLogId($logId);

		if ($parser === null)
		{
			$parser = new \CTextParser();
		}

		$commentEventId = false;

		$providerIntranetNewUser = new IntranetNewUser();
		if (in_array($this->getLogEventId(), $providerIntranetNewUser->getEventId()))
		{
			$commentEventId = 'intranet_new_user_comment';
		}

		if (!$commentEventId)
		{
			$providerBitrix24NewUser = new Bitrix24NewUser();
			if (in_array($this->getLogEventId(), $providerBitrix24NewUser->getEventId()))
			{
				$commentEventId = 'bitrix24_new_user_comment';
			}
		}

		if (!$commentEventId)
		{
			$providerPhotogalleryAlbum = new PhotogalleryAlbum();
			if (in_array($this->getLogEventId(), $providerPhotogalleryAlbum->getEventId()))
			{
				$commentEventId = 'photoalbum_comment';
			}
		}

		if (!$commentEventId)
		{
			$commentEventId = 'data_comment';
		}

		$sonetCommentFields = array(
			"ENTITY_TYPE" => $this->getLogEntityType(),
			"ENTITY_ID" => $this->getLogEntityId(),
			"EVENT_ID" => $commentEventId,
			"MESSAGE" => $message,
			"TEXT_MESSAGE" => $parser->convert4mail($message),
			"MODULE_ID" => "tasks",
			"LOG_ID" => $logId,
			"RATING_TYPE_ID" => "LOG_COMMENT",
			"USER_ID" => $authorId,
			"=LOG_DATE" => $DB->currentTimeFunction(),
		);

		if (!empty($params['SHARE_DEST']))
		{
			$sonetCommentFields['SHARE_DEST'] = $params['SHARE_DEST'];
		}

		if ($sonetCommentId = \CSocNetLogComments::add($sonetCommentFields, false, false))
		{
			\CSocNetLogComments::update($sonetCommentId, array(
				"RATING_ENTITY_ID" => $sonetCommentId
			));
		}

		return $sonetCommentId;
	}

}