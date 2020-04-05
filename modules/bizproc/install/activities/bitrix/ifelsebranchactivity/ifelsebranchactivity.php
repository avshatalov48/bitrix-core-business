<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile("SequenceActivity");

class CBPIfElseBranchActivity
	extends CBPSequenceActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array("Title" => "", "Condition" => null);
	}

	protected function GetACNames()
	{
		$ar = parent::GetACNames();
		if ($this->arProperties["Condition"] != null)
			$ar[] = substr(get_class($this->arProperties["Condition"]), 3);
		return $ar;
	}

	public function InitializeFromArray($arParams)
	{
		if (is_array($arParams))
		{
			foreach ($arParams as $key => $value)
			{
				$this->arProperties["Condition"] = $this->CreateCondition($key, $value);
				if ($this->arProperties["Condition"] != null)
					break;
			}
		}
	}

	private function CreateCondition($conditionCode, $data)
	{
		$runtime = CBPRuntime::GetRuntime();
		if ($runtime->IncludeActivityFile($conditionCode))
			return CBPActivityCondition::CreateInstance($conditionCode, $data);
		else
			return null;
	}

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = array();

		$runtime = CBPRuntime::GetRuntime();
		$arActivities = $runtime->SearchActivitiesByType("condition");

		foreach ($arTestProperties as $key => $value)
		{
			if (array_key_exists(strtolower($key), $arActivities))
			{
				$runtime->IncludeActivityFile($key);

				$arErrors = array_merge(
					CBPActivityCondition::CallStaticMethod(
						$key,
						"ValidateProperties",
						array($value, $user)
					),
					$arErrors
				);
			}
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		$runtime = CBPRuntime::GetRuntime();
		$arActivities = $runtime->SearchActivitiesByType("condition", $documentType);

		$defaultCondition = "truecondition";
		$defaultConditionValue = null;
		if (!is_array($arCurrentValues))
		{
			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"]))
			{
				foreach ($arCurrentActivity["Properties"] as $key => $value)
				{
					if (array_key_exists($key, $arActivities))
					{
						$defaultCondition = $key;
						$defaultConditionValue = $value;
						break;
					}
				}
			}
		}

		$firstConditionType = "";
		$arActivityKeys = array_keys($arActivities);
		foreach ($arActivityKeys as $activityKey)
		{
			if (!empty($arActivities[$activityKey]['EXCLUDED']))
			{
				unset($arActivities[$activityKey]);
				continue;
			}

			$runtime->IncludeActivityFile($activityKey);
			$v = CBPActivityCondition::CallStaticMethod(
				$activityKey,
				"GetPropertiesDialog",
				array($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, (($defaultCondition == $activityKey) ? $defaultConditionValue : null), $arCurrentValues, $formName)
			);
			if ($v == null)
			{
				unset($arActivities[$activityKey]);
				continue;
			}

			$arActivities[$activityKey]["PROPERTIES_DIALOG"] = $v;
			if (strlen($firstConditionType) <= 0)
				$firstConditionType = $activityKey;
		}

		if (!is_array($arCurrentValues))
			$arCurrentValues = array("condition_type" => $defaultCondition);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arActivities" => $arActivities,
				"arCurrentValues" => $arCurrentValues,
				"firstConditionType" => $firstConditionType
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$runtime = CBPRuntime::GetRuntime();
		$arActivities = $runtime->SearchActivitiesByType("condition", $documentType);

		if (!array_key_exists($arCurrentValues["condition_type"], $arActivities))
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPIEBA_EMPTY_TYPE"),
			);
			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		if (!is_array($arCurrentActivity["Properties"]))
			$arCurrentActivity["Properties"] = array();

		foreach ($arActivities as $key => $value)
		{
			if (array_key_exists($key, $arCurrentActivity["Properties"]))
				unset($arCurrentActivity["Properties"][$key]);
		}

		$condition = CBPActivityCondition::CallStaticMethod(
			$arCurrentValues["condition_type"],
			"GetPropertiesDialogValues",
			array($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$arErrors)
		);

		if ($condition != null)
		{
			$arCurrentActivity["Properties"][$arCurrentValues["condition_type"]] = $condition;
			return true;
		}

		return false;
	}
}
?>