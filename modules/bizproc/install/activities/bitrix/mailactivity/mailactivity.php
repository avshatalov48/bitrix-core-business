<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;
use Bitrix\Main\Mail;
use Bitrix\Disk;

class CBPMailActivity extends CBPActivity
{
	const DEFAULT_SEPARATOR = ',';

	const FILE_TYPE_FILE = 'file';
	const FILE_TYPE_DISK = 'disk';

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"MailUserFrom" => "",
			"MailUserFromArray" => "",
			"MailUserTo" => "",
			"MailUserToArray" => "",
			"MailSubject" => "",
			"MailText" => "",
			"MailMessageType" => "plain",
			"MailMessageEncoded" => 0,
			"MailCharset" => "windows-1251",
			"DirrectMail" => "Y",
			"MailSite" => null,
			"MailSeparator" => static::DEFAULT_SEPARATOR,
			"File" => null,
			"FileType" => static::FILE_TYPE_FILE,
		);
	}

	public function Execute()
	{
		$separator = $this->MailSeparator;
		if (empty($separator))
		{
			$separator = static::DEFAULT_SEPARATOR;
		}

		$fromList = $this->getFromList($separator);

		if (empty($fromList))
		{
			$this->WriteToTrackingService(GetMessage("BPMA_EMPTY_PROP1"), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$strMailUserTo = $this->getMailUserTo($separator);

		if (empty($strMailUserTo))
		{
			$this->WriteToTrackingService(GetMessage("BPMA_EMPTY_PROP2"), 0, CBPTrackingType::Error);
			return CBPActivityExecutionStatus::Closed;
		}

		$charset = $this->MailCharset;
		$mailText = $this->getMailText();

		if (!$this->IsPropertyExists("DirrectMail") || $this->DirrectMail == "Y")
		{
			global $APPLICATION;

			$strMailUserTo = $APPLICATION->ConvertCharset($strMailUserTo, SITE_CHARSET, $charset);
			$strMailUserTo = Mail\Mail::encodeMimeString($strMailUserTo, $charset);

			$mailSubject = $APPLICATION->ConvertCharset($this->MailSubject, SITE_CHARSET, $charset);

			$mailText = $APPLICATION->ConvertCharset(CBPHelper::ConvertTextForMail($mailText), SITE_CHARSET, $charset);

			$context = new Mail\Context();
			$context->setCategory(Mail\Context::CAT_EXTERNAL);
			$context->setPriority(Mail\Context::PRIORITY_LOW);

			Mail\Mail::send([
				'CHARSET'      => $charset,
				'CONTENT_TYPE' => $this->MailMessageType == "html" ? "html" : "plain",
				'ATTACHMENT'   => $this->getAttachments(),
				'TO'           => $strMailUserTo,
				'SUBJECT'      => $mailSubject,
				'BODY'         => $mailText,
				'HEADER'       => array(
					'From'       => $this->encodeFrom($fromList[0], $charset),
					'Reply-To'   => $this->encodeReplyTo($fromList, $charset, $separator),
				),
				'CONTEXT' => $context,
			]);
		}
		else
		{
			$siteId = null;
			if ($this->IsPropertyExists("MailSite"))
			{
				$siteId = $this->MailSite;
			}
			if (strlen($siteId) <= 0)
			{
				$siteId = SITE_ID;
			}

			$arFields = array(
				"SENDER" => $this->encodeFrom($fromList[0], $charset),
				"REPLY_TO" => $this->encodeFrom($fromList[0], $charset),//$this->encodeReplyTo($fromList, $charset, $separator),
				"RECEIVER" => $strMailUserTo,
				"TITLE" => $this->MailSubject,
				"MESSAGE" => CBPHelper::ConvertTextForMail($mailText),
			);

			$files = $this->getFileIds();

			$eventName = ($this->MailMessageType == "html") ? "BIZPROC_HTML_MAIL_TEMPLATE" : "BIZPROC_MAIL_TEMPLATE";
			$event = new CEvent;
			$event->Send($eventName, $siteId, $arFields, "N", '', $files);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if ((!array_key_exists("MailUserFrom", $arTestProperties) || strlen($arTestProperties["MailUserFrom"]) <= 0)
			&& (!array_key_exists("MailUserFromArray", $arTestProperties) || count($arTestProperties["MailUserFromArray"]) <= 0))
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailUserFrom", "message" => GetMessage("BPMA_EMPTY_PROP1"));

		if ((!array_key_exists("MailUserTo", $arTestProperties) || strlen($arTestProperties["MailUserTo"]) <= 0)
			&& (!array_key_exists("MailUserToArray", $arTestProperties) || count($arTestProperties["MailUserToArray"]) <= 0))
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailUserTo", "message" => GetMessage("BPMA_EMPTY_PROP2"));

		if (!array_key_exists("MailSubject", $arTestProperties) || strlen($arTestProperties["MailSubject"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailSubject", "message" => GetMessage("BPMA_EMPTY_PROP3"));
		if (!array_key_exists("MailCharset", $arTestProperties) || strlen($arTestProperties["MailCharset"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailCharset", "message" => GetMessage("BPMA_EMPTY_PROP4"));
		if (!array_key_exists("MailMessageType", $arTestProperties))
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailMessageType", "message" => GetMessage("BPMA_EMPTY_PROP5"));
		elseif (!in_array($arTestProperties["MailMessageType"], array("plain", "html")))
			$arErrors[] = array("code" => "NotInRange", "parameter" => "MailMessageType", "message" => GetMessage("BPMA_EMPTY_PROP6"));
		if (!array_key_exists("MailText", $arTestProperties) || strlen($arTestProperties["MailText"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MailText", "message" => GetMessage("BPMA_EMPTY_PROP7"));

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "", $popupWindow = null, $siteId = '')
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues,
			'formName' => $formName,
			'siteId' => $siteId
		));

		$dialog->setMap(array(
			'MailUserFrom' => array(
				'Name' => GetMessage('BPMA_MAIL_USER_FROM'),
				'FieldName' => 'mail_user_from',
				'Type' => 'string',
				'Required' => true,
				'Getter' => static::getMailUserPropertyGetter()
			),
			'MailUserTo' => array(
				'Name' => GetMessage('BPMA_MAIL_USER_TO'),
				'FieldName' => 'mail_user_to',
				'Type' => 'string',
				'Required' => true,
				'Getter' => static::getMailUserPropertyGetter(),
				'Default' => 'author'
			),
			'MailSubject' => array(
				'Name' => GetMessage('BPMA_MAIL_SUBJECT'),
				'FieldName' => 'mail_subject',
				'Type' => 'text',
				'Required' => true
			),
			'MailText' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_text',
				'Type' => 'text',
				'Required' => true
			),
			'MailMessageType' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_message_type',
				'Type' => 'select',
				'Options' => array(
					'plain' => 'plain',
					'html' => 'html'
				),
				'Default' => 'plain'
			),
			'MailMessageEncoded' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_message_encoded',
				'Type' => 'int',
				'Default' => 0
			),
			'MailCharset' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_charset',
				'Type' => 'string',
			),
			'DirrectMail' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'dirrect_mail',
				'Type' => 'string',
			),
			'MailSite' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_site',
				'Type' => 'string',
			),
			'MailSeparator' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'mail_separator',
				'Type' => 'string',
				'Default' => static::DEFAULT_SEPARATOR
			),
			'File' => array(
				'Name' => GetMessage('BPMA_ATTACHMENT'),
				'FieldName' => 'file',
				'Type' => 'file',
				'Multiple' => true
			),
			'FileType' => array(
				'Name' => GetMessage('BPMA_ATTACHMENT_TYPE'),
				'FieldName' => 'file_type',
				'Type' => 'select',
				'Options' => array(
					static::FILE_TYPE_FILE => GetMessage('BPMA_ATTACHMENT_FILE'),
					static::FILE_TYPE_DISK => GetMessage('BPMA_ATTACHMENT_DISK')
				)
			),
		));

		$mailboxes = static::getMailboxes();

		if (!empty($mailboxes))
		{
			$dialog->setRuntimeData(array(
				'mailboxes' => $mailboxes
			));
		}

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();
		$arMap = array(
			"mail_user_from" => "MailUserFrom",
			"mail_user_to" => "MailUserTo",
			"mail_subject" => "MailSubject",
			"mail_text" => "MailText",
			"mail_message_type" => "MailMessageType",
			"mail_charset" => "MailCharset",
			"dirrect_mail" => "DirrectMail",
			"mail_site" => "MailSite",
			'mail_separator' => 'MailSeparator',
			'file' => 'File',
			'file_type' => 'FileType',
		);

		$properties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "mail_user_from" || $key == "mail_user_to")
				continue;
			$properties[$value] = $arCurrentValues[$key];
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
			$properties['File'] = isset($arCurrentValues["file"])
				? $arCurrentValues["file"] : $arCurrentValues["file_text"];
		}

		if (strlen($properties["MailSite"]) <= 0)
			$properties["MailSite"] = $arCurrentValues["mail_site_x"];

		$properties["MailSeparator"] = trim($properties["MailSeparator"]);
		if (strlen($properties["MailSeparator"]) <= 0)
			$properties["MailSeparator"] = static::DEFAULT_SEPARATOR;

		list($mailUserFromArray, $mailUserFrom) = CBPHelper::UsersStringToArray($arCurrentValues["mail_user_from"], $documentType, $arErrors, array(__CLASS__, "CheckEmailUserValue"));
		if (count($arErrors) > 0)
			return false;
		$properties["MailUserFrom"] = implode(", ", $mailUserFrom);
		$properties["MailUserFromArray"] = $mailUserFromArray;

		list($mailUserToArray, $mailUserTo) = CBPHelper::UsersStringToArray($arCurrentValues["mail_user_to"], $documentType, $arErrors, array(__CLASS__, "CheckEmailUserValue"));
		if (count($arErrors) > 0)
			return false;
		$properties["MailUserTo"] = implode(", ", $mailUserTo);
		$properties["MailUserToArray"] = $mailUserToArray;

		$arErrors = self::ValidateProperties($properties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

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
			if ($request->isAjaxRequest())
			{
				\CUtil::decodeURIComponent($rawData);
			}
			//TODO: fix for WAF, needs refactoring.
			$rawData = \Bitrix\Bizproc\Automation\Helper::unConvertExpressions($rawData, $documentType);

			$properties['MailText'] = self::encodeMailText($rawData);
			$properties['MailMessageEncoded'] = 1;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $properties;

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
			$fileIds = \CBPHelper::MakeArrayFlat((array)$this->ParseValue($this->getRawProperty('File'), 'file'));
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
				continue;

			$file = \CFile::makeFileArray($fileId);

			$contentId = sprintf(
				'bxacid.%s@mailactivity.bizproc',
				hash('crc32b', $file['external_id'].$file['size'].$file['name'])
			);

			$attachments[] = array(
				'ID'           => $contentId,
				'NAME'         => $fileName ?: $file['name'],
				'PATH'         => $file['tmp_name'],
				'CONTENT_TYPE' => $file['type'],
			);
		}

		return $attachments;
	}

	public static function CheckEmailUserValue($user)
	{
		if (check_email($user))
			return $user;

		return null;
	}

	private function encodeFrom(array $from, $charset)
	{
		$name = $from['name'] ? \Bitrix\Main\Text\Encoding::convertEncoding($from['name'], SITE_CHARSET, $charset) : '';
		$email = $from['email'];

		if ($name)
		{
			$name = str_replace(array('\\', '"', '<', '>'), array('/', '\'', '(', ')'), $name);
			return sprintf(
				'%s <%s>',
				Mail\Mail::encodeSubject($name, $charset),
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
		$arEmails = array();
		$arUsers = array();

		if (!is_array($ar))
			$ar = array($ar);

		$ar = CBPHelper::MakeArrayFlat($ar);

		foreach ($ar as $item)
		{
			$arItem = explode(",", $item);
			$flag = true;
			foreach ($arItem as $itemTmp)
			{
				if (check_email($itemTmp))
				{
					$flag = false;
					break;
				}
			}

			if ($flag)
				$arUsers[] = $item;
			else
				$arEmails[] = $item;
		}

		return array($arUsers, $arEmails);
	}


	private static function getMailboxes()
	{
		$mailboxes = [];
		CBitrixComponent::includeComponentClass('bitrix:main.mail.confirm');
		if (
			class_exists('MainMailConfirmComponent')
			&& method_exists('MainMailConfirmComponent', 'prepareMailboxes')
		)
		{
			$mailboxes = (array)MainMailConfirmComponent::prepareMailboxes();
		}

		return $mailboxes;
	}

	private static function getMailUserPropertyGetter()
	{
		return function($dialog, $property, $arCurrentActivity, $compatible = false)
		{
			/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */
			$k = $property['Id'];

			$result = $arCurrentActivity["Properties"][$k."Array"];

			if ($compatible)
			{
				$result = [CBPHelper::UsersArrayToString(
					$arCurrentActivity["Properties"][$k."Array"],
					$dialog->getWorkflowTemplate(),
					$dialog->getDocumentType()
				)];
			}

			if (strlen($arCurrentActivity["Properties"][$k]) > 0)
				$result[] = $arCurrentActivity["Properties"][$k];
			return $compatible ? implode(', ', array_filter($result)) : $result;
		};
	}

	private function getFromList($separator = self::DEFAULT_SEPARATOR)
	{
		$fromList = [];

		list($mailUserFromArray, $mailUserFromArrayString) = static::extractEmails($this->MailUserFromArray);

		$arMailUserFromArray = CBPHelper::ExtractUsers($mailUserFromArray, $this->GetDocumentId(), false);
		foreach ($arMailUserFromArray as $user)
		{
			$dbUser = CUser::GetList(($b = ""), ($o = ""), array("ID_EQUAL_EXACT" => $user));
			if ($arUser = $dbUser->Fetch())
			{
				$userName = '';
				$userEmail = preg_replace("#[\r\n]+#", "", $arUser["EMAIL"]);

				if (strlen($arUser["NAME"]) > 0 || strlen($arUser["LAST_NAME"]) > 0)
				{
					$userName = preg_replace(
						"#['\r\n]+#",
						"",
						CUser::FormatName(
							COption::GetOptionString("bizproc", "name_template", CSite::GetNameFormat(false), SITE_ID),
							$arUser,
							false, false
						)
					);
				}
				$fromList[] = ['name' => $userName, 'email' => $userEmail];
			}
		}

		$mailUserFromTmp = str_replace(', ', $separator, $this->MailUserFrom);
		if (strlen($mailUserFromTmp) > 0)
		{
			$address = new Mail\Address($mailUserFromTmp);
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
				$address = new Mail\Address($s);
				if ($address->validate())
				{
					$fromList[] = [
						'name' => $address->getName(),
						'email' => $address->getEmail()
					];
				}
			}
		}

		return $fromList;
	}

	private function getMailUserTo($separator = self::DEFAULT_SEPARATOR)
	{
		$strMailUserTo = "";

		list($MailUserToArray, $MailUserToArrayString) = static::extractEmails($this->MailUserToArray);

		$arMailUserToArray = CBPHelper::ExtractUsers($MailUserToArray, $this->GetDocumentId(), false);
		foreach ($arMailUserToArray as $user)
		{
			$dbUser = CUser::GetList(($b = ""), ($o = ""), array("ID_EQUAL_EXACT" => $user));
			if ($arUser = $dbUser->Fetch())
			{
				if (strlen($strMailUserTo) > 0)
				{
					$strMailUserTo .= $separator;
				}
				$strMailUserTo .= preg_replace("#[\r\n]+#", "", $arUser["EMAIL"]);
			}
		}

		$mailUserToTmp = str_replace(', ', $separator, $this->MailUserTo);
		if (strlen($mailUserToTmp) > 0)
		{
			if (strlen($strMailUserTo) > 0)
				$strMailUserTo .= $separator;
			$strMailUserTo .= preg_replace("#[\r\n]+#", "", $mailUserToTmp);
		}

		if (!empty($MailUserToArrayString))
		{
			foreach ($MailUserToArrayString as $s)
			{
				if (strlen($strMailUserTo) > 0)
					$strMailUserTo .= $separator;
				$strMailUserTo .= $s;
			}
		}

		return $strMailUserTo;
	}

	private function getMailText()
	{
		$mailText = $this->getRawProperty('MailText');
		if ($this->MailMessageEncoded)
		{
			$mailText = self::decodeMailText($mailText);
		}
		$mailText = $this->ParseValue($mailText, 'text');

		return $mailText;
	}

	private static function encodeMailText($text)
	{
		return 'base64,' . base64_encode($text);
	}

	public static function decodeMailText($text)
	{
		if (strpos($text, 'base64,') === 0)
		{
			$text = substr($text, 7);
			return base64_decode($text);
		}
		//compatible encode type
		return htmlspecialcharsback($text);
	}
}