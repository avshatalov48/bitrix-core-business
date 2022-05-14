<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

$runtime = CBPRuntime::GetRuntime();
$runtime->IncludeActivityFile('SequenceActivity');

class CBPIfElseBranchActivity extends CBPSequenceActivity
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = [
			"Title" => "",
			"Condition" => null,
		];
	}

	public function pullProperties(): array
	{
		$condition = $this->Condition;
		$this->Condition = null;
		$result = parent::pullProperties();
		$this->Condition = $condition;

		return $result;
	}

	protected function GetACNames()
	{
		$ar = parent::GetACNames();
		if ($this->arProperties["Condition"] != null)
		{
			$ar[] = mb_substr(get_class($this->arProperties["Condition"]), 3);
		}

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
				{
					break;
				}
			}
		}
	}

	private function CreateCondition($conditionCode, $data)
	{
		$runtime = CBPRuntime::GetRuntime();
		if ($runtime->IncludeActivityFile($conditionCode))
		{
			return CBPActivityCondition::CreateInstance($conditionCode, $data);
		}
		else
		{
			return null;
		}
	}

	public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
	{
		$arErrors = [];

		$runtime = CBPRuntime::GetRuntime();
		$arActivities = $runtime->SearchActivitiesByType("condition");

		foreach ($arTestProperties as $key => $value)
		{
			if (array_key_exists(mb_strtolower($key), $arActivities))
			{
				$runtime->IncludeActivityFile($key);

				$arErrors = array_merge(
					CBPActivityCondition::CallStaticMethod(
						$key,
						"ValidateProperties",
						[$value, $user]
					),
					$arErrors
				);
			}
		}

		return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
	}

	public static function GetPropertiesDialog(
		$documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables,
		$arCurrentValues = null, $formName = "", $popupWindow = null, $currentSiteId = null, $arWorkflowConstants = null
	)
	{
		if (!is_array($arWorkflowParameters))
		{
			$arWorkflowParameters = [];
		}
		if (!is_array($arWorkflowVariables))
		{
			$arWorkflowVariables = [];
		}

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
				[
					$documentType,
					$arWorkflowTemplate,
					$arWorkflowParameters,
					$arWorkflowVariables,
					(($defaultCondition == $activityKey) ? $defaultConditionValue : null),
					$arCurrentValues,
					$formName,
					$popupWindow,
					$currentSiteId,
					$arWorkflowConstants,
				]
			);
			if ($v == null)
			{
				unset($arActivities[$activityKey]);
				continue;
			}

			$arActivities[$activityKey]["PROPERTIES_DIALOG"] = $v;
			if ($firstConditionType == '')
			{
				$firstConditionType = $activityKey;
			}
		}

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = ["condition_type" => $defaultCondition];
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arActivities" => $arActivities,
				"arCurrentValues" => $arCurrentValues,
				"firstConditionType" => $firstConditionType,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables,
		$arCurrentValues, &$arErrors, $arWorkflowConstants = null
	)
	{
		$runtime = CBPRuntime::GetRuntime();
		$arActivities = $runtime->SearchActivitiesByType("condition", $documentType);

		if (!array_key_exists($arCurrentValues["condition_type"], $arActivities))
		{
			$arErrors[] = [
				"code" => "",
				"message" => GetMessage("BPIEBA_EMPTY_TYPE"),
			];

			return false;
		}

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		if (!is_array($arCurrentActivity["Properties"]))
		{
			$arCurrentActivity["Properties"] = [];
		}

		foreach ($arActivities as $key => $value)
		{
			if (array_key_exists($key, $arCurrentActivity["Properties"]))
			{
				unset($arCurrentActivity["Properties"][$key]);
			}
		}

		$condition = CBPActivityCondition::CallStaticMethod(
			$arCurrentValues["condition_type"],
			"GetPropertiesDialogValues",
			[
				$documentType,
				$arWorkflowTemplate,
				$arWorkflowParameters,
				$arWorkflowVariables,
				$arCurrentValues,
				&$arErrors,
				$arWorkflowConstants,
			]
		);

		if ($condition != null)
		{
			$arCurrentActivity["Properties"][$arCurrentValues["condition_type"]] = $condition;

			return true;
		}

		return false;
	}

	public function collectUsages()
	{
		$usages = parent::collectUsages();
		foreach ($this->arProperties as $property)
		{
			if ($property instanceof CBPActivityCondition)
			{
				$usages = array_merge($usages, $property->collectUsages($this));
			}
		}

		return $usages;
	}
}
