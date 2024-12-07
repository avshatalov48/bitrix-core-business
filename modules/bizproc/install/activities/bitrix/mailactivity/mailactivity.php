<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Disk;
use Bitrix\Mail;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;

class CBPMailActivity extends CBPActivity
{
	const DEFAULT_SEPARATOR = ',';

	const FILE_TYPE_FILE = 'file';
	const FILE_TYPE_DISK = 'disk';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			'Title' => '',
			'MailUserFrom' => '',
			'MailUserFromArray' => '',
			'MailUserTo' => '',
			'MailUserToArray' => '',
			'MailSubject' => '',
			'MailText' => '',
			'MailMessageType' => 'plain',
			'MailMessageEncoded' => 0,
			'MailCharset' => 'windows-1251',
			'DirrectMail' => 'Y',
			'MailSite' => null,
			'MailSeparator' => static::DEFAULT_SEPARATOR,
			'File' => null,
			'FileType' => static::FILE_TYPE_FILE,
		];
	}

	public function Execute()
	{
		$separator = $this->MailSeparator;
		if (empty($separator))
		{
			$separator = static::DEFAULT_SEPARATOR;
		}

		$fromList = $this->getFromList($separator);
		$strMailUserTo = $this->getMailUserTo($separator);

		if ($this->workflow->isDebug())
		{
			$this->logDebugUsers([
				'MailUserFrom' => $fromList,
				'MailUserTo' => $strMailUserTo,
			]);
		}

		if (empty($fromList))
		{
			$this->WriteToTrackingService(
				Loc::getMessage('BPMA_EMPTY_PROP1'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		if (empty($strMailUserTo))
		{
			$this->WriteToTrackingService(
				Loc::getMessage('BPMA_EMPTY_PROP2'),
				0,
				CBPTrackingType::Error
			);

			return CBPActivityExecutionStatus::Closed;
		}

		$charset = $this->MailCharset;
		$mailMessageType = $this->MailMessageType;
		$mailText = $this->getMailText($mailMessageType);
		$mailSubject = CBPHelper::stringify($this->MailSubject);

		if ($this->workflow->isDebug())
		{
			$this->logDebugMessage([
				'MailText' => $mailText,
				'MailSubject' => $mailSubject,
			]);
		}

		if (!$this->IsPropertyExists('DirrectMail') || $this->DirrectMail == 'Y')
		{
			$strMailUserTo = Encoding::convertEncoding($strMailUserTo, SITE_CHARSET, $charset);
			$strMailUserTo = Main\Mail\Mail::encodeMimeString($strMailUserTo, $charset);

			$mailSubject = Encoding::convertEncoding($mailSubject, SITE_CHARSET, $charset);

			$mailText = Encoding::convertEncoding(
				CBPHelper::ConvertTextForMail($mailText),
				SITE_CHARSET,
				$charset
			);

			$context = new Main\Mail\Context();
			$context->setCategory(Main\Mail\Context::CAT_EXTERNAL);
			$context->setPriority(Main\Mail\Context::PRIORITY_LOW);

			Main\Mail\Mail::send([
				'CHARSET' => $charset,
				'CONTENT_TYPE' => $mailMessageType === 'html' ? 'html' : 'plain',
				'ATTACHMENT' => $this->getAttachments(),
				'TO' => $strMailUserTo,
				'SUBJECT' => $mailSubject,
				'BODY' => $mailText,
				'HEADER' => [
					'From' => $this->encodeFrom($fromList[0], $charset),
					'Reply-To' => $this->encodeReplyTo($fromList, $charset, $separator),
				],
				'CONTEXT' => $context,
			]);
		}
		else
		{
			$siteId = null;
			if ($this->IsPropertyExists('MailSite'))
			{
				$siteId = $this->MailSite;
			}
			if ($siteId == '')
			{
				$siteId = SITE_ID;
			}

			$arFields = [
				'SENDER' => $this->encodeFrom($fromList[0], $charset),
				'REPLY_TO' => $this->encodeFrom($fromList[0], $charset),//$this->encodeReplyTo($fromList, $charset, $separator),
				'RECEIVER' => $strMailUserTo,
				'TITLE' => $mailSubject,
				'MESSAGE' => CBPHelper::ConvertTextForMail($mailText),
			];

			$files = $this->getFileIds();

			$eventName = ($mailMessageType === 'html') ? 'BIZPROC_HTML_MAIL_TEMPLATE' : 'BIZPROC_MAIL_TEMPLATE';
			$event = new CEvent;
			$event->Send($eventName, $siteId, $arFields, 'N', '', $files);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		if (
			(
				!array_key_exists('MailUserFrom', $arTestProperties)
				|| $arTestProperties['MailUserFrom'] == ''
			)
			&& (
				!array_key_exists('MailUserFromArray', $arTestProperties)
				|| count($arTestProperties['MailUserFromArray']) <= 0
			)
		)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailUserFrom',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP1'),
			];
		}

		if (
			(
				!array_key_exists('MailUserTo', $arTestProperties)
				|| $arTestProperties['MailUserTo'] == ''
			)
			&& (
				!array_key_exists('MailUserToArray', $arTestProperties)
				|| count($arTestProperties['MailUserToArray']) <= 0)
		)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailUserTo',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP2'),
			];
		}

		if (
			!array_key_exists('MailSubject', $arTestProperties)
			|| $arTestProperties['MailSubject'] == ''
		)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailSubject',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP3'),
			];
		}

		if (
			!array_key_exists('MailCharset', $arTestProperties)
			|| $arTestProperties['MailCharset'] == ''
		)
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailCharset',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP4'),
			];
		}

		if (!array_key_exists('MailMessageType', $arTestProperties))
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailMessageType',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP5'),
			];
		}
		elseif (!in_array($arTestProperties['MailMessageType'], ['plain', 'html']))
		{
			$arErrors[] = [
				'code' => 'NotInRange',
				'parameter' => 'MailMessageType',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP6'),
			];
		}

		if (!array_key_exists('MailText', $arTestProperties) || $arTestProperties['MailText'] == '')
		{
			$arErrors[] = [
				'code' => 'NotExist',
				'parameter' => 'MailText',
				'message' => Loc::getMessage('BPMA_EMPTY_PROP7'),
			];
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog(
		$documentType,
		$activityName,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues = null,
		$formName = '',
		$popupWindow = null,
		$siteId = ''
	)
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(
			__FILE__,
			[
				'documentType' => $documentType,
				'activityName' => $activityName,
				'workflowTemplate' => $arWorkflowTemplate,
				'workflowParameters' => $arWorkflowParameters,
				'workflowVariables' => $arWorkflowVariables,
				'currentValues' => $arCurrentValues,
				'formName' => $formName,
				'siteId' => $siteId
			]
		);

		$dialog->setMap(self::getPropertiesMap($documentType));

		$dialog->setRuntimeData([
			'mailboxes' => (array)Main\Mail\Sender::prepareUserMailboxes()
		]);

		return $dialog;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		return [
			'MailUserFrom' => [
				'Name' => Loc::getMessage('BPMA_MAIL_USER_FROM'),
				'FieldName' => 'mail_user_from',
				'Type' => 'string',
				'Required' => true,
				'Getter' => static::getMailUserPropertyGetter(),
			],
			'MailUserTo' => [
				'Name' => Loc::getMessage('BPMA_MAIL_USER_TO'),
				'FieldName' => 'mail_user_to',
				'Type' => 'user',
				'Required' => true,
				'Multiple' => true,
				'Getter' => static::getMailUserPropertyGetter(),
				'Default' => \Bitrix\Bizproc\Automation\Helper::getResponsibleUserExpression($documentType),
			],
			'MailSubject' => [
				'Name' => Loc::getMessage('BPMA_MAIL_SUBJECT'),
				'Description' => Loc::getMessage('BPMA_MAIL_SUBJECT'),
				'FieldName' => 'mail_subject',
				'Type' => 'string',
				'Required' => true,
			],
			'MailText' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_text',
				'Type' => 'text',
				'Required' => true,
			],
			'MailMessageType' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_message_type',
				'Type' => 'select',
				'Options' => [
					'plain' => 'plain',
					'html' => 'html',
				],
				'Default' => 'plain',
			],
			'MailMessageEncoded' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_message_encoded',
				'Type' => 'int',
				'Default' => 0,
			],
			'MailCharset' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_charset',
				'Type' => 'string',
			],
			'DirrectMail' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'dirrect_mail',
				'Type' => 'string',
			],
			'MailSite' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_site',
				'Type' => 'string',
			],
			'MailSeparator' => [
				'Name' => Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_separator',
				'Type' => 'string',
				'Default' => static::DEFAULT_SEPARATOR,
			],
			'File' => [
				'Name' => Loc::getMessage('BPMA_ATTACHMENT'),
				'FieldName' => 'file',
				'Type' => 'file',
				'Multiple' => true,
			],
			'FileType' => [
				'Name' => Loc::getMessage('BPMA_ATTACHMENT_TYPE'),
				'FieldName' => 'file_type',
				'Type' => 'select',
				'Options' => [
					static::FILE_TYPE_FILE => GetMessage('BPMA_ATTACHMENT_FILE'),
					static::FILE_TYPE_DISK => GetMessage('BPMA_ATTACHMENT_DISK'),
				],
			],
		];
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$activityName,
		&$arWorkflowTemplate,
		&$arWorkflowParameters,
		&$arWorkflowVariables,
		$arCurrentValues,
		&$arErrors
	)
	{
		$arErrors = [];
		$arMap = [
			'mail_user_from' => 'MailUserFrom',
			'mail_user_to' => 'MailUserTo',
			'mail_subject' => 'MailSubject',
			'mail_text' => 'MailText',
			'mail_message_type' => 'MailMessageType',
			'mail_charset' => 'MailCharset',
			'dirrect_mail' => 'DirrectMail',
			'mail_site' => 'MailSite',
			'mail_separator' => 'MailSeparator',
			'file' => 'File',
			'file_type' => 'FileType',
		];

		$properties = [];
		foreach ($arMap as $key => $value)
		{
			if ($key == 'mail_user_from' || $key == 'mail_user_to')
			{
				continue;
			}
			$properties[$value] = $arCurrentValues[$key] ?? null;
		}

		if ($properties['FileType'] === static::FILE_TYPE_DISK)
		{
			$properties['File'] = [];
			foreach ((array)$arCurrentValues["file"] as $attachmentId)
			{
				$attachmentId = (int)$attachmentId;
				if ($attachmentId > 0)
				{
					$properties['File'][] = $attachmentId;
				}
			}
		}
		else
		{
			$properties['File'] = null;
			if (isset($arCurrentValues['file']))
			{
				$properties['File'] = $arCurrentValues['file'];
			}
			elseif (isset($arCurrentValues['file_text']))
			{
				$properties['File'] = $arCurrentValues['file_text'];
			}
		}

		if ($properties['MailSite'] == '')
		{
			$properties['MailSite'] = $arCurrentValues['mail_site_x'];
		}

		$properties['MailSeparator'] = trim($properties['MailSeparator']);
		if ($properties['MailSeparator'] == '')
		{
			$properties['MailSeparator'] = static::DEFAULT_SEPARATOR;
		}

		[$mailUserFromArray, $mailUserFrom] = CBPHelper::UsersStringToArray(
			$arCurrentValues['mail_user_from'],
			$documentType,
			$arErrors,
			[__CLASS__, 'CheckEmailUserValue']
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$properties['MailUserFrom'] = implode(', ', $mailUserFrom);
		$properties['MailUserFromArray'] = $mailUserFromArray;

		[$mailUserToArray, $mailUserTo] = CBPHelper::UsersStringToArray(
			$arCurrentValues['mail_user_to'],
			$documentType,
			$arErrors,
			[__CLASS__, 'CheckEmailUserValue']
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$properties['MailUserTo'] = implode(', ', $mailUserTo);
		$properties['MailUserToArray'] = $mailUserToArray;

		$arErrors = self::ValidateProperties(
			$properties,
			new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
		);
		if (count($arErrors) > 0)
		{
			return false;
		}

		$properties['MailMessageEncoded'] = 0;
		if ($properties['MailMessageType'] === 'html')
		{
			$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
			$rawData = $request->getPostList()->getRaw('mail_text');
			if ($rawData === null)
			{
				$rawData = (array)$request->getPostList()->getRaw('form_data');
				$rawData = $rawData['mail_text'];
			}
			//TODO: fix for WAF, needs refactoring.
			$rawData = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($rawData, $documentType);

			$properties['MailText'] = self::encodeMailText($rawData);
			$properties['MailMessageEncoded'] = 1;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity['Properties'] = $properties;

		return true;
	}

	private function getFiles()
	{
		$files = [];
		if ($this->FileType === static::FILE_TYPE_DISK)
		{
			if (Loader::includeModule('disk'))
			{
				$diskFiles = \CBPHelper::MakeArrayFlat((array)$this->File);
				foreach ($diskFiles as $diskFileId)
				{
					/** @var Disk\File $file */
					$file = Disk\File::loadById($diskFileId);
					if ($file)
					{
						$id = (int)$file->getFileId();
						$name = $file->getName();

						$files[$id] = ['id' => $id, 'name' => $name];
					}
				}
			}
		}
		else
		{
			$fileIds = \CBPHelper::MakeArrayFlat(
				(array)$this->ParseValue($this->getRawProperty('File'), 'file')
			);
			$fileIds = array_filter($fileIds);

			foreach ($fileIds as $id)
			{
				$files[$id] = ['id' => $id];
			}
		}

		return array_values($files);
	}

	private function getFileIds()
	{
		$files = $this->getFiles();

		return array_column($files, 'id');
	}

	private function getAttachments()
	{
		$files = $this->getFiles();
		$attachments = [];
		foreach ($files as $file)
		{
			$fileId = $file['id'];
			$fileName = isset($file['name']) ? $file['name'] : '';

			if (!is_int($fileId))
			{
				continue;
			}

			$file = \CFile::makeFileArray($fileId);

			$contentId = sprintf(
				'bxacid.%s@mailactivity.bizproc',
				hash('crc32b', $file['external_id'].$file['size'].$file['name'])
			);

			$attachments[] = array(
				'ID' => $contentId,
				'NAME' => $fileName ?: $file['name'],
				'PATH' => $file['tmp_name'],
				'CONTENT_TYPE' => $file['type'],
			);
		}

		return $attachments;
	}

	public static function CheckEmailUserValue($user)
	{
		$address = new Main\Mail\Address($user);
		if ($address->validate())
		{
			return $user;
		}

		return null;
	}

	private function encodeFrom(array $from, $charset)
	{
		$name =
			$from['name']
				? \Bitrix\Main\Text\Encoding::convertEncoding($from['name'], SITE_CHARSET, $charset)
				: ''
		;
		$email = $from['email'];

		if ($name)
		{
			$name = str_replace(array('\\', '"', '<', '>'), array('/', '\'', '(', ')'), $name);

			return sprintf(
				'%s <%s>',
				Main\Mail\Mail::encodeSubject($name, $charset),
				$email
			);
		}

		return $email;
	}

	private function encodeReplyTo(array $fromList, $charset, $separator = self::DEFAULT_SEPARATOR)
	{
		$reply = [];
		foreach ($fromList as $from)
		{
			$reply[] = $this->encodeFrom($from, $charset);
		}

		return implode($separator, $reply);
	}

	private static function extractEmails($ar)
	{
		$emails = [];
		$users = [];

		$ar = CBPHelper::MakeArrayFlat($ar);

		foreach ($ar as $item)
		{
			$arItem = explode(',', $item);
			$flag = true;
			foreach ($arItem as $itemTmp)
			{
				if (check_email($itemTmp))
				{
					$emails[] = $itemTmp;
					$flag = false;
				}
			}

			if ($flag)
			{
				$users[] = $item;
			}
		}

		return [$users, $emails];
	}

	private static function getMailUserPropertyGetter()
	{
		return function($dialog, $property, $arCurrentActivity, $compatible = false)
		{
			/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
			$k = $property['Id'];

			$result = $arCurrentActivity['Properties'][$k . 'Array'];
			if (!is_array($result))
			{
				$result = [];
			}

			if ($compatible)
			{
				$result = [CBPHelper::UsersArrayToString(
					$arCurrentActivity['Properties'][$k . 'Array'],
					$dialog->getWorkflowTemplate(),
					$dialog->getDocumentType()
				)];
			}

			if ($arCurrentActivity['Properties'][$k] <> '')
			{
				$result[] = $arCurrentActivity['Properties'][$k];
			}

			return $compatible ? implode(', ', array_filter($result)) : $result;
		};
	}

	private function getFromList($separator = self::DEFAULT_SEPARATOR)
	{
		$fromList = [];

		[$mailUserFromArray, $mailUserFromArrayString] = static::extractEmails($this->MailUserFromArray);

		$arMailUserFromArray = CBPHelper::ExtractUsers($mailUserFromArray, $this->GetDocumentId(), false);
		foreach ($arMailUserFromArray as $user)
		{
			$dbUser = CUser::GetList('', '', array('ID_EQUAL_EXACT' => $user));
			if ($arUser = $dbUser->Fetch())
			{
				$userName = '';
				$userEmail = preg_replace("#[\r\n]+#", '', $arUser['EMAIL']);

				if ($arUser['NAME'] <> '' || $arUser['LAST_NAME'] <> '')
				{
					$userName = preg_replace(
						"#['\r\n]+#",
						"",
						CUser::FormatName(
							COption::GetOptionString(
								'bizproc',
								'name_template',
								CSite::GetNameFormat(false),
								SITE_ID
							),
							$arUser,
							false,
							false
						)
					);
				}
				$fromList[] = ['name' => $userName, 'email' => $userEmail];
			}
		}

		$mailUserFromTmp = str_replace(', ', $separator, $this->MailUserFrom);
		if ($mailUserFromTmp <> '')
		{
			$address = new Main\Mail\Address($mailUserFromTmp);
			if ($address->validate())
			{
				$fromList[] = [
					'name' => $address->getName(),
					'email' => $address->getEmail()
				];
			}
		}

		if (!empty($mailUserFromArrayString))
		{
			foreach ($mailUserFromArrayString as $s)
			{
				$address = new Main\Mail\Address($s);
				if ($address->validate())
				{
					$fromList[] = [
						'name' => $address->getName(),
						'email' => $address->getEmail()
					];
				}
			}
		}

		return $this->filterFromList($fromList);
	}

	private function filterFromList(array $fromList): array
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return $fromList;
		}

		$confirmedEmails = $this->getConfirmedEmails(array_column($fromList, 'email'));

		foreach ($fromList as $i => $item)
		{
			if (!in_array($item['email'], $confirmedEmails))
			{
				unset($fromList[$i]);
			}
		}

		return array_values($fromList);
	}

	private function filterToList(array $toList): array
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return $toList;
		}

		return array_slice($toList, 0, 10);
	}

	private function getConfirmedEmails(array $emailsToCheck): array
	{
		$result = [];
		$emailsToCheck = array_filter($emailsToCheck);

		if (!$emailsToCheck)
		{
			return $result;
		}

		$crmAddress = new Main\Mail\Address(Main\Config\Option::get('crm', 'mail', ''));
		if ($crmAddress->validate())
		{
			$result[] = $crmAddress->getEmail();
		}

		if (Loader::includeModule('mail'))
		{
			$res = Mail\MailboxTable::getList(array(
				'filter' => array(
					array(
						'LOGIC' => 'OR',
						'@EMAIL' => $emailsToCheck,
						'@NAME' => $emailsToCheck,
						'@LOGIN' => $emailsToCheck,
					),
					'=ACTIVE' => 'Y',
					'=SERVER_TYPE' => 'imap',
				),
			));

			while ($mailbox = $res->fetch())
			{
				Mail\MailboxTable::normalizeEmail($mailbox);
				$result[] = $mailbox['EMAIL'];
			}
		}

		$res = Main\Mail\Internal\SenderTable::getList(array(
			'filter' => array(
				'IS_CONFIRMED' => true,
				'@EMAIL' => $emailsToCheck
			)
		));

		while ($item = $res->fetch())
		{
			$result[] = mb_strtolower($item['EMAIL']);
		}

		return array_unique($result);
	}

	private function getMailUserTo($separator = self::DEFAULT_SEPARATOR)
	{
		$result = [];

		[$mailUserToArray, $mailUserToArrayString] = static::extractEmails($this->MailUserToArray);

		$userIds = CBPHelper::ExtractUsers($mailUserToArray, $this->GetDocumentId());
		foreach ($userIds as $userId)
		{
			$listResult = CUser::GetList('', '', ['ID_EQUAL_EXACT' => $userId]);
			if ($row = $listResult->fetch())
			{
				$userEmail = trim(preg_replace("#[\r\n]+#", '', $row['EMAIL']));
				if ($userEmail)
				{
					$result[] = $userEmail;
				}
			}
		}

		$toEmails = explode(', ', $this->MailUserTo);
		if ($toEmails)
		{
			foreach ($toEmails as $toEmail)
			{
				$toEmail = trim(preg_replace("#[\r\n]+#", '', $toEmail));
				if ($toEmail)
				{
					$result[] = $toEmail;
				}
			}
		}

		if (!empty($mailUserToArrayString))
		{
			foreach ($mailUserToArrayString as $s)
			{
				$result[] = $s;
			}
		}

		return implode($separator, $this->filterToList($result));
	}

	private function getMailText($mailMessageType)
	{
		$mailText = $this->getRawProperty('MailText');
		if ($this->MailMessageEncoded)
		{
			$mailText = self::decodeMailText($mailText);
		}

		$mailText = $this->ParseValue(
			$mailText,
			'text',
			function($objectName, $fieldName, $property, $result) use ($mailMessageType)
			{
				if (is_array($result))
				{
					$result = implode(', ', CBPHelper::makeArrayFlat($result));
				}

				if ($mailMessageType === 'html' && isset($property['ValueContentType']))
				{
					if ($property['ValueContentType'] === 'bb')
					{
						$sanitizer = new \CBXSanitizer();
						$sanitizer->SetLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
						$sanitizer->DeleteAttributes(['id']);

						$result = $sanitizer->SanitizeHtml(
							\CBPHelper::convertBBtoText($result)
						);
					}
					elseif ($property['ValueContentType'] !== 'html' && isset($property['Type']) && $property['Type'] !== 'S:HTML')
					{
						$result = htmlspecialcharsbx($result);
					}
				}

				return $result;
			}
		);

		return $mailText;
	}

	private static function encodeMailText($text)
	{
		return 'base64,' . base64_encode($text);
	}

	public static function decodeMailText($text)
	{
		if (mb_strpos($text, 'base64,') === 0)
		{
			$text = mb_substr($text, 7);

			return base64_decode($text);
		}

		//compatible encode type
		return htmlspecialcharsback($text);
	}

	protected function logDebugUsers(array $values = [])
	{
		$fullMap = static::getPropertiesMap($this->getDocumentType());
		$map = [
			'MailUserFrom' => $fullMap['MailUserFrom']['Name'],
			'MailUserTo' => $fullMap['MailUserTo']['Name'],
		];

		$this->writeDebugInfo($this->getDebugInfo($values, $map));
	}

	protected function logDebugMessage(array $values = [])
	{
		$fullMap = static::getPropertiesMap($this->getDocumentType());
		$map = [
			'MailSubject' => $fullMap['MailSubject'],
			'MailText' => Loc::getMessage('BPMA_MAIL_TEXT'),
		];

		$this->writeDebugInfo($this->getDebugInfo($values, $map));
	}
}
