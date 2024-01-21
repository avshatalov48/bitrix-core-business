<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Mail;
use Bitrix\Mail\Helper\DownloadResponse;
use Bitrix\Mail\Integration\Calendar\ICal\ICalMailManager;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Main;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail\Message;
use Bitrix\Main\Engine\Response\Redirect;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main\Errorable;
use Bitrix\Mail\Helper\Cache\SanitizedBodyCache;
use Bitrix\Mail\Integration\AI;

Loc::loadMessages(__DIR__ . '/../mail.client/class.php');

Main\Loader::includeModule('mail');

class CMailClientMessageViewComponent extends CBitrixComponent implements Controllerable, Errorable
{
	/**
	 * Slow sanitizing html size
	 */
	private const SANITIZE_HTML_SIZE_THRESHOLD = 50000;

	/** @var Main\ErrorCollection */
	private $errorCollection;

	/** @var bool */
	private $isCrmEnable = false;

	/**
	 * @return array
	 */
	public function configureActions(): array
	{
		$this->errorCollection = new Main\ErrorCollection();

		return [
			'downloadHtmlBody' => [
				'+prefilters' => [
					new \Bitrix\Main\Engine\ActionFilter\CloseSession(),
				],
				'-prefilters' => [
					\Bitrix\Main\Engine\ActionFilter\Csrf::class,
				],
			],
			'getHtmlBody' => [
				'+prefilters' => [
					new \Bitrix\Main\Engine\ActionFilter\CloseSession(),
				],
				'-prefilters' => [
					\Bitrix\Main\Engine\ActionFilter\Csrf::class,
				],
			],
		];
	}

	/**
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$APPLICATION->setTitle(Loc::getMessage('MAIL_CLIENT_HOME_TITLE'));

		if (!is_object($USER) || !$USER->isAuthorized())
		{
			$APPLICATION->authForm('');
			return;
		}

		$this->setCrmEnableFields();

		$pageSize = (int) $this->arParams['PAGE_SIZE'];
		if ($pageSize < 1 || $pageSize > 100)
		{
			$this->arParams['PAGE_SIZE'] = ($pageSize = 5);
		}

		$message = $this->getPreparedMessage((int)$this->arParams['VARIABLES']['id']);

		if (empty($message))
		{
			showError($this->getFirstErrorMessage());
			return;
		}
		$this->prepareMessageHtml($message);

		$this->arResult['MESSAGE'] = $message;

		$this->arResult['LAST_RCPT'] = Mail\Helper\Recipient::loadLastRcpt();
		$this->arResult['EMAILS'] = array();//Mail\Helper\Recipient::loadMailContacts();

		$this->arResult['LOG'] = array(
			'A' => array(),
			'B' => array(),
		);

		$res = Mail\MailMessageTable::getList(array(
			'runtime' => array(
				new Main\Entity\ReferenceField(
					'CLOSURE',
					Mail\Internals\MessageClosureTable::class,
					array(
						'=this.ID' => 'ref.MESSAGE_ID',
					)
				),
			),
			'select' => $this->getLogItemSelectFields(),
			'filter' => array(
				'=MAILBOX_ID' => $message['MAILBOX_ID'],
				'=CLOSURE.PARENT_ID' => $message['ID'],
			),
			'order' => array(
				'FIELD_DATE' => 'ASC',
			),
			'offset' => 1,
			'limit' => $pageSize,
		));

		while ($item = $res->fetch())
		{
			$item = $this->prepareLog($item, $message);

			$item['__log'] = 'A';

			$this->arResult['LOG']['A'][] = $item;
		}

		$this->arResult['LOG']['A'] = array_reverse($this->arResult['LOG']['A']);

		if ($message['__access_level'] == 'full')
		{
			$res = \Bitrix\Mail\MailMessageTable::getList(array(
				'runtime' => array(
					new Main\Entity\ReferenceField(
						'CLOSURE',
						Mail\Internals\MessageClosureTable::class,
						array(
							'=this.ID' => 'ref.PARENT_ID',
						)
					),
				),
				'select' => $this->getLogItemSelectFields(),
				'filter' => array(
					'=MAILBOX_ID' => $message['MAILBOX_ID'],
					'=CLOSURE.MESSAGE_ID' => $message['ID'],
				),
				'order' => array(
					'FIELD_DATE' => 'DESC',
				),
				'offset' => 1,
				'limit' => $pageSize,
			));

			while ($item = $res->fetch())
			{
				$item = $this->prepareLog($item, $message);

				$item['__log'] = 'B';

				$this->arResult['LOG']['B'][] = $item;
			}
		}

		$this->markMessageAsSeen($message);
		$this->prepareUser();

		$this->arResult['avatarParams'] = $this->getAvatarParams(array_merge(
			$this->arResult['LOG']['B'],
			$this->arResult['LOG']['A'],
			[$this->arResult['MESSAGE']]
		));
		$APPLICATION->setTitle(htmlspecialcharsbx($message['SUBJECT']) ?: Loc::getMessage('MAIL_MESSAGE_EMPTY_SUBJECT_PLACEHOLDER'));
		$this->arResult['MESSAGE_UID_KEY'] = $message['UID'] . '-' . $message['MAILBOX_ID'];
		$this->arResult['COPILOT_PARAMS'] = $this->prepareCopilotParams($USER->getId());

		$this->includeComponentTemplate();
	}

	/**
	 * @param $id
	 * @param $log
	 * @param $size
	 *
	 * @return array|void
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function logAction($id, $log, $size)
	{
		if (!$id)
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'));
			return;
		}

		if (!empty($log) && preg_match('/([ab])(\d+)/i', $log, $matches))
		{
			$type = mb_strtoupper($matches[1]);
			$offset = (int) $matches[2];
		}
		else
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'));
			return;
		}

		$message = Mail\MailMessageTable::getList(array(
			'select' => array(
				'ID', 'MAILBOX_ID',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
			),
			'filter' => array(
				'=ID' => $id,
			),
		))->fetch();

		if (empty($message))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'));
			return;
		}

		if (!Mail\Helper\Message::hasAccess($message))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
			return;
		}

		if ('A' == $type)
		{
			$res = Mail\MailMessageTable::getList(array(
				'runtime' => array(
					new Main\Entity\ReferenceField(
						'CLOSURE',
						Mail\Internals\MessageClosureTable::class,
						array(
							'=this.ID' => 'ref.MESSAGE_ID',
						)
					),
				),
				'select' => $this->getLogItemSelectFields(),
				'filter' => array(
					'=MAILBOX_ID' => $message['MAILBOX_ID'],
					'=CLOSURE.PARENT_ID' => $message['ID'],
				),
				'order' => array(
					'FIELD_DATE' => 'ASC',
				),
				'offset' => $offset + 1,
				'limit' => $size > 0 ? $size : 5,
			));
		}
		else
		{
			if ($message['__access_level'] != 'full')
			{
				$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
				return;
			}

			$res = \Bitrix\Mail\MailMessageTable::getList(array(
				'runtime' => array(
					new Main\Entity\ReferenceField(
						'CLOSURE',
						Mail\Internals\MessageClosureTable::class,
						array(
							'=this.ID' => 'ref.PARENT_ID',
						)
					),
				),
				'select' => $this->getLogItemSelectFields(),
				'filter' => array(
					'=MAILBOX_ID' => $message['MAILBOX_ID'],
					'=CLOSURE.MESSAGE_ID' => $message['ID'],
				),
				'order' => array(
					'FIELD_DATE' => 'DESC',
				),
				'offset' => $offset + 1,
				'limit' => $size > 0 ? $size : 5,
			));
		}

		$log = array();
		while ($item = $res->fetch())
		{
			$item = $this->prepareLog($item, $message);
			$item['__log'] = $type;

			$log[] = $item;
		}

		if (!empty($log))
		{
			if ('A' == $type)
			{
				$log = array_reverse($log);
			}

			$this->arResult['LOG'] = $log;
			$this->arResult['avatarParams'] = $this->getAvatarParams($log);
			ob_start();

			$this->includeComponentTemplate('log');

			return array(
				'html' => ob_get_clean(),
				'count' => count($log),
			);
		}

		return array(
			'html' => '',
			'count' => 0,
		);
	}

	/**
	 * @param $messages
	 *
	 * @return array
	 */
	private function getAvatarParams($messages)
	{
		$params = (new Mail\MessageView\AvatarManager(Main\Engine\CurrentUser::get()->getId()))
			->getAvatarParamsFromMessagesHeaders($messages);
		foreach ($params as $email => $data)
		{
			$params[$email]['avatarSize'] = 23;
		}
		return $params;
	}

	/**
	 * Return html for message in chain by ajax
	 *
	 * @param $id
	 *
	 * @return string|void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function logitemAction($id)
	{
		$this->setCrmEnableFields();

		$message = $this->getPreparedMessage((int)$id);

		if (empty($message))
		{
			// Errors added to collection in getMessage method
			return;
		}
		$this->prepareMessageHtml($message);

		$this->arResult['MESSAGE'] = $message;
		$this->prepareUser();

		$this->arResult['LAST_RCPT'] = Mail\Helper\Recipient::loadLastRcpt();
		$this->arResult['EMAILS'] = array();//Mail\Helper\Recipient::loadMailContacts();

		$this->arParams['LOADED_FROM_LOG'] = true;
		$this->arResult['avatarParams'] = $this->getAvatarParams([$this->arResult['MESSAGE']]);
		$this->markMessageAsSeen($this->arResult['MESSAGE']);
		ob_start();

		$this->arResult['COPILOT_PARAMS'] = $this->prepareCopilotParams(Main\Engine\CurrentUser::get()->getId());
		$this->includeComponentTemplate('logitem');

		return ob_get_clean();
	}

	/**
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function prepareUser()
	{
		global $USER, $APPLICATION;

		$userFields = \Bitrix\Main\UserTable::getList(array(
			'select' => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'PERSONAL_PHOTO'),
			'filter' => array('=ID' => $USER->getId()),
		))->fetch();

		$userImage = \CFile::resizeImageGet(
			$userFields['PERSONAL_PHOTO'], array('width' => 38, 'height' => 38),
			BX_RESIZE_IMAGE_EXACT, false
		);

		$this->arResult['USER_IMAGE'] = !empty($userImage['src']) ? $userImage['src'] : '';
	}

	protected function prepareICal($message)
	{
		if (empty($message['OPTIONS']['iCal']))
		{
			return;
		}

		$icalComponent = ICalMailManager::parseRequest($message['OPTIONS']['iCal']);

		if (
			$icalComponent instanceof \Bitrix\Calendar\ICal\Parser\Calendar
			&& $icalComponent->getMethod() === \Bitrix\Calendar\ICal\Parser\Dictionary::METHOD['request']
			&& $icalComponent->hasOneEvent()
		)
		{
			$this->arResult['iCalEvent'] = $icalComponent->getEvent();
		}
	}

	/**
	 * @param $message
	 *
	 * @return mixed
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function prepareMessage(&$message)
	{
		$dirsHelper = new Mail\Helper\MailboxDirectoryHelper($message['MAILBOX_ID']);
		$dir = $dirsHelper->getDirByHash($message['DIR_MD5'] ?: '');

		$message['isSpam'] = $dir ? $dir->isSpam() : false;
		$message['isTrash'] = $dir ? $dir->isTrash() : false;

		if ($message['OPTIONS']['trackable'] && !$message['READ_CONFIRMED'])
		{
			if (\Bitrix\Main\Config\Option::get('main', 'track_outgoing_emails_read', 'Y') == 'Y')
			{
				if (Main\Loader::includeModule('pull'))
				{
					\CPullWatch::add(
						Main\Engine\CurrentUser::get()->getId(),
						Mail\Helper\MessageEventManager::getPullTagName($message['ID']),
						true
					);
				}
			}
			else
			{
				$message['OPTIONS']['trackable'] = false;
			}
		}

		if (
			($message['OPTIONS']['attachments'] <= 0)
			&& empty($message['OPTIONS']['isOriginalEmptyBody'])
			&& ((isset($message['OPTIONS']['isEmptyBody']) && $message['OPTIONS']['isEmptyBody'] === 'Y')
				|| empty($message['BODY_HTML'])
			)
		)
		{
			$message['isSyncError'] = true;
			$mailBoxQuery = Mail\MailboxTable::getById($message['MAILBOX_ID']);
			if ($mailBox = $mailBoxQuery->fetchObject())
			{
				if ($link = $mailBox->getLink())
				{
					$message['hideFastReplyPanel'] = true;
					$message['hideMailControlPanel'] = true;
					$uri = new Main\Web\Uri($link);
					$message['MAILBOX']['HOST'] = $uri->getHost();
					$message['MAILBOX']['URI'] = $uri->getUri();
				}
			}
		}

		$binds = array();
		foreach ((array) $message['BIND'] as $item)
		{
			if (preg_match('/^(\w+)-(\d+)$/', $item, $matches))
			{
				$binds[$matches[1]][] = $matches[2];
			}
		}

		$message['BIND_LINKS'] = array();

		if (!empty($binds[MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT]) && Main\Loader::includeModule('calendar'))
		{
			$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CALENDAR_TITLE')] = array();

			foreach ($binds[MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT] as $eventId)
			{
				$eventData = CCalendarEvent::getEventForViewInterface($eventId);
				$defaultTitle = sprintf('%s #%u', Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CALENDAR_EMPTY_TITLE'), $eventId);
				$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CALENDAR_TITLE')][] = array(
					'title' => $eventData['NAME'] ?? $defaultTitle,
					'href' => \CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_CALENDAR_EVENT'],
						[
							'event_id' => $eventData['ID'],
						]
					),
				);
			}
		}

		if (!empty($binds[MessageAccessTable::ENTITY_TYPE_IM_CHAT]) && Main\Loader::includeModule('im'))
		{
			$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CHAT_TITLE')] = array();

			foreach ($binds[MessageAccessTable::ENTITY_TYPE_IM_CHAT] as $chatId)
			{
				$chatData = CIMChat::GetChatData(['ID' => $chatId]);
				// $defaultTitle = sprintf('%s #%u', Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CHAT_EMPTY_TITLE'), $chatId);
				$defaultTitle = Loc::getMessage('MAIL_MESSAGE_CREATE_IM_BTN');
				$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CHAT_TITLE')][] = array(
					'title' => isset($chatData['chat'][$chatId]['name']) ? htmlspecialcharsback($chatData['chat'][$chatId]['name']) : $defaultTitle,
					'href' => \CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_IM_CHAT'],
						[
							'chat_id' => $chatId,
						]
					),
					'onclick' => 'BX.Mail.Secretary.getInstance('.(int)$message['ID'].').openChat();',
				);
			}
		}

		if (!empty($binds[MessageAccessTable::ENTITY_TYPE_TASKS_TASK]) && Main\Loader::includeModule('tasks'))
		{
			$res = Bitrix\Tasks\Internals\TaskTable::getList(array(
				'select' => array('ID', 'TITLE'),
				'filter' => array(
					'@ID' => (array) $binds[MessageAccessTable::ENTITY_TYPE_TASKS_TASK],
				),
			));

			$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_TASKS_TITLE')] = array();
			while ($item = $res->fetch())
			{
				$defaultTitle = sprintf('%s #%u', Loc::getMessage('MAIL_MESSAGE_EXT_BIND_TASKS_EMPTY_TITLE'), $item['ID']);
				$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_TASKS_TITLE')][] = array(
					'title' => $item['TITLE'] ?: $defaultTitle,
					'href' => \CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_TASKS_TASK'],
						[
							'action' => 'view',
							'task_id' => $item['ID'],
						]
					),
				);
			}
		}

		if (!empty($binds[MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY]) && $this->isCrmEnable)
		{
			$res = \Bitrix\Crm\ActivityTable::getList(array(
				'select' => array('OWNER_TYPE_ID', 'OWNER_ID'),
				'filter' => array(
					'@ID' => (array) $binds[MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY],
				),
			));

			$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CRM_TITLE')] = array();
			while ($item = $res->fetch())
			{
				$defaultTitle = sprintf('%s #%u', Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CRM_EMPTY_TITLE'), $item['OWNER_ID']);

				// @TODO: group queries
				$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_CRM_TITLE')][] = array(
					'title' => \CCrmOwnerType::getCaption($item['OWNER_TYPE_ID'], $item['OWNER_ID'], false) ?: $defaultTitle,
					'href' => \CCrmOwnerType::getEntityShowPath($item['OWNER_TYPE_ID'], $item['OWNER_ID'], false),
				);
			}
		}

		if (!empty($binds[MessageAccessTable::ENTITY_TYPE_BLOG_POST]) && Main\Loader::includeModule('blog'))
		{
			$res = Bitrix\Blog\PostTable::getList(array(
				'select' => array('ID', 'TITLE'),
				'filter' => array(
					'@ID' => (array) $binds[MessageAccessTable::ENTITY_TYPE_BLOG_POST],
				),
			));

			$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_POSTS_TITLE')] = array();
			while ($item = $res->fetch())
			{
				$defaultTitle = sprintf('%s #%u', Loc::getMessage('MAIL_MESSAGE_EXT_BIND_POSTS_EMPTY_TITLE'), $item['ID']);
				$message['BIND_LINKS'][Loc::getMessage('MAIL_MESSAGE_EXT_BIND_POSTS_TITLE')][] = array(
					'title' => $item['TITLE'] ?: $defaultTitle,
					'href' => \CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_BLOG_POST'],
						[
							'post_id' => $item['ID'],
						]
					),
					'onclick' => 'top.BX.SidePanel.Instance.open(this.href, {loader: \'socialnetwork:userblogpost\'}); return false; ',
				);
			}
		}

		$message = \Bitrix\Mail\Helper\Message::prepare($message);

		$message['__diskFiles'] = array_filter(
			$message['__files'] ?? [],
			function ($item)
			{
				return isset($item['objectId']) && $item['objectId'] > 0;
			}
		);

		return $message;
	}

	/**
	 * @param $messageField
	 *
	 * @return string
	 */
	private function getEmailFromFieldFrom($messageField)
	{
		$address = new Main\Mail\Address($messageField);
		return trim($address->getEmail());
	}


	/**
	 * Getting array of errors.
	 * @return Main\Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Main\Error|null
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	private function markMessageAsSeen($message)
	{
		if (!(array_key_exists('MSG_UID', $message)
			&& array_key_exists('IS_SEEN', $message)
			&& array_key_exists('MAILBOX_ID', $message)
			&& array_key_exists('UID', $message)))
		{
			return;
		}
		if ($message['MSG_UID'] && !in_array($message['IS_SEEN'], ['Y', 'S']))
		{
			\Bitrix\Main\Application::getInstance()->addBackgroundJob(function () use ($message)
			{
				$mailMarkerManager = new \Bitrix\Mail\ImapCommands\MailsFlagsManager($message['MAILBOX_ID'], $message['UID']);
				$mailMarkerManager->setMessages([$message]);
				$mailMarkerManager->markMailsSeen();
			});
		}
	}

	/**
	 * Get message attachments by ajax
	 *
	 * @param int $id Message ID
	 * @return array
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getAttachmentsAction(int $id): array
	{
		$this->setCrmEnableFields();

		$message = $this->getPreparedMessage($id, true);


		if (empty($message))
		{
			// Errors added to collection in getMessage method
			return [];
		}

		$this->arResult['MESSAGE'] = $message;
		ob_start();

		$this->includeComponentTemplate('files');

		return [
			'attachmentsHtml' => ob_get_clean(),
		];
	}

	/**
	 * Set properties is CRM enabled
	 */
	private function setCrmEnableFields(): void
	{
		$this->isCrmEnable = Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();
		$this->arResult['CRM_ENABLE'] = ($this->isCrmEnable ? 'Y' : 'N');
	}

	/**
	 * Get first error message from collection
	 *
	 * @return string
	 */
	private function getFirstErrorMessage(): string
	{
		/** @var Main\Error $item */
		foreach ($this->errorCollection as $item)
		{
			return $item->getMessage();
		}
		return '';
	}

	/**
	 * Get message with prepeared fields
	 *
	 * @param int $id Message ID
	 * @param bool $forceLoadLazyAttachments Force load lazy attachments
	 *
	 * @return array|null
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getPreparedMessage(int $id, bool $forceLoadLazyAttachments = false): ?array
	{
		$message = $this->getMessage($id);

		if (empty($message))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return null;
		}

		if (!Mail\Helper\Message::hasAccess($message))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
			return null;
		}

		if ($forceLoadLazyAttachments
			|| Mail\Helper\Message::isBodyNeedUpdateAfterLoadAttachments((string)$message['BODY_HTML']))
		{
			Mail\Helper\Message::ensureAttachments($message);
		}

		$message = $this->appendAttachments($message);
		$this->prepareMessage($message);

		$message['SENDER_EMAIL'] = $this->getEmailFromFieldFrom($message['FIELD_FROM']);

		return $message;
	}

	/**
	 * Get message from database
	 *
	 * @param int $id Message ID
	 *
	 * @return array|null
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getMessage(int $id): ?array
	{
		$message = Mail\MailMessageTable::getList([
			'runtime' => [
				new Main\Entity\ReferenceField(
					'MESSAGE_UID',
					'Bitrix\Mail\MailMessageUidTable',
					[
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			],
			'select' => [
				'*',
				'UID' => 'MESSAGE_UID.ID',
				'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
				'MSG_UID' => 'MESSAGE_UID.MSG_UID',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_OPTIONS' => 'MAILBOX.OPTIONS',
				'HEADER_MD5' => 'MESSAGE_UID.HEADER_MD5',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
				'IS_SEEN' => 'MESSAGE_UID.IS_SEEN',
			],
			'filter' => [
				'=ID' => $id,
			],
		])->fetch();

		if ($message)
		{
			$message['BIND'] = MessageAccessTable::getBinds($message['MAILBOX_ID'], $id);

			return $message;
		}

		return null;
	}

	/**
	 * Append attachments to message field if need
	 *
	 * @param array $message Message field
	 *
	 * @return array Array with appended __files field
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function appendAttachments(array $message): array
	{
		$message['__files'] = [];
		if ($message['ATTACHMENTS'] > 0)
		{
			$message['__files'] = Mail\Internals\MailMessageAttachmentTable::getList([
				'select' => ['ID', 'FILE_ID', 'FILE_NAME', 'FILE_SIZE', 'CONTENT_TYPE'],
				'filter' => ['=MESSAGE_ID' => $message['ID']],
			])->fetchAll();
		}
		return $message;
	}

	/**
	 * Prepare log item fields
	 *
	 * @param array $item Log message
	 * @param array $message Main message
	 *
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function prepareLog(array $item, array $message): array
	{
		$item['MAILBOX_EMAIL'] = $message['MAILBOX_EMAIL'];
		$item['MAILBOX_NAME'] = $message['MAILBOX_NAME'];
		$item['MAILBOX_LOGIN'] = $message['MAILBOX_LOGIN'];

		$item = $this->prepareMessage($item);
		$item['SENDER_EMAIL'] = $this->getEmailFromFieldFrom($item['FIELD_FROM']);
		return $item;
	}

	/**
	 * Get fields list to select for log items
	 *
	 * @return array|string[]
	 */
	private function getLogItemSelectFields(): array
	{
		return [
			'ID',
			'MAILBOX_ID',
			'FIELD_DATE',
			'SUBJECT',
			'FIELD_FROM',
			'FIELD_REPLY_TO',
			'FIELD_TO',
			'FIELD_CC',
			'FIELD_BCC',
			'ATTACHMENTS',
			'OPTIONS',
			'READ_CONFIRMED',
		];
	}

	/**
	 * Sanitize html in message
	 *
	 * @param array $message Message fields
	 *
	 * @return void
	 */
	private function prepareMessageHtml(array &$message): void
	{
		if (!trim($message['BODY_HTML']))
		{
			$message['MESSAGE_HTML'] = $this->getHtmlFromTextBody((string)$message['BODY']);
			return;
		}

		if (!$message[MailMessageTable::FIELD_SANITIZE_ON_VIEW])
		{
			$message['MESSAGE_HTML'] = $message['BODY_HTML'];
			return;
		}
		if (!$this->isSanitizeHtmlCanBeLong($message['BODY_HTML']))
		{
			$message['MESSAGE_HTML'] = \Bitrix\Mail\Helper\Message::sanitizeHtml($message['BODY_HTML'], true);
			return;
		}

		$cachedBody = (new SanitizedBodyCache())->get($message['ID']);
		if ($cachedBody)
		{
			$message['MESSAGE_HTML'] = $cachedBody;
		}
		else
		{
			$message['MESSAGE_HTML'] = $this->getHtmlFromTextBody((string)$message['BODY']);
			$message['IS_AJAX_BODY_SANITIZE'] = true;
		}
	}

	/**
	 * Get html body of message
	 *
	 * @param int $id Message ID
	 *
	 * @return array
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getHtmlBodyAction(int $id): array
	{
		$this->setCrmEnableFields();

		$message = $this->getPreparedMessage($id);
		if (empty($message))
		{
			// Errors added to collection in getMessage method
			return [];
		}

		$messageHtml = \Bitrix\Mail\Helper\Message::sanitizeHtml($message['BODY_HTML'], true);
		(new SanitizedBodyCache())->set($id, $messageHtml);

		$quote = Message::wrapTheMessageWithAQuote(
			$messageHtml,
			$message['SUBJECT'],
			$message['FIELD_DATE'],
			$message['__from'],
			$message['__to'],
			$message['__cc'],
			true,
		);

		return [
			"messageHtml" => $messageHtml,
			"quote" => $quote,
		];
	}

	/**
	 * Download html body
	 *
	 * @param int $id Message ID
	 *
	 * @return \Bitrix\Main\HttpResponse
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function downloadHtmlBodyAction(int $id): \Bitrix\Main\HttpResponse
	{
		$this->setCrmEnableFields();

		$message = $this->getPreparedMessage($id);

		if (empty($message))
		{
			return new Redirect('/404.php');
		}

		$content = $message['BODY_HTML'] ?? '';
		$name = "email_$id.html";
		$contentType = 'text/html';

		return new DownloadResponse($content, $name, $contentType);
	}

	/**
	 * Is html big enough to cause slow sanitization
	 *
	 * @param string $bodyHtml Email html body, that we sanitize
	 *
	 * @return bool
	 */
	private function isSanitizeHtmlCanBeLong(string $bodyHtml): bool
	{
		return mb_strlen(trim($bodyHtml)) > self::SANITIZE_HTML_SIZE_THRESHOLD;
	}

	/**
	 * Get HTML from text body of email
	 *
	 * @param string $textBody Text body
	 *
	 * @return string
	 */
	private function getHtmlFromTextBody(string $textBody): string
	{
		return preg_replace('/(\s*(\r\n|\n|\r))+/', '<br>', htmlspecialcharsbx($textBody));
	}

	private function prepareCopilotParams(): array
	{
		$messageIds = [];
		if ($this->arResult['LOG']['A'])
		{
			foreach ($this->arResult['LOG']['A'] as $message)
			{
				array_unshift($messageIds, (int)$message['ID']);
			}
		}
		array_unshift($messageIds, (int)$this->arResult['MESSAGE']['ID']);
		if ($this->arResult['LOG']['B'])
		{
			foreach ($this->arResult['LOG']['B'] as $message)
			{
				array_unshift($messageIds, (int)$message['ID']);
			}
		}

		return AI\Settings::instance()->getMailCopilotParams(
			AI\Settings::MAIL_REPLY_MESSAGE_CONTEXT_ID,
			['messageIds' => $messageIds,],
		);
	}

}
