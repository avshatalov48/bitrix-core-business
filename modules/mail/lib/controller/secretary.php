<?php

namespace Bitrix\Mail\Controller;

use Bitrix\Forum\ForumTable;
use Bitrix\Mail\Helper\Message;
use Bitrix\Mail\Integration\Calendar\ICal\ICalMailManager;
use Bitrix\Mail\Internals\MailMessageAttachmentTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Secretary extends Controller
{
	protected function processBeforeAction(Action $action)
	{
		if (! Loader::includeModule('intranet'))
		{
			return false;
		}
		return parent::processBeforeAction($action);
	}

	/**
	 * Configure actions.
	 *
	 * @return array
	 */
	public function configureActions()
	{
		$config = [
			'createChatFromMessage' => [
				'+prefilters' => [
					new \Bitrix\Main\Engine\ActionFilter\HttpMethod(['POST']),
					new \Bitrix\Main\Engine\ActionFilter\Csrf(),
				]
			],
			'onCalendarSave' => [
				'+prefilters' => [
					new \Bitrix\Main\Engine\ActionFilter\HttpMethod(['POST']),
					new \Bitrix\Main\Engine\ActionFilter\Csrf(),
				]
			],
			'getCalendarEventDataFromMessage' => [
				'+prefilters' => [
					new \Bitrix\Main\Engine\ActionFilter\HttpMethod(['POST']),
					new \Bitrix\Main\Engine\ActionFilter\Csrf(),
				]
			],
		];

		if (Loader::includeModule('intranet'))
		{
			$config['createChatFromMessage']['+prefilters'][] = new \Bitrix\Intranet\ActionFilter\IntranetUser();
			$config['onCalendarSave']['+prefilters'][] = new \Bitrix\Intranet\ActionFilter\IntranetUser();
			$config['getCalendarEventDataFromMessage']['+prefilters'][] = new \Bitrix\Intranet\ActionFilter\IntranetUser();
		}

		return $config;
	}

	/**
	 * Create chat for mail message or go back to existing chat.
	 *
	 * @param int $messageId  mail message id
	 * @return int|null  chat id
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function createChatFromMessageAction(int $messageId): ?int
	{
		if (!Loader::includeModule('im'))
		{
			return null;
		}

		global $USER;
		$userId = $USER->GetID();

		if (!$this->canBindEntities($messageId, (int)$userId))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_SECRETARY_ACCESS_DENIED')));
			return null;
		}

		$message = \Bitrix\Mail\Integration\Intranet\Secretary::getMessage($messageId);
		$messageData = $message->toArray();
		$messageData['USER_IDS'] = [$USER->GetID()];

		if ($chatId = \Bitrix\Intranet\Secretary::getChatIdIfExists($messageId, 'MAIL'))
		{
			// get back to chat if user left in past
			if (! \Bitrix\Intranet\Secretary::isUserInChat($chatId, $userId))
			{
				\Bitrix\Intranet\Secretary::addUserToChat($chatId, $userId, false);
				// // post welcome message again because it was hidden
				// \Bitrix\Intranet\Secretary::postMailChatWelcomeMessage($message, $chatId, $userId);
			}
		}
		else
		{
			$lockName = "chat_create_mail_{$messageId}";
			if (!Application::getConnection()->lock($lockName))
			{
				$this->addError(new Error(
						Loc::getMessage('MAIL_SECRETARY_CREATE_CHAT_LOCK_ERROR'), 'lock_error')
				);
				return null;
			}

			$chatId = \Bitrix\Intranet\Secretary::createMailChat($messageData, $userId);

			Application::getConnection()->unlock($lockName);
		}

		if (Loader::includeModule('pull'))
		{
			$mailboxId = \Bitrix\Mail\Integration\Intranet\Secretary::getMailboxIdForMessage($messageId);

			if($mailboxId)
			{
				\CPullWatch::addToStack(
					'mail_mailbox_' . $mailboxId,
					[
						'module_id' => 'mail',
						'command' => 'messageBindingCreated',
						'params' => [
							'messageId' => $messageId,
							'mailboxId' => $mailboxId,
							'entityType' => Message::ENTITY_TYPE_IM_CHAT,
							'entityId' => $chatId,
							'bindingEntityLink' =>
							\CComponentEngine::makePathFromTemplate(
								'/online/?IM_DIALOG=chat#chat_id#',
								[
									'chat_id' => $chatId,
								]
							),
						],
					]
				);
			}
		}

		return $chatId;
	}

	public function onCalendarSaveAction(int $messageId, int $calendarEventId)
	{
		if ($this->provideAccessToMessage($messageId, $calendarEventId))
		{
			if ($this->postCalendarBackLinkComment($messageId, $calendarEventId))
			{
				$this->assignCreatedCalendarLabelToMessage($messageId, $calendarEventId);
			}
			else
			{
				$this->addError(new Error('secretary: comment post error'));
			}
		}
		else
		{
			$this->addError(new Error('secretary: grant access to message failed'));
		}
	}

	/**
	 * Assign event label to mail messages list.
	 *
	 * @param int $messageId
	 * @param int $calendarEventId
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function assignCreatedCalendarLabelToMessage(int $messageId, int $calendarEventId): bool
	{
		if (Loader::includeModule('pull'))
		{
			$mailboxId = \Bitrix\Mail\Integration\Intranet\Secretary::getMailboxIdForMessage($messageId);

			if($mailboxId)
			{
				global $USER;

				$userPage = \Bitrix\Main\Config\Option::get('socialnetwork', 'user_page', '/company/personal/', SITE_ID);

				\CPullWatch::addToStack(
					'mail_mailbox_' . $mailboxId,
					[
						'module_id' => 'mail',
						'command' => 'messageBindingCreated',
						'params' => [
							'messageId' => $messageId,
							'mailboxId' => $mailboxId,
							'entityType' => Message::ENTITY_TYPE_CALENDAR_EVENT,
							'entityId' => $calendarEventId,
							'bindingEntityLink' =>
								\CComponentEngine::makePathFromTemplate(
									$userPage . 'user/#user_id#/calendar/?EVENT_ID=#event_id#',
									[
										'user_id' => $USER->getId(),
										'event_id' => $calendarEventId,
									]
								),
						],
					]
				);
			}

			return true;
		}

		return false;
	}

	/**
	 * Grant access to message for calendar event attendees.
	 *
	 * @param int $messageId
	 * @param int $calendarEventId
	 * @return bool
	 */
	private function provideAccessToMessage(int $messageId, int $calendarEventId): bool
	{
		global $USER;
		$userId = $USER->GetID();

		return \Bitrix\Mail\Integration\Intranet\Secretary::provideAccessToMessage(
			$messageId,
			Message::ENTITY_TYPE_CALENDAR_EVENT,
			$calendarEventId,
			$userId
		);
	}

	/**
	 * Post comment to calendar event with tokenized backlink.
	 *
	 * @param int $messageId
	 * @param int $calendarEventId
	 * @return bool
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function postCalendarBackLinkComment(int $messageId, int $calendarEventId): bool
	{
		if (! Loader::includeModule('calendar'))
		{
			$this->addError(new Error('module calendar unloaded'));
			return false;
		}

		if (! Loader::includeModule('forum'))
		{
			$this->addError(new Error('module forum unloaded'));
			return false;
		}

		global $USER;
		$userId = (int)$USER->GetID();

		$xmlId = 'EVENT_' . $calendarEventId;

		$calendarEntry = \CCalendarEvent::getEventForViewInterface($calendarEventId, [
			'eventDate' => '',
			'userId' => $userId,
		]);

		if (!$this->canBindEntities($messageId, $userId))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_SECRETARY_ACCESS_DENIED')));
			return false;
		}

		if (! isset($calendarEntry['CREATED_BY']) || (int)$calendarEntry['CREATED_BY'] !== $userId)
		{
			$this->addError(new Error(Loc::getMessage('MAIL_SECRETARY_ACCESS_DENIED_CALENDAR')));
			return false;
		}

		if ($calendarEntry)
		{
			$xmlId = \CCalendarEvent::getEventCommentXmlId($calendarEntry);
		}

		$feedParams = [
			'type' => 'EV', // \Bitrix\Socialnetwork\Livefeed\ForumPost::getForumTypeMap()
			'id' => $calendarEventId,
			'xml_id' => $xmlId,
		];

		$forumId = self::getForumId(array_merge($feedParams, [
			'SITE_ID' => SITE_ID,
		]));

		if (!$forumId)
		{
			$this->addError(new Error('forum id error'));
			return false;
		}

		$feed = new \Bitrix\Forum\Comments\Feed(
			$forumId,
			$feedParams,
			$userId
		);

		$link = \Bitrix\Mail\Integration\Intranet\Secretary::getMessageUrlForCalendarEvent($messageId, $calendarEventId);
		$commentMessage = Loc::getMessage('MAIL_SECRETARY_POST_MESSAGE_CALENDAR_EVENT', [
			'#LINK#' => $link,
		]);

		$forumMessageFields = [
			'POST_MESSAGE' => $commentMessage,
		];
		$forumComment = $feed->add($forumMessageFields);

		if (!$forumComment)
		{
			$this->addError(new Error('forum comment error'));
			return false;
		}

		// TODO post to social network feed
		// if (! Loader::includeModule('socialnetwork'))
		// {
		// 	$this->addError(new Error('module socialnetwork unloaded'));
		// 	return null;
		// }

		return true;
	}

	/**
	 * Prepare name and description for new calendar event,
	 * created from mail message.
	 *
	 * @param int $messageId
	 * @return array|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getCalendarEventDataFromMessageAction(int $messageId)
	{
		if (! Loader::includeModule('intranet'))
		{
			$this->addError(new Error('module intranet unloaded')); // FIXME translate
			return null;
		}

		global $USER;
		if (!$this->canBindEntities($messageId, (int)$USER->getId()))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_SECRETARY_ACCESS_DENIED')));
			return null;
		}

		$message = \Bitrix\Mail\Integration\Intranet\Secretary::getMessage($messageId);
		$address = new \Bitrix\Main\Mail\Address($message->getFrom());
		$desc = Loc::getMessage('MAIL_SECRETARY_CALENDAR_EVENT_DESC', [
			'#SUBJECT#' => htmlspecialcharsbx($message->getSubject()),
			'#FROM#' => htmlspecialcharsbx($message->getFrom()),
			'#DATE#' => $message->getDate()->toString(),
			'#LINK_FROM#' => 'mailto:' . htmlspecialcharsbx($address->getEmail()),
			'#LINK#' => \Bitrix\Mail\Integration\Intranet\Secretary::getDirectMessageUrl($message->getId()),
		]);

		$isIcal = Message::isIcalMessage($message);

		return [
			'name' => htmlspecialcharsbx($message->getSubject()),
			'desc' => $desc,
			'isIcal' => $isIcal,
			'isNewEvent' => !$isIcal,
			// 'userIds' => $data['USER_IDS'],
		];
	}

	// FIXME copypaste from forum module, must be simplified
	private static function getForumId($params = [])
	{
		$result = 0;

		$siteId = (
		isset($params['SITE_ID'])
		&& $params['SITE_ID'] <> ''
			? $params['SITE_ID']
			: SITE_ID
		);

		if (isset($params['type']))
		{
			if ($params['type'] === 'TK')
			{
				$result = Option::get('tasks', 'task_forum_id', 0, $siteId);

				if (
					(int)$result <= 0
					&& Loader::includeModule('forum')
				)
				{
					$res = ForumTable::getList([
						'filter' => [
							'=XML_ID' => 'intranet_tasks',
						],
						'select' => [ 'ID' ],
					]);
					if ($forumFields = $res->fetch())
					{
						$result = (int)$forumFields['ID'];
					}
				}
			}
			elseif ($params['type'] === 'WF')
			{
				$result = Option::get('bizproc', 'forum_id', 0, $siteId);

				if ((int)$result <= 0)
				{
					$res = ForumTable::getList([
						'filter' => [
							'=XML_ID' => 'bizproc_workflow',
						],
						'select' => [ 'ID' ],
					]);
					if ($forumFields = $res->fetch())
					{
						$result = (int)$forumFields['ID'];
					}
				}
			}
			elseif (in_array($params['type'], [ 'TM', 'TR' ]))
			{
				$result = Option::get('timeman', 'report_forum_id', 0, $siteId);
			}
			elseif (
				$params['type'] === 'EV'
				&& Loader::includeModule('calendar')
			)
			{
				$calendarSettings = \CCalendar::getSettings();
				$result = $calendarSettings["forum_id"];
			}
			elseif (
				$params['type'] === 'PH'
				&& Loader::includeModule('forum')
			)
			{
				$res = ForumTable::getList(array(
					'filter' => array(
						'=XML_ID' => 'PHOTOGALLERY_COMMENTS'
					),
					'select' => array('ID')
				));
				if ($forumFields = $res->fetch())
				{
					$result = (int)$forumFields['ID'];
				}
			}
			elseif ($params['type'] === 'IBLOCK')
			{
				$result = Option::get('wiki', 'socnet_forum_id', 0, $siteId);
			}
			else
			{
				$res = ForumTable::getList(array(
					'filter' => array(
						'=XML_ID' => 'USERS_AND_GROUPS'
					),
					'select' => array('ID')
				));
				if ($forumFields = $res->fetch())
				{
					$result = (int)$forumFields['ID'];
				}
			}
		}

		return $result;
	}

	/**
	 * Check user can create linked entities
	 *
	 * @param int $messageId
	 * @param int $userId
	 * @return bool
	 */
	private function canBindEntities(int $messageId, int $userId): bool
	{
		return  \Bitrix\Mail\MessageAccess::createByMessageId($messageId, $userId)->canModifyMessage();
	}
}
