<?php

namespace Bitrix\Socialnetwork\Livefeed;

use Bitrix\Forum\MessageTable;
use Bitrix\Forum\TopicTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\LogTable;

final class ForumTopic extends Provider
{
	public const PROVIDER_ID = 'FORUM_TOPIC';
	public const CONTENT_TYPE_ID = 'FORUM_TOPIC';

	public static function getId(): string
	{
		return static::PROVIDER_ID;
	}

	public function getEventId(): array
	{
		return [ 'forum' ];
	}

	public function getType(): string
	{
		return Provider::TYPE_POST;
	}

	final public function setEntityId($topicId): void // patch TOPIC->POST
	{
		$topicId = (int)$topicId;
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

	public function getCommentProvider(): Provider
	{
		return new ForumPost();
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
					$logId = (int)$logEntryFields['ID'];
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
							$title = \Bitrix\Socialnetwork\Helper\Mention::clear($title);

							$CBXSanitizer = new \CBXSanitizer;
							$CBXSanitizer->delAllTags();
							$title = preg_replace(array("/\n+/isu", "/\s+/isu"), " ", $CBXSanitizer->sanitizeHtml($title));

						}
						$this->setSourceTitle(truncateText($title, 100));
						$this->setSourceAttachedDiskObjects($this->getAttachedDiskObjects($this->cloneDiskObjects));
						$this->setSourceDiskObjects($this->getDiskObjects($messageId, $this->cloneDiskObjects));
					}
				}
			}
		}
	}

	protected function getAttachedDiskObjects($clone = false)
	{
		return $this->getEntityAttachedDiskObjects([
			'userFieldEntity' => 'FORUM_MESSAGE',
			'userFieldCode' => 'UF_FORUM_MESSAGE_DOC',
			'clone' => $clone,
		]);
	}

	public static function canRead($params): bool
	{
		return true;
	}

	protected function getPermissions(array $post): string
	{
		return self::PERMISSION_READ;
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

	public function getAdditionalData($params = []): array
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