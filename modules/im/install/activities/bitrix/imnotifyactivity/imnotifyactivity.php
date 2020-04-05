<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPIMNotifyActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"MessageUserFrom" => "",
			"MessageUserTo" => "",
			"MessageSite" => "",
			"MessageOut" => "",
			"MessageType" => "",
		);
	}

	public function Execute()
	{
		if (!CModule::IncludeModule("im"))
			return CBPActivityExecutionStatus::Closed;

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$arMessageUserFrom = CBPHelper::ExtractUsers($this->MessageUserFrom, $documentId, true);
		$arMessageUserTo = CBPHelper::ExtractUsers($this->MessageUserTo, $documentId, false);

		$arMessageFields = array(
			"FROM_USER_ID" => $this->MessageType == IM_NOTIFY_SYSTEM? 0: $arMessageUserFrom,
			"NOTIFY_TYPE" => intval($this->MessageType), 
			"NOTIFY_MESSAGE" => $this->MessageSite, 
			"NOTIFY_MESSAGE_OUT" => $this->MessageOut,
			"NOTIFY_MODULE" => "bizproc",
			"NOTIFY_EVENT" => "activity"
		);

		$ar = array();
		foreach ($arMessageUserTo as $userTo)
		{
			if (in_array($userTo, $ar))
				continue;

			$ar[] = $userTo;
			$arMessageFields["TO_USER_ID"] = $userTo;
			CIMNotify::Add($arMessageFields);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!array_key_exists("MessageUserFrom", $arTestProperties) || count($arTestProperties["MessageUserFrom"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageUserFrom", "message" => GetMessage("BPIMNA_EMPTY_FROM"));
		if (!array_key_exists("MessageUserTo", $arTestProperties) || count($arTestProperties["MessageUserTo"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageUserTo", "message" => GetMessage("BPIMNA_EMPTY_TO"));
		if (!array_key_exists("MessageSite", $arTestProperties) || strlen($arTestProperties["MessageSite"]) <= 0)
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageText", "message" => GetMessage("BPIMNA_EMPTY_MESSAGE"));

		$from = array_key_exists("MessageUserFrom", $arTestProperties) ? $arTestProperties["MessageUserFrom"] : null;
		if ($user && $from !== $user->getBizprocId() && !$user->isAdmin())
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "MessageUserFrom", "message" => GetMessage("BPIMNA_EMPTY_FROM"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"MessageUserFrom" => "from_user_id",
			"MessageUserTo" => "to_user_id",
			"MessageSite" => "message_site",
			"MessageOut" => "message_out",
			"MessageType" => "message_type",
		);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arMap as $k => $v)
				{
					if (array_key_exists($k, $arCurrentActivity["Properties"]))
					{
						if ($k == "MessageUserFrom" || $k == "MessageUserTo")
							$arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
						else
							$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
					}
					else
					{
						if ($k == "MessageType")
							$arCurrentValues[$arMap[$k]] = 2;
						else
							$arCurrentValues[$arMap[$k]] = "";
					}
				}
			}
			else
			{
				foreach ($arMap as $k => $v)
					$arCurrentValues[$arMap[$k]] = "";
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				'user' => new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser)
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();
		$arMap = array(
			"from_user_id" => "MessageUserFrom",
			"to_user_id" => "MessageUserTo",
			"message_site" => "MessageSite",
			"message_out" => "MessageOut",
			"message_type" => "MessageType",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
		{
			if ($key == "from_user_id" || $key == "to_user_id")
				continue;

			$arProperties[$value] = $arCurrentValues[$key];
		}

		$user = new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser);
		if ($user->isAdmin())
		{
			$arProperties["MessageUserFrom"] = CBPHelper::UsersStringToArray($arCurrentValues["from_user_id"], $documentType, $arErrors);
			if (count($arErrors) > 0)
				return false;
		}
		else
		{
			$arProperties["MessageUserFrom"] = $user->getBizprocId();
		}

		$arProperties["MessageUserTo"] = CBPHelper::UsersStringToArray($arCurrentValues["to_user_id"], $documentType, $arErrors);
		if (count($arErrors) > 0)
			return false;

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}