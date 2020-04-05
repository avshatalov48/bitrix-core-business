<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Mail\ImapCommands\MailsFlagsManager;
use Bitrix\Mail\ImapCommands\MailsFoldersManager;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Mail;
use Bitrix\Main\Error;

Main\Loader::includeModule('mail');
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

		$this->isCrmEnable = Main\Loader::includeModule('crm') && \CCrmPerms::isAccessEnabled();
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

	/**
	 * Mark messages as unseen.
	 * @param string[] $ids
	 */
	public function markAsUnseenAction($ids)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFlagsManager($data['mailboxId'], $data['messagesIds']);
			$result = $mailMarkerManager->markMailsUnseen();
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
	}

	/**
	 * Mark messages as seen.
	 * @param string[] $ids
	 */
	public function markAsSeenAction($ids)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFlagsManager($data['mailboxId'], $data['messagesIds']);
			$result = $mailMarkerManager->markMailsSeen();
			if (!$result->isSuccess())
			{
				$errors = $result->getErrors();
				$this->addError($errors[0]);
			}
		}
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
	 */
	public function deleteAction($ids)
	{
		$result = $this->getIds($ids);
		if ($result->isSuccess())
		{
			$data = $result->getData();
			$mailMarkerManager = new MailsFoldersManager($data['mailboxId'], $data['messagesIds']);
			$result = $mailMarkerManager->deleteMails();
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
		foreach ($ids as $index => $id)
		{
			list($messId, $mailboxId) = $this->parseMessageId($id);
			if (!$this->validateId($messId) || !is_numeric($mailboxId))
			{
				continue;
			}
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
	 * @param $id
	 *
	 * @return array
	 */
	private function parseMessageId($id)
	{
		return explode('-', $id);
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
	 * Validate message Id.
	 * @param string $id
	 *
	 * @return bool
	 */
	private function validateId($id)
	{
		return strlen($id) == 32;
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
	 *
	 * @return array
	 * @throws Exception
	 */
	public function syncMailboxAction($id)
	{
		$response = array(
			'new' => 0,
			'complete' => false,
			'status' => -1,
		);

		if ($mailbox = \Bitrix\Mail\MailboxTable::getUserMailbox($id))
		{
			session_write_close();

			$mailboxHelper = \Bitrix\Mail\Helper\Mailbox::createInstance($id);
			$mailboxHelper->setSyncParams(array(
				'full' => true,
			));

			$result = $mailboxHelper->sync();

			if ($result === false)
			{
				$this->errorCollection->add($mailboxHelper->getErrors()->toArray());
			}
			else
			{
				$response['new'] = $result;
				$response['complete'] = $mailboxHelper->getMailbox()['SYNC_LOCK'] < 0;
				$response['status'] = $mailboxHelper->getSyncStatus();
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
								if (strpos($item['id'], 'CRM') === 0)
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

		$attachments = array();
		$attachmentIds = array();
		if (!empty($data['__diskfiles']) && is_array($data['__diskfiles']) && Main\Loader::includeModule('disk'))
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
		if (!\Bitrix\Main\Loader::includeModule('crm'))
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

		if (!Main\Loader::includeModule('crm'))
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
		$fromField = strtoupper($fromField);
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
			$allEmails[] = strtolower($address->getEmail());
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

}
