<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Main\Config\Option;

final class LogComment extends Provider
{
	public const PROVIDER_ID = 'SONET_COMMENT';
	public const CONTENT_TYPE_ID = 'LOG_COMMENT';

	protected $logEventId = null;
	protected $logEntityType = null;
	protected $logEntityId = null;

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'data_comment', 'photoalbum_comment', 'intranet_new_user_comment', 'bitrix24_new_user_comment' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_COMMENT;
	}

	public function initSourceFields()
	{
		$commentId = $this->entityId;

		if ($commentId > 0)
		{
			$logId = false;

			$res = LogCommentTable::getList([
				'filter' => [
					'=ID' => $commentId,
					'@EVENT_ID' => $this->getEventId(),
				],
				'select' => [ 'ID', 'LOG_ID', 'MESSAGE', 'SHARE_DEST', 'EVENT_ID' ]
			]);
			if ($logComentFields = $res->fetch())
			{
				$logId = (int)$logComentFields['LOG_ID'];
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
					$title = \Bitrix\Socialnetwork\Helper\Mention::clear($title);

					$CBXSanitizer = new \CBXSanitizer;
					$CBXSanitizer->delAllTags();
					$title = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", $CBXSanitizer->sanitizeHtml($title));
					$this->setSourceTitle(truncateText($title, 100));
					$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($this->cloneDiskObjects));
					$this->setSourceDiskObjects($this->getDiskObjects($commentId, $this->cloneDiskObjects));
					$this->setSourceOriginalText($logComentFields['MESSAGE']);
					$this->setSourceAuxData($logComentFields);
				}
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return $this->getEntityAttachedDiskObjects([
			'userFieldEntity' => 'SONET_COMMENT',
			'userFieldCode' => 'UF_SONET_COM_DOC',
			'clone' => $clone,
		]);
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
				$pathToLogEntry .= (mb_strpos($pathToLogEntry, '?') === false ? '?' : '&').'commentId='.$this->getEntityId().'#com'.$this->getEntityId();
			}
		}
		return $pathToLogEntry;
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
	}

	public function getSuffix(): string
	{
		$logEventId = $this->getLogEventId();

		if (!empty($logEventId))
		{
			$providerIntranetNewUser = new IntranetNewUser();
			if (in_array($logEventId, $providerIntranetNewUser->getEventId(), true))
			{
				return 'INTRANET_NEW_USER';
			}

			$providerBitrix24NewUser = new Bitrix24NewUser();
			if (in_array($logEventId, $providerBitrix24NewUser->getEventId(), true))
			{
				return 'BITRIX24_NEW_USER';
			}
		}

		return '2';
	}

	public function add($params = array())
	{
		global $USER;

		static $parser = null;

		$authorId = (
			isset($params['AUTHOR_ID'])
			&& (int)$params['AUTHOR_ID'] > 0
				? (int)$params['AUTHOR_ID']
				: $USER->getId()
		);

		$message = (string)(
			isset($params['MESSAGE'])
			&& (string)$params['MESSAGE'] !== ''
				? $params['MESSAGE']
				: ''
		);

		if ($message === '')
		{
			return false;
		}

		$module = ($params['MODULE'] ?? 'tasks');
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
		if (in_array($this->getLogEventId(), $providerIntranetNewUser->getEventId(), true))
		{
			$commentEventId = 'intranet_new_user_comment';
		}

		if (!$commentEventId)
		{
			$providerBitrix24NewUser = new Bitrix24NewUser();
			if (in_array($this->getLogEventId(), $providerBitrix24NewUser->getEventId(), true))
			{
				$commentEventId = 'bitrix24_new_user_comment';
			}
		}

		if (!$commentEventId)
		{
			$providerPhotogalleryAlbum = new PhotogalleryAlbum();
			if (in_array($this->getLogEventId(), $providerPhotogalleryAlbum->getEventId(), true))
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
			"MODULE_ID" => $module,
			"LOG_ID" => $logId,
			"RATING_TYPE_ID" => "LOG_COMMENT",
			"USER_ID" => $authorId,
			"=LOG_DATE" => \CDatabase::currentTimeFunction(),
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

		return [
			'sonetCommentId' => $sonetCommentId,
			'sourceCommentId' => $sonetCommentId
		];
	}

}