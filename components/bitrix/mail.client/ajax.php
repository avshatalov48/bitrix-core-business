<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Calendar\ICal\Parser\Calendar as CalendarIcalComponent;
use Bitrix\Mail;
use Bitrix\Mail\ImapCommands\MailsFlagsManager;
use Bitrix\Mail\ImapCommands\MailsFoldersManager;
use Bitrix\Mail\Integration\Calendar\ICal\ICalMailManager;
use Bitrix\Mail\MailMessageTable;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('mail');
Loc::loadLanguageFile(__FILE__);
Loc::loadMessages(__DIR__ . '/../mail.client/class.php');

class CMailClientAjaxController extends \Bitrix\Main\Engine\Controller
{
	/** @var bool */
	private $isCrmEnable = false;

	/**
	 * Initializes controller.
	 * @return void
	 */
	protected function init()
	{
		parent::init();

		$this->isCrmEnable = Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();
	}


	/**
	 * Common operations before process action.
	 *
	 * @param \Bitrix\Main\Engine\Action $action Action.
	 *
	 * @return bool If method will return false, then action will not execute.
	 * @throws Main\LoaderException
	 */
	protected function processBeforeAction(\Bitrix\Main\Engine\Action $action)
	{
		if (parent::processBeforeAction($action))
		{
			if ($action->getName() === 'sendMessage')
			{
				$data = $this->request->getPost('data');
				if (empty($data))
				{
					$this->addError(new Error('Source data are not found'));
				}
			}
		}

		return (count($this->getErrors()) === 0);
	}


	/**
	 * Move messages to folder.
	 * @param string[] $ids
	 * @param string $folder
	 */
	public function moveToFolderAction($ids, $folder)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFoldersManager($data['mailboxId'], $data['messagesIds'], $this->getCurrentUser()->getId());
			$result = $mailMarkerManager->moveMails($folder);
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	protected function markMessages($ids, $seen = true)
	{
		$method = ($seen ? 'markMailsSeen' : 'markMailsUnseen');

		if (!empty($ids['for_all']))
		{
			list($mailboxId, $dir) = explode('-', $ids['for_all']);

			$ids = array();

			$res = Mail\MailMessageUidTable::getList(array(
				'select' => array('ID'),
				'filter' => array(
					'=MAILBOX_ID' => $mailboxId,
					'=DIR_MD5' => md5($dir),
					'>MESSAGE_ID' => 0,
					'@IS_SEEN' => $seen ? array('N', 'U') : array('Y', 'S'),
					'=DELETE_TIME' => 'IS NULL',
				),
			));
			while ($item = $res->fetch())
			{
				$ids[] = "{$item['ID']}-{$mailboxId}";
			}
		}

		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFlagsManager($data['mailboxId'], $data['messagesIds']);
			$result = $mailMarkerManager->$method();
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	/**
	 * Mark messages as unseen.
	 * @param string[] $ids
	 */
	public function markAsUnseenAction($ids)
	{
		$this->markMessages($ids, false);
	}

	/**
	 * Mark messages as seen.
	 * @param string[] $ids
	 */
	public function markAsSeenAction($ids)
	{
		$this->markMessages($ids, true);
	}

	/**
	 * Restore messages from spam.
	 * @param $ids
	 */
	public function restoreFromSpamAction($ids)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFoldersManager($data['mailboxId'], $data['messagesIds'], $this->getCurrentUser()->getId());
			$result = $mailMarkerManager->restoreMailsFromSpam();
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	/**
	 * Marks messages as spam.
	 * @param string[] $ids
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function markAsSpamAction($ids)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFoldersManager($data['mailboxId'], $data['messagesIds'], $this->getCurrentUser()->getId());
			$result = $mailMarkerManager->sendMailsToSpam();
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	/**
	 * Deletes messages.
	 * @param string[] $ids
	 * @param boolean $deleteImmediately
	 */
	public function deleteAction($ids, $deleteImmediately = false)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFoldersManager($data['mailboxId'], $data['messagesIds']);
			$result = $mailMarkerManager->deleteMails($deleteImmediately);
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	/**
	 * @param $ids
	 *
	 * @return \Bitrix\Main\Result
	 */
	private function getIds($ids)
	{
		$result = new \Bitrix\Main\Result();
		if (empty($ids))
		{
			return $result->addError(new \Bitrix\Main\Error('validation'));
		}
		$mailboxIds = $messIds = [];
		foreach ($ids as $id)
		{
			list($messId, $mailboxId) = explode('-', $id, 2);

			$mailboxIds[$mailboxId] = $mailboxId;
			$messIds[$messId] = $messId;
		}
		if (count($mailboxIds) > 1)
		{
			return $result->addError(new \Bitrix\Main\Error('validation'));
		}
		if (!count($mailboxIds))
		{
			return $result->addError(new \Bitrix\Main\Error('validation'));
		}
		if (!count($messIds))
		{
			return $result->addError(new \Bitrix\Main\Error('validation'));
		}
		$result->setData([
			'mailboxId' => array_pop($mailboxIds),
			'messagesIds' => array_keys($messIds),
		]);

		return $result;
	}

	/**
	 * Generates message Id.
	 * @param string $hostname
	 *
	 * @return string
	 */
	private function generateMessageId($hostname)
	{
		// @TODO: more entropy
		return sprintf(
			'<bx.mail.%x.%x@%s>',
			time(),
			rand(0, 0xffffff),
			$hostname
		);
	}

	/**
	 * Generates message Id for CRM email.
	 * @param string $hostname
	 * @param string $urn
	 *
	 * @return string
	 */
	private function generateCrmMessageId($hostname, $urn)
	{
		return sprintf('<crm.activity.%s@%s>', $urn, $hostname);
	}

	/**
	 * Gets host name.
	 *
	 * @return string
	 */
	private function getHostname()
	{
		static $hostname;
		if (empty($hostname))
		{
			$hostname = \COption::getOptionString('main', 'server_name', '') ?: 'localhost';
			if (defined('BX24_HOST_NAME') && BX24_HOST_NAME != '')
			{
				$hostname = BX24_HOST_NAME;
			}
			elseif (defined('SITE_SERVER_NAME') && SITE_SERVER_NAME != '')
			{
				$hostname = SITE_SERVER_NAME;
			}
		}

		return $hostname;
	}

	/**
	 * @param $id
	 * @param $dir
	 * @param $onlySyncCurrent
	 *
	 * @return array
	 * @throws Exception
	 */
	public function syncMailboxAction($id, $dir, $onlySyncCurrent = false)
	{
		$sessionId = md5(uniqid(''));

		$response = array(
			'complete' => -1,
			'status' => 0,
			'sessid' => $sessionId,
			'timestamp' => microtime(true),
			'final' => true,
		);

		if ($mailbox = \Bitrix\Mail\MailboxTable::getUserMailbox($id))
		{
			session_write_close();

			$mailboxHelper = \Bitrix\Mail\Helper\Mailbox::createInstance($id);
			$mailboxHelper->setSyncParams(array(
				'full' => true,
				'currentDir' => $dir,
				'sessid' => $sessionId,
			));

			$mailboxSyncManager = new Mail\Helper\Mailbox\MailboxSyncManager($mailbox['USER_ID']);
			$mailboxSyncManager->setSyncStartedData($id);

			$result = $mailboxHelper->syncDir($dir);

			$response['timestamp'] = microtime(true);

			if ($result === false)
			{
				$mailboxSyncManager->setSyncStatus($id, false, time());
				$this->errorCollection->add($mailboxHelper->getWarnings()->toArray());
			}
			else
			{
				if (null !== $result)
				{
					$response['new'] = $result;

					$lastSyncResult = $mailboxHelper->getLastSyncResult();

					$response['updated'] = -$lastSyncResult['updatedMessages'];
					$response['deleted'] = -$lastSyncResult['deletedMessages'];

					$mailboxHelper->resyncDir($dir);

					$lastSyncResult = $mailboxHelper->getLastSyncResult();

					$response['updated'] += $lastSyncResult['updatedMessages'];
					$response['deleted'] += $lastSyncResult['deletedMessages'];

					$response['timestamp'] = microtime(true);
				}

				$response['complete'] = true;

				$onlySyncCurrent = filter_var($onlySyncCurrent, FILTER_VALIDATE_BOOLEAN);
				if (!$onlySyncCurrent && count($mailboxHelper->getDirsHelper()->getSyncDirs()) > 1)
				{
					$mailboxHelper->sync();
				}
				else
				{
					$mailboxSyncManager->setSyncStatus($id, true, time());
					$mailboxHelper->notifyNewMessages();
				}
			}
		}
		else
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR'));
		}

		return $response;
	}

	/**
	 * Sends email.
	 *
	 * @param array $data
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public function sendMessageAction($data)
	{
		$rawData = (array) \Bitrix\Main\Application::getInstance()->getContext()->getRequest()->getPostList()->getRaw('data');

		$decodedData = $rawData;
		\CUtil::decodeUriComponent($decodedData);

		$hostname = $this->getHostname();

		$fromEmail = $decodedData['from'];
		$fromAddress = new \Bitrix\Main\Mail\Address($fromEmail);

		if ($fromAddress->validate())
		{
			$fromEmail = $fromAddress->getEmail();

			\CBitrixComponent::includeComponentClass('bitrix:main.mail.confirm');
			if (!in_array($fromEmail, array_column(\MainMailConfirmComponent::prepareMailboxes(), 'email')))
			{
				$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_MESSAGE_BAD_SENDER'));

				return;
			}

			if ($fromAddress->getName())
			{
				$fromEncoded = sprintf(
					'%s <%s>',
					sprintf('=?%s?B?%s?=', SITE_CHARSET, base64_encode($fromAddress->getName())),
					$fromEmail
				);
			}
		}
		else
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage(
				empty($fromEmail) ? 'MAIL_MESSAGE_EMPTY_SENDER' : 'MAIL_MESSAGE_BAD_SENDER'
			));

			return;
		}

		$to  = array();
		$cc  = array();
		$bcc = array();
		$toEncoded = array();
		$ccEncoded = array();
		$bccEncoded = array();

		if ($this->isCrmEnable)
		{
			$crmCommunication = array();
		}

		foreach (array('to', 'cc', 'bcc') as $field)
		{
			if (!empty($rawData[$field]) && is_array($rawData[$field]))
			{
				$addressList = array();
				foreach ($rawData[$field] as $item)
				{
					try
					{
						$item = \Bitrix\Main\Web\Json::decode($item);

						$address = new Bitrix\Main\Mail\Address();
						$address->setEmail($item['email']);
						$address->setName($item['name']);

						if ($address->validate())
						{
							$fieldEncoded = $field.'Encoded';

							if ($address->getName())
							{
								${$field}[] = $address->get();
								${$fieldEncoded}[] = $address->getEncoded();
							}
							else
							{
								${$field}[] = $address->getEmail();
								${$fieldEncoded}[] = $address->getEmail();
							}

							$addressList[] = $address;

							if ($this->isCrmEnable)
							{
								// crm only
								if (mb_strpos($item['id'], 'CRM') === 0)
								{
									$crmCommunication[] = $item;
								}
							}
						}
					}
					catch (\Exception $e)
					{
					}
				}

				if (count($addressList) > 0)
				{
					$this->appendMailContacts($addressList, $field);
				}
			}
		}

		$to  = array_unique($to);
		$cc  = array_unique($cc);
		$bcc = array_unique($bcc);
		$toEncoded = array_unique($toEncoded);
		$ccEncoded = array_unique($ccEncoded);
		$bccEncoded = array_unique($bccEncoded);

		if (empty($to))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_MESSAGE_EMPTY_RCPT'));
			return;
		}

		$messageBody = (string) $decodedData['message'];
		$messageBodyHtml = '';
		if (!empty($messageBody))
		{
			$messageBody = preg_replace('/<!--.*?-->/is', '', $messageBody);
			$messageBody = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $messageBody);
			$messageBody = preg_replace('/<title[^>]*>.*?<\/title>/is', '', $messageBody);

			$sanitizer = new \CBXSanitizer();
			$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
			$sanitizer->applyDoubleEncode(false);
			$sanitizer->addTags(array('style' => array()));

			$messageBody = $sanitizer->sanitizeHtml($messageBody);
			$messageBodyHtml = $messageBody;
			$messageBody = preg_replace('/https?:\/\/bxacid:(n?\d+)/i', 'bxacid:\1', $messageBody);
		}

		$outgoingBody = $messageBody;

		$totalSize = 0;
		$attachments = array();
		$attachmentIds = array();
		if (!empty($data['__diskfiles']) && is_array($data['__diskfiles']) && Loader::includeModule('disk'))
		{
			foreach ($data['__diskfiles'] as $item)
			{
				if (!preg_match('/n\d+/i', $item))
				{
					continue;
				}

				$id = ltrim($item, 'n');

				if (!($diskFile = \Bitrix\Disk\File::loadById($id)))
				{
					continue;
				}

				if (!($file = \CFile::makeFileArray($diskFile->getFileId())))
				{
					continue;
				}

				$totalSize += $diskFile->getSize();

				$attachmentIds[] = $id;

				$contentId = sprintf(
					'bxacid.%s@%s.mail',
					hash('crc32b', $file['external_id'].$file['size'].$file['name']),
					hash('crc32b', $hostname)
				);

				$attachments[] = array(
					'ID'           => $contentId,
					'NAME'         => $diskFile->getName(),
					'PATH'         => $file['tmp_name'],
					'CONTENT_TYPE' => $file['type'],
				);

				$outgoingBody = preg_replace(
					sprintf('/(https?:\/\/)?bxacid:n?%u/i', $id),
					sprintf('cid:%s', $contentId),
					$outgoingBody
				);
			}
		}

		$maxSize = (int) Main\Config\Option::get('main', 'max_file_size', 0);
		$maxSizeAfterEncoding = floor($maxSize/4)*3;
		if ($maxSize > 0 && $maxSize <= ceil($totalSize / 3) * 4) // base64 coef.
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage(
				'MAIL_MESSAGE_MAX_SIZE_EXCEED',
				['#SIZE#' => \CFile::formatSize($maxSizeAfterEncoding,1)]
			));
			return;
		}

		// @TODO: improve mailbox detection

		if ($data['MAILBOX_ID'] > 0)
		{
			if ($mailbox = Mail\MailboxTable::getUserMailbox($data['MAILBOX_ID']))
			{
				$mailboxHelper = Mail\Helper\Mailbox::createInstance($mailbox['ID'], false);
			}
		}

		if (empty($mailboxHelper))
		{
			foreach (Mail\MailboxTable::getUserMailboxes() as $mailbox)
			{
				if ($fromEmail == $mailbox['EMAIL'])
				{
					$mailboxHelper = Mail\Helper\Mailbox::createInstance($mailbox['ID'], false);
					break;
				}
			}
		}

		$outgoingParams = array(
			'CHARSET'      => SITE_CHARSET,
			'CONTENT_TYPE' => 'html',
			'ATTACHMENT'   => $attachments,
			'TO'           => implode(', ', $toEncoded),
			'SUBJECT'      => $data['subject'],
			'BODY'         => $outgoingBody,
			'HEADER'       => array(
				'From'       => $fromEncoded ?: $fromEmail,
				'Reply-To'   => $fromEncoded ?: $fromEmail,
				//'To'         => join(', ', $to),
				'Cc'         => implode(', ', $ccEncoded),
				'Bcc'        => implode(', ', $bccEncoded),
				//'Subject'    => $data['subject'],
				//'Message-Id' => $messageId,
				'In-Reply-To' => sprintf('<%s>', $data['IN_REPLY_TO']),
			),
		);

		$messageBindings = array();

		// crm activity
		if ($this->isCrmEnable && count($crmCommunication) > 0)
		{
			$messageFields = array_merge(
				$outgoingParams,
				array(
					'BODY' => $messageBodyHtml,
					'FROM' => $fromEmail,
					'TO' => $to,
					'CC' => $cc,
					'BCC' => $bcc,
					'IMPORTANT' => !empty($data['important']),
					'STORAGE_TYPE_ID' => \Bitrix\Crm\Integration\StorageType::Disk,
					'STORAGE_ELEMENT_IDS' => $attachmentIds,
				)
			);
			$activityFields = array(
				'COMMUNICATIONS' => $crmCommunication,
			);

			if (\CCrmEMail::createOutgoingMessageActivity($messageFields, $activityFields) !== true)
			{
				if (!empty($activityFields['ERROR_TEXT']))
				{
					$this->errorCollection[] = new \Bitrix\Main\Error($activityFields['ERROR_TEXT']);
				}
				elseif (!empty($activityFields['ERROR_CODE']))
				{
					$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_' . $activityFields['ERROR_CODE']));
				}
				else
				{
					$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_ACTIVITY_CREATE_ERROR'));
				}

				return;
			}

			$messageBindings[] = Mail\Internals\MessageAccessTable::ENTITY_TYPE_CRM_ACTIVITY;

			//$activityId = $activityFields['ID'];
			//$urn = $messageFields['URN'];
			$messageId = $messageFields['MSG_ID'];
		}
		else
		{
			$messageId = $this->generateMessageId($hostname);
		}

		$outgoingParams['HEADER']['Message-Id'] = $messageId;

		if (empty($mailboxHelper))
		{
			$context = new Main\Mail\Context();
			$context->setCategory(Main\Mail\Context::CAT_EXTERNAL);
			$context->setPriority(Main\Mail\Context::PRIORITY_NORMAL);

			$result = Main\Mail\Mail::send(array_merge(
				$outgoingParams,
				array(
					'CONTEXT' => $context,
				)
			));
		}
		else
		{
			$eventKey = Main\EventManager::getInstance()->addEventHandler(
				'mail',
				'onBeforeUserFieldSave',
				function (\Bitrix\Main\Event $event) use (&$messageBindings)
				{
					$params = $event->getParameters();
					$messageBindings[] = $params['entity_type'];
				}
			);

			$result = $mailboxHelper->mail(array_merge(
				$outgoingParams,
				array(
					'HEADER' => array_merge(
						$outgoingParams['HEADER'],
						array(
							'To' => $outgoingParams['TO'],
							'Subject' => $outgoingParams['SUBJECT'],
						)
					),
				)
			));

			Main\EventManager::getInstance()->removeEventHandler('mail', 'onBeforeUserFieldSave', $eventKey);
		}

		addEventToStatFile(
			'mail',
			(empty($data['IN_REPLY_TO']) ? 'send_message' : 'send_reply'),
			join(',', array_unique(array_filter($messageBindings))),
			trim(trim($messageId), '<>')
		);

		return;
	}

	/**
	 * Creates crm activity.
	 *
	 * @param string $messageId
	 *
	 * @return array|void
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function createCrmActivityAction($messageId, $level = 1)
	{
		if (!Loader::includeModule('crm'))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'));
			return;
		}

		$message = Mail\MailMessageTable::getList(array(
			'runtime' => array(
				new Main\Entity\ReferenceField(
					'MESSAGE_UID',
					'Bitrix\Mail\MailMessageUidTable',
					array(
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					),
					array(
						'join_type' => 'INNER',
					)
				),
			),
			'select' => array(
				'*',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
				'IS_SEEN' => 'MESSAGE_UID.IS_SEEN',
				'MSG_HASH' => 'MESSAGE_UID.HEADER_MD5',
				'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
				'MSG_UID' => 'MESSAGE_UID.MSG_UID',
			),
			'filter' => array(
				'=ID' => $messageId,
			),
			'order' => array(
				'FIELD_DATE' => 'DESC',
				'MESSAGE_UID.ID' => 'DESC',
				'MESSAGE_UID.MSG_UID' => 'ASC',
			),
			'limit' => 1,
		))->fetch();

		if (empty($message))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return;
		}

		if (!Mail\Helper\Message::hasAccess($message))
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
			return;
		}

		if ($level <= 1 && Mail\Helper\Message::ensureAttachments($message) > 0)
		{
			return $this->createCrmActivityAction($messageId, $level + 1);
		}

		Mail\Helper\Message::prepare($message);

		$message['IS_OUTCOME'] = $message['__is_outcome'];
		//$message['IS_TRASH'] = !empty($params['trash']);
		//$message['IS_SPAM'] = !empty($params['spam']);
		$message['IS_SEEN'] = in_array($message['IS_SEEN'], array('Y', 'S'));

		$message['__forced'] = true;
		if (!\CCrmEMail::imapEmailMessageAdd($message, null, $error))
		{
			$this->addError(new Error(Loc::getMessage('MAIL_MESSAGE_LIST_NOTIFY_ADD_TO_CRM_ERROR')));
			if ($error)
			{
				$this->addError($error instanceof Error ? $error : new Error($error));
			}
		}
	}

	/**
	 * Removes crm activity.
	 * @param string $messageId
	 *
	 * @return array|void
	 * @throws Main\ArgumentException
	 * @throws Main\DB\SqlQueryException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function removeCrmActivityAction($messageId)
	{
		global $USER;

		if (!Loader::includeModule('crm'))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_AJAX_ERROR'));
			return;
		}

		$message = Mail\MailMessageTable::getList(array(
			'runtime' => array(
				new Main\Entity\ReferenceField(
					'MESSAGE_ACCESS',
					Mail\Internals\MessageAccessTable::class,
					array(
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					)
				),
			),
			'select' => array(
				'*',
				'MAILBOX_EMAIL' => 'MAILBOX.EMAIL',
				'MAILBOX_NAME' => 'MAILBOX.NAME',
				'MAILBOX_LOGIN' => 'MAILBOX.LOGIN',
				new Main\Entity\ExpressionField(
					'BIND',
					'GROUP_CONCAT(%s)',
					'MESSAGE_ACCESS.ENTITY_ID'
				),
			),
			'filter' => array(
				'=ID' => $messageId,
				'=MESSAGE_ACCESS.ENTITY_TYPE' => 'CRM_ACTIVITY',
			),
		))->fetch();

		if (empty($message))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_NOT_FOUND'));
			return;
		}

		$mailbox = Mail\MailboxTable::getUserMailbox($message['MAILBOX_ID']);

		if (empty($mailbox))
		{
			$this->errorCollection[] = new Main\Error(Loc::getMessage('MAIL_CLIENT_ELEMENT_DENIED'));
			return;
		}

		$result = array();

		Mail\Helper\Message::prepare($message);

		if (empty($message['__is_outcome']))
		{
			$exclusionAccess = new \Bitrix\Crm\Exclusion\Access($USER->getId());
			if ($exclusionAccess->canWrite())
			{
				foreach (array_merge($message['__from'], $message['__reply_to']) as $item)
				{
					if (!empty($item['email']))
					{
						\Bitrix\Crm\Exclusion\Store::add(\Bitrix\Crm\Communication\Type::EMAIL, $item['email']);
					}
				}
			}
		}

		foreach (explode(',', $message['BIND']) as $item)
		{
			\CCrmActivity::delete($item);
		}

		return $result;
	}

	/**
	 * Append contact reference.
	 *
	 * @param \Bitrix\Main\Mail\Address[] $addressList Email address list.
	 * @param string $fromField Email field TO|CC|BCC.
	 *
	 * @return void
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function appendMailContacts($addressList, $fromField = '')
	{
		$fromField = mb_strtoupper($fromField);
		if (
			!in_array(
				$fromField,
				array(
					\Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_TO,
					\Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_CC,
					\Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_BCC,
				)
			)
		)
		{
			$fromField = \Bitrix\Mail\Internals\MailContactTable::ADDED_TYPE_TO;
		}

		$allEmails = array();
		$contactsData = array();

		/**
		 * @var \Bitrix\Main\Mail\Address $address
		 */
		foreach ($addressList as $address)
		{
			$allEmails[] = mb_strtolower($address->getEmail());
			$contactsData[] = array(
				'USER_ID' => $this->getCurrentUser()->getId(),
				'NAME' => $address->getName(),
				'ICON' => \Bitrix\Mail\Helper\MailContact::getIconData($address->getEmail(), $address->getName()),
				'EMAIL' => $address->getEmail(),
				'ADDED_FROM' => $fromField,
			);
		}

		\Bitrix\Mail\Internals\MailContactTable::addContactsBatch($contactsData);

		$mailContacts = \Bitrix\Mail\Internals\MailContactTable::query()
			->addSelect('ID')
			->where('USER_ID', $this->getCurrentUser()->getId())
			->whereIn('EMAIL', $allEmails)
			->exec();

		$lastRcpt = array();
		while ($contact = $mailContacts->fetch())
		{
			$lastRcpt[] = 'MC'. $contact['ID'];
		}

		if (count($lastRcpt) > 0)
		{
			\Bitrix\Main\FinderDestTable::merge(array(
				'USER_ID' => $this->getCurrentUser()->getId(),
				'CONTEXT' => 'MAIL_LAST_RCPT',
				'CODE' => $lastRcpt,
			));
		}
	}

	public function icalAction()
	{
//		return false;
		$request = Context::getCurrent()->getRequest();

		$messageId = (int)$request->getPost("messageId");
		$action = (string)$request->getPost("action");

		if (!$messageId || !$action)
		{
			$this->addError(new Error(Loc::getMessage('MAIL_CLIENT_FORM_ERROR')));

			return false;
		}

		$message = MailMessageTable::getList([
			'runtime' => [
				new Main\Entity\ReferenceField(
					'MAILBOX',
					'Bitrix\Mail\MailboxTable',
					[
						'=this.MAILBOX_ID' => 'ref.ID',
					],
					[
						'join_type' => 'INNER',
					]
				),
			],
			'select'  => [
				'ID',
				'FIELD_FROM',
				'FIELD_TO',
				'OPTIONS',
				'USER_ID' => 'MAILBOX.USER_ID',
			],
			'filter'  => [
				'=ID' => $messageId,
			],
		])->fetch();

		if (empty($message['OPTIONS']['iCal']))
		{
			return false;
		}

		$icalComponent = ICalMailManager::parseRequest($message['OPTIONS']['iCal']);

		if ($icalComponent instanceof \Bitrix\Calendar\ICal\Parser\Calendar
			&& $icalComponent->getMethod() === \Bitrix\Calendar\ICal\Parser\Dictionary::METHOD['request']
			&& $icalComponent->hasOneEvent()
		)
		{
			\Bitrix\Calendar\ICal\MailInvitation\IncomingInvitationRequestHandler::createInstance()
				->setDecision($action)
				->setIcalComponent($icalComponent)
				->setUserId((int)$message['USER_ID'])
				->handle();
//			ICalMailManager::manageRequest([
//				'event'  => $icalComponent->getEvent(),
//				'userId' => $message['USER_ID'],
//				'emailFrom'  => $message['FIELD_FROM'],
//				'emailTo'  => $message['FIELD_TO'],
//				'answer' => $action
//			]);

			return true;
		}

		return false;
	}
}
