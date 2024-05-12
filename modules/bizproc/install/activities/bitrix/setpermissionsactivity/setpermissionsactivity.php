<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetPermissionsActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"Permission" => array(),
			"Rewrite" => "Y", //deprecated property
			"SetMode" => false,
			"SetScope" => CBPSetPermissionsMode::ScopeWorkflow
		);
	}

	public function Execute()
	{
		$stateService = $this->workflow->GetService("StateService");
		$documentService = $this->workflow->GetService("DocumentService");
		$isExtended = $documentService->isExtendedPermsSupported($this->GetDocumentType());

		$mode = array('setMode' => $this->SetMode, 'setScope' => $this->SetScope);
		if ($mode['setMode'] === false)
		{
			$mode['setMode'] = $this->Rewrite != "N" ? CBPSetPermissionsMode::Clear : CBPSetPermissionsMode::Hold;
		}

		if (!$isExtended)
			$mode = ($mode['setMode'] == CBPSetPermissionsMode::Clear);

		$stateService->SetStatePermissions($this->GetWorkflowInstanceId(), $this->Permission, $mode);

		return CBPActivityExecutionStatus::Closed;
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]) && array_key_exists("Permission", $arCurrentActivity["Properties"]))
			{
				$current = $documentService->toExternalOperations($documentType, $arCurrentActivity["Properties"]["Permission"]);
				foreach ($arAllowableOperations as $operationKey => $operationValue)
				{
					$arCurrentValues["permission_".$operationKey] = CBPHelper::UsersArrayToString(
						$current[$operationKey] ?? null,
						$arWorkflowTemplate,
						$documentType
					);
				}
			}

			$arCurrentValues['set_mode'] = CBPSetPermissionsMode::Clear;
			$arCurrentValues['set_scope'] = CBPSetPermissionsMode::ScopeWorkflow;

			// old style override
			if (array_key_exists("Rewrite", $arCurrentActivity["Properties"]) && $arCurrentActivity["Properties"]["Rewrite"] == "N")
				$arCurrentValues['set_mode'] = CBPSetPermissionsMode::Hold;

			if (array_key_exists("SetMode", $arCurrentActivity["Properties"]) && $arCurrentActivity["Properties"]["SetMode"] !== false)
				$arCurrentValues["set_mode"] = $arCurrentActivity["Properties"]["SetMode"];
			if (array_key_exists("SetScope", $arCurrentActivity["Properties"]))
				$arCurrentValues["set_scope"] = $arCurrentActivity["Properties"]["SetScope"];

			$arCurrentValues['is_extended_mode'] = $documentService->isExtendedPermsSupported($documentType);
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arAllowableOperations" => $arAllowableOperations,
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$arProperties = array("Permission" => array(), "Rewrite" => true);

		$documentService = $runtime->GetService("DocumentService");
		$arAllowableOperations = $documentService->GetAllowableOperations($documentType);

		foreach ($arAllowableOperations as $operationKey => $operationValue)
		{
			$arProperties["Permission"][$operationKey] = CBPHelper::UsersStringToArray($arCurrentValues["permission_".$operationKey], $documentType, $arErrors);
			if (count($arErrors) > 0)
				return false;
		}

		$arProperties["Rewrite"] = '';
		$arProperties["SetMode"] = $arCurrentValues["set_mode"];
		$arProperties["SetScope"] = isset($arCurrentValues["set_scope"]) ? $arCurrentValues["set_scope"] : '';

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>