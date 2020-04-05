<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CBPPropertyVariableCondition
	extends CBPActivityCondition
{
	const CONDITION_JOINER_AND = 0;
	const CONDITION_JOINER_OR = 1;

	public $condition = null;

	public function __construct($condition)
	{
		$this->condition = $condition;
	}

	public function Evaluate(CBPActivity $ownerActivity)
	{
		if ($this->condition == null || !is_array($this->condition) || count($this->condition) <= 0)
			return true;

		if (!is_array($this->condition[0]))
			$this->condition = array($this->condition);

		$rootActivity = $ownerActivity->GetRootActivity();

		$result = array(0 => true);
		$i = 0;
		foreach ($this->condition as $cond)
		{
			$r = true;
			$joiner = empty($cond[3])? static::CONDITION_JOINER_AND : static::CONDITION_JOINER_OR;
			if ($rootActivity->IsPropertyExists($cond[0]))
			{
				if (!$this->CheckCondition($rootActivity->{$cond[0]}, $cond[1], $cond[2], $rootActivity->GetPropertyBaseType($cond[0]), $rootActivity, $rootActivity->getTemplatePropertyType($cond[0])))
				{
					$r = false;
				}
			}
			elseif ($rootActivity->IsVariableExists($cond[0]))
			{
				if (!$this->CheckCondition($rootActivity->GetVariable($cond[0]), $cond[1], $cond[2], $rootActivity->GetVariableBaseType($cond[0]), $rootActivity, $rootActivity->getVariableType($cond[0])))
				{
					$r = false;
				}
			}
			if ($joiner == static::CONDITION_JOINER_OR)
			{
				++$i;
				$result[$i] = $r;
			}
			elseif (!$r)
				$result[$i] = false;
		}
		$result = array_filter($result);
		return sizeof($result) > 0 ? true : false;
	}

	private function CheckCondition($field, $operation, $value, $type = null, $rootActivity = null, $property = null)
	{
		$result = false;

		$value = $rootActivity->ParseValue($value, is_array($property) ? $property['Type'] : $type);
		if ($type == "user")
		{
			$field = CBPHelper::ExtractUsersFromUserGroups($field, $rootActivity);
			$value = CBPHelper::ExtractUsersFromUserGroups($value, $rootActivity);
		}

		if (!is_array($field))
			$field = array($field);

		if ($operation == "in")
		{
			foreach ($field as $f)
			{
				if (is_array($value))
					$result = in_array($f, $value);
				else
					$result = (strpos($value, $f) !== false);

				if (!$result)
					break;
			}

			return $result;
		}

		if ($operation == "contain")
		{
			if (!is_array($value))
				$value = array($value);
			foreach ($value as $v)
			{
				foreach ($field as $f)
				{
					if (is_array($f))
						$result = in_array($v, $f);
					else
						$result = (strpos($f, $v) !== false);

					if ($result)
						break;
				}
				if (!$result)
					break;
			}

			return $result;
		}

		if (!is_array($value))
			$value = array($value);

		if (CBPHelper::IsAssociativeArray($field))
			$field = array_keys($field);
		if (CBPHelper::IsAssociativeArray($value))
			$value = array_keys($value);

		$i = 0;
		$fieldCount = count($field);
		$valueCount = count($value);

		if (($fieldCount == 0) && ($valueCount == 0))
			return in_array($operation, array("=", ">=", "<="));

		$iMax = max($fieldCount, $valueCount);
		while ($i < $iMax)
		{
			$f1 = ($fieldCount > $i) ? $field[$i] : $field[$fieldCount - 1];
			$v1 = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			if ($type == "datetime" || $type == "date")
			{
				if (($f1Tmp = MakeTimeStamp($f1, FORMAT_DATETIME)) === false)
				{
					if (($f1Tmp = MakeTimeStamp($f1, FORMAT_DATE)) === false)
					{
						if (($f1Tmp = MakeTimeStamp($f1, "YYYY-MM-DD HH:MI:SS")) === false)
						{
							if (($f1Tmp = MakeTimeStamp($f1, "YYYY-MM-DD")) === false)
								$f1Tmp = 0;
						}
					}
				}
				$f1 = $f1Tmp;

				if (($v1Tmp = MakeTimeStamp($v1, FORMAT_DATETIME)) === false)
				{
					if (($v1Tmp = MakeTimeStamp($v1, FORMAT_DATE)) === false)
					{
						if (($v1Tmp = MakeTimeStamp($v1, "YYYY-MM-DD HH:MI:SS")) === false)
						{
							if (($v1Tmp = MakeTimeStamp($v1, "YYYY-MM-DD")) === false)
								$v1Tmp = 0;
						}
					}
				}
				$v1 = $v1Tmp;
			}

			if ($type === 'bool')
			{
				$f1 = CBPHelper::getBool($f1);
				$v1 = CBPHelper::getBool($v1);
			}

			switch ($operation)
			{
				case ">":
					$result = ($f1 > $v1);
					break;
				case ">=":
					$result = ($f1 >= $v1);
					break;
				case "<":
					$result = ($f1 < $v1);
					break;
				case "<=":
					$result = ($f1 <= $v1);
					break;
				case "!=":
					$result = ($f1 != $v1);
					break;
				default:
					$result = ($f1 == $v1);
			}

			if (!$result)
				break;

			$i++;
		}

		return $result;
	}

	public static function GetPropertiesDialog($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue, $arCurrentValues = null, $formName = "")
	{
		if (count($arWorkflowParameters) <= 0 && count($arWorkflowVariables) <= 0)
			return null;

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();
			if (is_array($defaultValue))
			{
				$i = 0;
				foreach ($defaultValue as $value)
				{
					if (strlen($arCurrentValues["variable_condition_count"]) > 0)
						$arCurrentValues["variable_condition_count"] .= ",";
					$arCurrentValues["variable_condition_count"] .= $i;

					$arCurrentValues["variable_condition_field_".$i] = $value[0];
					$arCurrentValues["variable_condition_condition_".$i] = $value[1];
					$arCurrentValues["variable_condition_value_".$i] = $value[2];
					$arCurrentValues["variable_condition_joiner_".$i] = $value[3];

					//if (array_key_exists($value[0], $arWorkflowParameters))
					//{
					//	if ($arFieldTypes[$arWorkflowParameters[$value[0]]["Type"]]["BaseType"] == "user" && $arWorkflowParameters[$value[0]]["Type"] != "S:employee")
					//	{
					//		if (!is_array($arCurrentValues["variable_condition_value_".$i]))
					//			$arCurrentValues["variable_condition_value_".$i] = array($arCurrentValues["variable_condition_value_".$i]);
					//
					//		$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersArrayToString($arCurrentValues["variable_condition_value_".$i], $arWorkflowTemplate, $documentType);
					//	}
					//}
					//elseif (array_key_exists($value[0], $arWorkflowVariables))
					//{
					//	if ($arFieldTypes[$arWorkflowVariables[$value[0]]["Type"]]["BaseType"] == "user" && $arWorkflowParameters[$value[0]]["Type"] != "S:employee")
					//	{
					//		if (!is_array($arCurrentValues["variable_condition_value_".$i]))
					//			$arCurrentValues["variable_condition_value_".$i] = array($arCurrentValues["variable_condition_value_".$i]);
					//
					//		$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersArrayToString($arCurrentValues["variable_condition_value_".$i], $arWorkflowTemplate, $documentType);
					//	}
					//}

					$i++;
				}
			}
		}
		else
		{
			$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);
			foreach ($arVariableConditionCount as $i)
			{
				if (intval($i)."!" != $i."!")
					continue;

				$i = intval($i);

				if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["variable_condition_field_".$i]) <= 0)
					continue;

				$n = $arCurrentValues["variable_condition_field_".$i];

				$arErrors = array();
				$arCurrentValues["variable_condition_value_".$i] = $documentService->GetFieldInputValue(
					$documentType,
					array_key_exists($n, $arWorkflowParameters) ? $arWorkflowParameters[$n] : $arWorkflowVariables[$n],
					"variable_condition_value_".$i,
					$arCurrentValues,
					$arErrors
				);
			}
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsPVC", $arWorkflowParameters + $arWorkflowVariables, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"arProperties" => $arWorkflowParameters,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		if (!array_key_exists("variable_condition_count", $arCurrentValues) || strlen($arCurrentValues["variable_condition_count"]) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		$arResult = array();

		$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);
		foreach ($arVariableConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
				continue;

			$i = intval($i);

			if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["variable_condition_field_".$i]) <= 0)
				continue;

			$n = $arCurrentValues["variable_condition_field_".$i];

			$arErrors = array();
			$arCurrentValues["variable_condition_value_".$i] = $documentService->GetFieldInputValue(
				$documentType,
				array_key_exists($n, $arWorkflowParameters) ? $arWorkflowParameters[$n] : $arWorkflowVariables[$n],
				"variable_condition_value_".$i,
				$arCurrentValues,
				$arErrors
			);

			/*if (array_key_exists($arCurrentValues["variable_condition_field_".$i], $arWorkflowParameters))
			{
				if ($arFieldTypes[$arWorkflowParameters[$arCurrentValues["variable_condition_field_".$i]]["Type"]]["BaseType"] == "user")
					$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersStringToArray($arCurrentValues["variable_condition_value_".$i], $documentType, $ae);
			}
			elseif (array_key_exists($arCurrentValues["variable_condition_field_".$i], $arWorkflowVariables))
			{
				if ($arFieldTypes[$arWorkflowVariables[$arCurrentValues["variable_condition_field_".$i]]["Type"]]["BaseType"] == "user")
					$arCurrentValues["variable_condition_value_".$i] = CBPHelper::UsersStringToArray($arCurrentValues["variable_condition_value_".$i], $documentType, $ae);
			}*/

			$arResult[] = array(
				$arCurrentValues["variable_condition_field_".$i],
				htmlspecialcharsback($arCurrentValues["variable_condition_condition_".$i]),
				$arCurrentValues["variable_condition_value_".$i],
				(int) $arCurrentValues["variable_condition_joiner_".$i],
			);
		}

		if (count($arResult) <= 0)
		{
			$arErrors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		return $arResult;
	}

}
?>