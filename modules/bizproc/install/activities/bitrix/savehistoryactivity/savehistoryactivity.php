<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSaveHistoryActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Name" => null,
			"UserId" => null,
		);
	}

	public function Execute()
	{
		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$historyService = $this->workflow->GetService("HistoryService");
		$documentService = $this->workflow->GetService("DocumentService");

		$userId = CBPHelper::ExtractUsers($this->UserId, $documentId, true);
		if ($userId == null || intval($userId) <= 0)
			$userId = 1;

		$historyIndex = $historyService->AddHistory(
			array(
				"DOCUMENT_ID" => $documentId,
				"NAME" => "New",
				"DOCUMENT" => null,
				"USER_ID" => $userId,
			)
		);

		$arDocument = $documentService->GetDocumentForHistory($documentId, $historyIndex);
		if (!is_array($arDocument))
			return CBPActivityExecutionStatus::Closed;

		$name = $this->Name;
		if ($name == null || strlen($name) <= 0)
		{
			if (array_key_exists("NAME", $arDocument) && is_string($arDocument["NAME"]) && strlen($arDocument["NAME"]) > 0)
				$name = $arDocument["NAME"];
			elseif (array_key_exists("TITLE", $arDocument) && is_string($arDocument["TITLE"]) && strlen($arDocument["TITLE"]) > 0)
				$name = $arDocument["TITLE"];
			else
				$name = Date("Y-m-d H:i:s");
		}

		$historyService->UpdateHistory(
			$historyIndex,
			array(
				"NAME" => $name,
				"DOCUMENT" => $arDocument,
			)
		);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();
		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"Name" => "sh_name",
			"UserId" => "sh_user_id",
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
						$arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
					else
						$arCurrentValues[$arMap[$k]] = "";
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
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arMap = array(
			"sh_name" => "Name",
			"sh_user_id" => "UserId",
		);

		$arProperties = array();
		foreach ($arMap as $key => $value)
			$arProperties[$value] = $arCurrentValues[$key];

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>