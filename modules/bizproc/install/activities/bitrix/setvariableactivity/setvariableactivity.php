<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPSetVariableActivity
	extends CBPActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title" => "",
			"VariableValue" => null,
		);
	}

	public function Execute()
	{
		$variables = $this->getRawProperty('VariableValue');
		if (!is_array($variables) || sizeof($variables) <= 0)
			return CBPActivityExecutionStatus::Closed;

		foreach ($variables as $name => $value)
		{
			$property = $this->getVariableType($name);
			$value = $this->ParseValue($value, isset($property['Type']) ? $property['Type'] : null);
			$this->SetVariable($name, $value);
		}

		return CBPActivityExecutionStatus::Closed;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		if (!is_array($arTestProperties)
			|| !array_key_exists("VariableValue", $arTestProperties)
			|| !is_array($arTestProperties["VariableValue"])
			|| count($arTestProperties["VariableValue"]) <= 0)
		{
			$arErrors[] = array("code" => "NotExist", "parameter" => "VariableValue", "message" => GetMessage("BPSVA_EMPTY_VARS"));
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"])
				&& array_key_exists("VariableValue", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["VariableValue"]))
			{
				foreach ($arCurrentActivity["Properties"]["VariableValue"] as $k => $v)
				{
					$arCurrentValues[$k] = $v;

					/*if ($arFieldTypes[$arWorkflowVariables[$k]["Type"]]["BaseType"] == "user")
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($arCurrentValues[$k], $arWorkflowTemplate, $documentType);
					}*/
				}
			}
		}
		else
		{
			$ind = 0;
			while (array_key_exists("variable_field_".$ind, $arCurrentValues))
			{
				if (array_key_exists($arCurrentValues["variable_field_".$ind], $arWorkflowVariables))
				{
					$varCode = $arCurrentValues["variable_field_".$ind];

					$arErrors = array();
					$arCurrentValues[$varCode] = $documentService->GetFieldInputValue($documentType, $arWorkflowVariables[$varCode], $varCode, $arCurrentValues, $arErrors);
				}
				$ind++;
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsVars", $arWorkflowVariables, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();

		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$arProperties = array("VariableValue" => array());

		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		if (count($arWorkflowVariables) <= 0)
		{
			$arErrors[] = array(
				"code" => "EmptyVariables",
				"parameter" => "",
				"message" => GetMessage("BPSVA_EMPTY_VARS"),
			);
			return false;
		}

		$l = strlen("variable_field_");
		foreach ($arCurrentValues as $key => $varCode)
		{
			if (substr($key, 0, $l) === "variable_field_")
			{
				$ind = substr($key, $l);
				if ($ind."!" === intval($ind)."!")
				{
					if (array_key_exists($varCode, $arWorkflowVariables))
						$arProperties["VariableValue"][$varCode] = $documentService->GetFieldInputValue($documentType, $arWorkflowVariables[$varCode], $varCode, $arCurrentValues, $arErrors);
				}
			}
		}

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}
}
?>