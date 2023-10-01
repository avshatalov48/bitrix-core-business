<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

class CBPSocNetMessageActivity extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"MessageUserFrom" => "",
			"MessageUserTo" => "",
			"MessageText" => "",
			"MessageFormat" => "",
		];
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("socialnetwork"))
		{
			return CBPActivityExecutionStatus::Closed;
		}

		if ($this->workflow->isDebug())
		{
			$this->writeDebugInfo($this->getDebugInfo(['MessageText' => $this->MessageText]));
		}

		if ($this->MessageFormat == 'robot' && CModule::IncludeModule('im'))
		{
			$this->sendRobotMessage();

			return CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arMessageUserFrom = CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);
		$messageText = $this->getMessageText();

		$arMessageFields = array(
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM,
			"FROM_USER_ID" => $arMessageUserFrom,
			"MESSAGE" => $messageText,
			'NOTIFY_MODULE' => 'bizproc',
			'NOTIFY_EVENT' => 'activity',
			'PUSH_MESSAGE' => $this->getPushText($messageText),
		);
		$ar = array();
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
			{
				continue;
			}

			$ar[] = $userTo;
			$arMessageFields["TO_USER_ID"] = $userTo;
			CSocNetMessages::Add($arMessageFields);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function getMessageText()
	{
		$messageText = $this->MessageText;
		if (is_array($messageText))
		{
			$messageText = implode(', ', CBPHelper::MakeArrayFlat($messageText));
		}

		return (string)$messageText;
	}

	private function getPushText(string $htmlMessage): string
	{
		$text = mb_substr(HTMLToTxt($htmlMessage, '', [], 0), 0, 200);
		if (mb_strlen($text) === 200)
		{
			$text .= '...';
		}

		return $text;
	}

	private function sendRobotMessage()
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentId = $this->GetDocumentId();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');

		$messageText = $this->getMessageText();

		$attachDescription = ''
			. GetMessage('BPSNMA_FORMAT_ROBOT') . '. '
			. $messageText
		;

		$attach = new CIMMessageParamAttach(1, '#468EE5');
		$attach->SetDescription($attachDescription);
		$attach->AddUser(Array(
			'NAME' => GetMessage('BPSNMA_FORMAT_ROBOT'),
			'AVATAR' => '/bitrix/images/bizproc/message_robot.png'
		));
		$attach->AddDelimiter();
		$attach->AddGrid([[
			'NAME' => $documentService->getDocumentTypeName($this->GetDocumentType()).':',
			'VALUE' => $documentService->getDocumentName($documentId),
			'LINK' => $documentService->GetDocumentAdminPage($documentId),
			'DISPLAY' => 'BLOCK'
		]]);

		$attach->AddDelimiter();
		$attach->AddMessage($messageText);

		$arMessageUserFrom = CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);

		$tagSalt = md5($this->GetWorkflowInstanceId().'|'.$this->GetName());
		$arMessageFields = array(
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"MESSAGE_OUT" => CBPHelper::convertBBtoText($messageText),
			"ATTACH" => $attach,
			'NOTIFY_TAG' => 'ROBOT|'.implode('|', array_map('mb_strtoupper', $documentId))	.'|'.$tagSalt,
			'NOTIFY_MODULE' => 'bizproc',
			'NOTIFY_EVENT' => 'activity',
			'PUSH_MESSAGE' => $this->getPushText($messageText),
		);

		if ($arMessageUserFrom)
		{
			$arMessageFields['FROM_USER_ID'] = $arMessageUserFrom;
		}

		$ar = array();
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
			{
				continue;
			}

			$ar[] = $userTo;
			$arMessageFields["TO_USER_ID"] = $userTo;
			CIMNotify::Add($arMessageFields);
		}
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (empty($arTestProperties["MessageUserTo"]))
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageUserTo", "message" => GetMessage("BPSNMA_EMPTY_TO"));
		}
		if (!array_key_exists("MessageText", $arTestProperties) || $arTestProperties["MessageText"] == '')
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageText", "message" => GetMessage("BPSNMA_EMPTY_MESSAGE"));
		}

		$from = array_key_exists("MessageUserFrom", $arTestProperties) ? $arTestProperties["MessageUserFrom"] : null;
		if ($user && $from !== $user->getBizprocId() && !$user->isAdmin())
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageUserFrom", "message" => GetMessage("BPSNMA_EMPTY_FROM"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, array(
			'documentType' => $documentType,
			'activityName' => $activityName,
			'workflowTemplate' => $arWorkflowTemplate,
			'workflowParameters' => $arWorkflowParameters,
			'workflowVariables' => $arWorkflowVariables,
			'currentValues' => $arCurrentValues
		));

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		$fromDefault = $user->isAdmin() ? null : $user->getBizprocId();

		$dialog->setMap(static::getPropertiesMap($documentType, ['fromDefault' => $fromDefault]));

		$dialog->setRuntimeData(array(
			'user' => $user
		));

		return $dialog;
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$arMap = array(
			"message_user_from" => "MessageUserFrom",
			"message_user_to" => "MessageUserTo",
			"message_text" => "MessageText",
			"message_format" => "MessageFormat",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "message_user_from" || $key == "message_user_to")
				continue;
			$arProperties[$value] = (string)$arCurrentValues[$key];
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			if (empty($arCurrentValues["message_user_from"]))
				$arProperties["MessageUserFrom"] = null;
			else
			{
				$arProperties["MessageUserFrom"] = CBPHelper::UsersStringToArray($arCurrentValues["message_user_from"], $documentType, $arErrors);
				if (count($arErrors) > 0)
					return false;
			}
		}
		else
		{
			$arProperties["MessageUserFrom"] = $user->getBizprocId();
		}

		$arProperties["MessageUserTo"] = CBPHelper::UsersStringToArray($arCurrentValues["message_user_to"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, $user);
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}

	protected static function getPropertiesMap(array $documentType, array $context = []): array
	{
		$fromDefault = $context['fromDefault'] ?? null;

		return [
			'MessageUserFrom' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSNMA_FROM'),
				'FieldName' => 'message_user_from',
				'Type' => 'user',
				'Default' => $fromDefault
			],
			'MessageUserTo' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSNMA_TO'),
				'FieldName' => 'message_user_to',
				'Type' => 'user',
				'Required' => true,
				'Multiple' => true,
				'Default' => \Bitrix\Bizproc\Automation\Helper::getResponsibleUserExpression($documentType)
			],
			'MessageText' => [
				'Name' => \Bitrix\Main\Localization\Loc::getMessage('BPSNMA_MESSAGE'),
				'Description' => \Bitrix\Main\Localization\Loc::getMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'message_text',
				'Type' => 'text',
				'Required' => true
			]
		];
	}
}