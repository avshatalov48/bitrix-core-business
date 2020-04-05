<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSocNetMessageActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"MessageUserFrom" => "",
			"MessageUserTo" => "",
			"MessageText" => "",
			"MessageFormat" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("socialnetwork"))
			return CBPActivityExecutionStatus::Closed;

		if ($this->MessageFormat == 'robot' && CModule::IncludeModule('im'))
		{
			$this->sendRobotMessage();
			return CBPActivityExecutionStatus::Closed;
		}

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arMessageUserFrom = CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);

		$arMessageFields = array(
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"MESSAGE_TYPE" => SONET_MESSAGE_SYSTEM,
			"FROM_USER_ID" => $arMessageUserFrom,
			"MESSAGE" => CBPHelper::ConvertTextForMail($this->MessageText),
		);
		$ar = array();
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
				continue;

			$ar[] = $userTo;
			$arMessageFields["TO_USER_ID"] = $userTo;
			CSocNetMessages::Add($arMessageFields);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	private function sendRobotMessage()
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentId = $this->GetDocumentId();
		/** @var CBPDocumentService $documentService */
		$documentService = $runtime->GetService('DocumentService');

		$messageText = $this->MessageText;

		$CCTP = new CTextParser();
		$CCTP->allow = array(
			"HTML" => "N",
			"USER" => "N",
			"ANCHOR" => "Y",
			"BIU" => "Y",
			"IMG" => "Y", "QUOTE" => "N", "CODE" => "N", "FONT" => "Y", "LIST" => "Y",
			"SMILES" => "N", "NL2BR" => "Y", "VIDEO" => "N", "TABLE" => "N",
			"CUT_ANCHOR" => "N", "ALIGN" => "N"
		);

		$attach = new CIMMessageParamAttach(1, '#468EE5');
		$attach->AddUser(Array(
			'NAME' => GetMessage('BPSNMA_FORMAT_ROBOT'),
			'AVATAR' => '/bitrix/images/bizproc/message_robot.png'
		));
		$attach->AddDelimiter(Array('COLOR' => '#c6c6c6'));
		$attach->AddGrid(Array(
			Array(
				"NAME" => $documentService->getDocumentTypeName($this->GetDocumentType()) . ':',
				"VALUE" => $documentService->getDocumentName($documentId),
				"LINK" => $documentService->GetDocumentAdminPage($documentId),
				"DISPLAY" => "COLUMN",
				"WIDTH" => 60,
			),
		));
		$attach->AddDelimiter();
		$attach->AddHtml('<span style="color: #6E6E6E">'.
			$CCTP->convertText(htmlspecialcharsbx($messageText))
			.'</span>'
		);

		$arMessageUserFrom = CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);

		$tagSalt = md5($this->GetWorkflowInstanceId().'|'.$this->GetName());
		$arMessageFields = array(
			"=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
			"MESSAGE_TYPE" => IM_MESSAGE_SYSTEM,
			"MESSAGE_OUT" => CBPHelper::ConvertTextForMail($messageText),
			"ATTACH" => $attach,
			'NOTIFY_TAG' => 'ROBOT|'.implode('|', array_map('strtoupper', $documentId))	.'|'.$tagSalt
		);

		if ($arMessageUserFrom)
		{
			$arMessageFields['FROM_USER_ID'] = $arMessageUserFrom;
		}

		$ar = array();
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
				continue;

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
		if (!array_key_exists("MessageText", $arTestProperties) || strlen($arTestProperties["MessageText"]) <= 0)
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

		$dialog->setMap(array(
			'MessageUserFrom' => array(
				'Name' => GetMessage('BPSNMA_FROM'),
				'FieldName' => 'message_user_from',
				'Type' => 'user',
				'Default' => $fromDefault
			),
			'MessageUserTo' => array(
				'Name' => GetMessage('BPSNMA_TO'),
				'FieldName' => 'message_user_to',
				'Type' => 'user',
				'Required' => true,
				'Default' => 'author'
			),
			'MessageText' => array(
				'Name' => GetMessage('BPSNMA_MESSAGE'),
				'FieldName' => 'message_text',
				'Type' => 'text',
				'Required' => true
			)
		));

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
}