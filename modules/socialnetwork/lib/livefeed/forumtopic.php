<?php
namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Forum\MessageTable;
use Bitrix\Forum\TopicTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;

final class ForumTopic extends Provider
{
	const PROVIDER_ID = 'FORUM_TOPIC';
	const CONTENT_TYPE_ID = 'FORUM_TOPIC';

	public static function getId()
	{
		return static::PROVIDER_ID;
	}

	public function getEventId()
	{
		return array('forum');
	}

	public function getType()
	{
		return Provider::TYPE_POST;
	}

	final public function setEntityId($topicId) // patch TOPIC->POST
	{
		$topicId = intval($topicId);
		$messageId = 0;

		if (
			$topicId > 0
			&& Loader::includeModule('forum')
		)
		{
			$res = MessageTable::getList(array(
				'order' => array('ID' => 'ASC'),
				'filter' => array(
					'TOPIC_ID' => $topicId,
				),
				'select' => array('ID')
			));
			if ($message = $res->fetch())
			{
				$messageId = $message['ID'];
			}
		}

		$this->entityId = $messageId;
	}

	public function getCommentProvider()
	{
		$provider = new \Bitrix\Socialnetwork\Livefeed\ForumPost();
		return $provider;
	}

	public function initSourceFields()
	{
		$messageId = $this->entityId;

		if (
			$messageId > 0
			&& Loader::includeModule('forum')
		)
		{
			$res = MessageTable::getList(array(
				'filter' => array(
					'=ID' => $messageId
				),
				'select' => array('ID', 'TOPIC_ID', 'POST_MESSAGE')
			));
			if ($message = $res->fetch())
			{
				$logId = false;

				$res = LogTable::getList(array(
					'filter' => array(
						'SOURCE_ID' => $messageId,
						'@EVENT_ID' => $this->getEventId(),
					),
					'select' => array('ID')
				));
				if ($logEntryFields = $res->fetch())
				{
					$logId = intval($logEntryFields['ID']);
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
						array('ID', 'EVENT_ID', 'URL'),
						array(
							"CHECK_RIGHTS" => "Y",
							"USE_FOLLOW" => "N",
							"USE_SUBSCRIBE" => "N"
						)
					);
					if ($logFields = $res->fetch())
					{
						$this->setLogId($logFields['ID']);
						$this->setSourceFields(array_merge($message, array(
							'LOG_EVENT_ID' => $logFields['EVENT_ID'],
							'URL' => $logFields['URL']
						)));
						$this->setSourceDescription($message['POST_MESSAGE']);

						$title = '';
						$res = TopicTable::getList(array(
							'filter' => array(
								'=ID' => $message['TOPIC_ID']
							),
							'select' => array('TITLE')
						));
						if ($topic = $res->fetch())
						{
							$title = htmlspecialcharsback($topic['TITLE']);
							$title = preg_replace(
								"/\[USER\s*=\s*([^\]]*)\](.+?)\[\/USER\]/is".BX_UTF_PCRE_MODIFIER,
								"\\2",
								$title
							);
							$CBXSanitizer = new \CBXSanitizer;
							$CBXSanitizer->delAllTags();
							$title = preg_replace(array("/\n+/is".BX_UTF_PCRE_MODIFIER, "/\s+/is".BX_UTF_PCRE_MODIFIER), " ", $CBXSanitizer->sanitizeHtml($title));

						}
						$this->setSourceTitle(truncateText($title, 100));
						$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($messageId));
						$this->setSourceDiskObjects($this->getDiskObjects($messageId, $this->cloneDiskObjects));
					}
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
			$messageUF = $USER_FIELD_MANAGER->getUserFields("FORUM_MESSAGE", $messageId, LANGUAGE_ID);
			if (
				!empty($messageUF['UF_FORUM_MESSAGE_DOC'])
				&& !empty($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'])
				&& is_array($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'])
			)
			{
				if ($clone)
				{
					$this->attachedDiskObjectsCloned = self::cloneUfValues($messageUF['UF_FORUM_MESSAGE_DOC']['VALUE']);
					$result = $cache[$cacheKey] = array_values($this->attachedDiskObjectsCloned);
				}
				else
				{
					$result = $cache[$cacheKey] = $messageUF['UF_FORUM_MESSAGE_DOC']['VALUE'];
				}
			}
		}

		return $result;
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
		$pathToMessage = '';

		if (
			($message = $this->getSourceFields())
			&& !empty($message)
		)
		{
			$pathToMessage = str_replace(
				"#GROUPS_PATH#",
				Option::get('socialnetwork', 'workgroups_page', '/workgroups/', $this->getSiteId()),
				$message['URL']
			);
		}

		return $pathToMessage;
	}

	public function getAdditionalData($params = array())
	{
		$result = array();

		if (
			!$this->checkAdditionalDataParams($params)
			|| !Loader::includeModule('forum')
		)
		{
			return $result;
		}

		$res = MessageTable::getList(array(
			'filter' => array(
				'@ID' => $params['id']
			),
			'select' => array('ID', 'USE_SMILES')
		));

		while ($message = $res->fetch())
		{
			$data = $message;
			unset($data['ID']);
			$result[$message['ID']] = $data;
		}

		return $result;
	}
}