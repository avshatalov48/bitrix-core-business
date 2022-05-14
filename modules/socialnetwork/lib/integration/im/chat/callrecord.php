<?php

namespace Bitrix\Socialnetwork\Integration\Im\Chat;

use Bitrix\Disk\AttachedObject;
use Bitrix\Disk\File;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Forum\MessageTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Event;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\Livefeed;
use Bitrix\Forum\Comments;

Loc::loadMessages(__FILE__);

/**
 * Class for call record event handlers
 *
 * Class CallRecord
 * @package Bitrix\Socialnetwork\Integration\Im\Chat
 */
final class CallRecord
{
	private const ENTITY_TYPE_TASK = 'TASKS';
	private const ENTITY_TYPE_CALENDAR_EVENT = 'CALENDAR';

	public static function getEntityTypeList(): array
	{
		return [
			self::ENTITY_TYPE_TASK,
			self::ENTITY_TYPE_CALENDAR_EVENT,
		];
	}

	/**
	 * Handles file sharing in IM task or calendar event chat
	 *
	 * @param Event $event Event.
	 * @return bool
	 */
	public static function onDiskRecordShare(Event $event): bool
	{
		global $USER;

		$diskObjectId = (int)$event->getParameter('DISK_ID');
		$chat = $event->getParameter('CHAT');
		$userId = $event->getParameter('USER_ID');

		$userId = ($userId === null ? $USER->getId() : (int)$userId);

		if (
			$userId <= 0
			|| $diskObjectId <= 0
			|| !is_array($chat)
		)
		{
			return false;
		}

		$entityType = (string)$chat['ENTITY_TYPE'];
		$entityId = (int)$chat['ENTITY_ID'];

		if (
			$entityId <= 0
			|| !in_array($entityType, self::getEntityTypeList())
		)
		{
			return false;
		}

		$normalizedEntityType = self::getNormalizedEntityType($entityType);
		if ($normalizedEntityType === '')
		{
			return false;
		}

		$postProvider = Livefeed\Provider::init([
			'ENTITY_TYPE' => $normalizedEntityType,
			'ENTITY_ID' => $entityId,
		]);
		if (!$postProvider)
		{
			return false;
		}

		if (!Loader::includeModule('disk'))
		{
			return false;
		}

		$postProvider->initSourceFields();
		$logId = $postProvider->getLogId();

		$commentProvider = $postProvider->getCommentProvider();
		$commentProvider->setParentProvider($postProvider);
		$commentProvider->setLogId($logId);

		$fileName = Loc::getMessage('SOCIALNETWORK_CHAT_CALLRECORD_FILE_NOT_FOUND');
		if ($diskObject = File::loadById($diskObjectId))
		{
			$fileName = $diskObject->getName();
		}

		$sonetCommentData = $commentProvider->add([
			'AUTHOR_ID' => $userId,
			'MESSAGE' => Loc::getMessage('SOCIALNETWORK_CHAT_CALLRECORD_TEXT', [
				'#FILE_NAME#' => $fileName,
				'#DISK_FILE_ID#' => $diskObjectId,
			]),
			'MODULE' => false,
		]);

		$sonetCommentId = (int)($sonetCommentData['sonetCommentId'] ?? 0);
		$sourceCommentId = (int)($sonetCommentData['sourceCommentId'] ?? 0);

		if (
			$sourceCommentId > 0
			&& $commentProvider->getId() === Livefeed\ForumPost::PROVIDER_ID
			&& Loader::includeModule('forum')
		)
		{
			$updateFields = [
				'UF_FORUM_MESSAGE_DOC' => [ FileUserType::NEW_FILE_PREFIX . $diskObjectId ],
			];
			if (\Bitrix\Forum\Message::update($sourceCommentId, $updateFields)->isSuccess())
			{
				$res = MessageTable::getList([
					'filter' => [
						'=ID' => $sourceCommentId,
					],
					'select' => [ 'UF_FORUM_MESSAGE_DOC' ],
				]);
				if (
					($messageFields = $res->fetch())
					&& !empty($messageFields['UF_FORUM_MESSAGE_DOC'])
				)
				{
					$attachedDiskId = (int)$messageFields['UF_FORUM_MESSAGE_DOC'][0];
					$renderedMessage = '';

					if (
						Loader::includeModule('disk')
						&& ($attachedObject = AttachedObject::loadById($attachedDiskId, [ 'OBJECT' ]))
					)
					{
						$url = \Bitrix\Disk\Driver::getInstance()->getUrlManager()->getUrlUfController('download', [ 'attachedId' => $attachedDiskId ]);
						$renderedMessage = Loc::getMessage('SOCIALNETWORK_CHAT_CALLRECORD_TEXT', [
							'#FILE_NAME#' => (!empty($url) ? '[URL=' . $url . ']' . $fileName . '[/URL]' : $fileName),
							'#DISK_FILE_ID#' => $diskObjectId,
						]);

						$updateFields = [
							'POST_MESSAGE' => $renderedMessage,
						];
						MessageTable::update($sourceCommentId, $updateFields);
					}

					if ($sonetCommentId > 0)
					{
						$updateFields = [
							'UF_SONET_COM_DOC' => $messageFields['UF_FORUM_MESSAGE_DOC']
						];
						if (!empty($renderedMessage))
						{
							$updateFields['MESSAGE'] = $renderedMessage;
						}
						LogCommentTable::update($sonetCommentId, $updateFields);
					}

					$forumId = self::getForumId($entityType);
					if ($forumId > 0)
					{
						$feed = new Comments\Feed(
							$forumId,
							[
								'type' => self::getForumFeedType($entityType),
								'id' => $entityId,
								'xml_id' => self::getForumFeedXmlIdPrefix($entityType) . $entityId,
							],
							$userId
						);

						$feed->send(
							$sourceCommentId,
							[
								'URL_TEMPLATES_PROFILE_VIEW' => Option::get('socialnetwork', 'user_page', '/company/personal/') . 'user/#user_id#/',
								'SKIP_USER_READ' => 'N',
							]
						);
					}
				}
			}
		}

		return true;
	}

	private static function getNormalizedEntityType(string $entityType): string
	{
		switch ($entityType)
		{
			case self::ENTITY_TYPE_TASK:
				$result = Livefeed\Provider::DATA_ENTITY_TYPE_TASKS_TASK;
				break;
			case self::ENTITY_TYPE_CALENDAR_EVENT:
				$result = Livefeed\Provider::DATA_ENTITY_TYPE_CALENDAR_EVENT;
				break;
			default:
				$result = '';
		}

		return $result;
	}

	private static function getForumFeedType(string $entityType): string
	{
		if (!Loader::includeModule('forum'))
		{
			return '';
		}

		switch ($entityType)
		{
			case self::ENTITY_TYPE_TASK:
				$result = mb_strtoupper(Comments\TaskEntity::ENTITY_TYPE);
				break;
			case self::ENTITY_TYPE_CALENDAR_EVENT:
				$result = mb_strtoupper(Comments\CalendarEntity::ENTITY_TYPE);
				break;
			default:
				$result = '';
		}

		return $result;
	}

	private static function getForumFeedXmlIdPrefix(string $entityType): string
	{
		if (!Loader::includeModule('forum'))
		{
			return '';
		}

		switch ($entityType)
		{
			case self::ENTITY_TYPE_TASK:
				$result = mb_strtoupper(Comments\TaskEntity::XML_ID_PREFIX);
				break;
			case self::ENTITY_TYPE_CALENDAR_EVENT:
				$result = mb_strtoupper(Comments\CalendarEntity::XML_ID_PREFIX);
				break;
			default:
				$result = '';
		}

		return $result;
	}

	public static function getForumId($entityType): int
	{
		switch ($entityType)
		{
			case self::ENTITY_TYPE_TASK:
				$result = (Loader::includeModule('tasks') ? \CTasksTools::getForumIdForIntranet() : 0);
				break;
			case self::ENTITY_TYPE_CALENDAR_EVENT:
				$settings = \CCalendar::getSettings();
				$result = (int)$settings['forum_id'];
				break;
			default:
				$result = 0;
		}

		return $result;
	}
}
