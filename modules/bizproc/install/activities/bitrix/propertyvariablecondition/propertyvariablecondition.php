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
		{
			return true;
		}

		if (!is_array($this->condition[0]))
		{
			$this->condition = array($this->condition);
		}

		$rootActivity = $ownerActivity->GetRootActivity();

		$result = [0 => true];
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

	/**
	 * @param $field
	 * @param $operation
	 * @param $value
	 * @param null $baseType
	 * @param CBPActivity $rootActivity
	 * @param null $property
	 * @return bool
	 */
	private function CheckCondition($field, $operation, $value, $baseType, $rootActivity, $property = null)
	{
		if ($operation === 'empty')
		{
			return CBPHelper::isEmptyValue($field);
		}
		elseif ($operation === '!empty')
		{
			return !CBPHelper::isEmptyValue($field);
		}

		$result = false;
		$type = is_array($property) ? $property['Type'] : $baseType;

		$value = $rootActivity->ParseValue($value, $type);
		if ($baseType == "user")
		{
			$field = CBPHelper::ExtractUsersFromUserGroups($field, $rootActivity);
			$value = CBPHelper::ExtractUsersFromUserGroups($value, $rootActivity);
		}

		if (!is_array($field))
		{
			$field = array($field);
		}

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
		{
			$value = array($value);
		}

		if (CBPHelper::IsAssociativeArray($field))
		{
			$field = array_keys($field);
		}

		if (CBPHelper::IsAssociativeArray($value))
		{
			$value = array_keys($value);
		}

		$i = 0;
		$fieldCount = count($field);
		$valueCount = count($value);

		if (($fieldCount == 0) && ($valueCount == 0))
		{
			return in_array($operation, array("=", ">=", "<="));
		}

		$iMax = max($fieldCount, $valueCount);
		while ($i < $iMax)
		{
			$f1 = ($fieldCount > $i) ? $field[$i] : $field[$fieldCount - 1];
			$v1 = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			if ($baseType == "datetime" || $baseType == "date")
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

			if ($baseType === 'bool')
			{
				$f1 = CBPHelper::getBool($f1);
				$v1 = CBPHelper::getBool($v1);
			}

			/** @var \Bitrix\Bizproc\BaseType\Base $classType */
			$classType = \Bitrix\Bizproc\BaseType\Base::class;
			if ($type)
			{
				$fieldType = $rootActivity->workflow
					->GetService('DocumentService')
					->getFieldTypeObject($rootActivity->GetDocumentType(), ['Type' => $type]);
				if ($fieldType)
				{
					$classType = $fieldType->getTypeClass();
				}
			}
			$compareResult = $classType::compareValues($f1, $v1);

			switch ($operation)
			{
				case ">":
					$result = ($compareResult === 1);
					break;
				case ">=":
					$result = ($compareResult >= 0);
					break;
				case "<":
					$result = ($compareResult === -1);
					break;
				case "<=":
					$result = ($compareResult <= 0);
					break;
				case "!=":
					$result = ($compareResult !== 0);
					break;
				default:
					$result = ($compareResult === 0);
			}

			if (!$result)
			{
				break;
			}

			$i++;
		}

		return $result;
	}

	public static function GetPropertiesDialog($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue, $arCurrentValues = null, $formName = "")
	{
		if (count($arWorkflowParameters) <= 0 && count($arWorkflowVariables) <= 0)
		{
			return null;
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = [];
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
				{
					continue;
				}

				$i = intval($i);

				if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["variable_condition_field_".$i]) <= 0)
				{
					continue;
				}

				$n = $arCurrentValues["variable_condition_field_".$i];

				$errors = [];
				$arCurrentValues["variable_condition_value_".$i] = $documentService->GetFieldInputValue(
					$documentType,
					array_key_exists($n, $arWorkflowParameters) ? $arWorkflowParameters[$n] : $arWorkflowVariables[$n],
					"variable_condition_value_".$i,
					$arCurrentValues,
					$errors
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

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$errors)
	{
		$errors = [];

		if (!array_key_exists("variable_condition_count", $arCurrentValues) || strlen($arCurrentValues["variable_condition_count"]) <= 0)
		{
			$errors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$result = [];

		$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);
		foreach ($arVariableConditionCount as $i)
		{
			if (intval($i)."!" != $i."!")
			{
				continue;
			}

			$i = intval($i);

			if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || strlen($arCurrentValues["variable_condition_field_".$i]) <= 0)
			{
				continue;
			}

			$n = $arCurrentValues["variable_condition_field_".$i];

			$errors = [];
			$arCurrentValues["variable_condition_value_".$i] = $documentService->GetFieldInputValue(
				$documentType,
				array_key_exists($n, $arWorkflowParameters) ? $arWorkflowParameters[$n] : $arWorkflowVariables[$n],
				"variable_condition_value_".$i,
				$arCurrentValues,
				$errors
			);

			$result[] = array(
				$arCurrentValues["variable_condition_field_".$i],
				htmlspecialcharsback($arCurrentValues["variable_condition_condition_".$i]),
				$arCurrentValues["variable_condition_value_".$i],
				(int) $arCurrentValues["variable_condition_joiner_".$i],
			);
		}

		if (count($result) <= 0)
		{
			$errors[] = array(
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			);
			return null;
		}

		return $result;
	}

}