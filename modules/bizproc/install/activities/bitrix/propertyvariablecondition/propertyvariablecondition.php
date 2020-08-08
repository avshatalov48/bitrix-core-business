<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Bizproc;

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

	public function collectUsages(CBPActivity $ownerActivity)
	{
		$usages = [];
		$rootActivity = $ownerActivity->GetRootActivity();
		foreach ($this->condition as $cond)
		{
			if ($rootActivity->IsPropertyExists($cond[0]))
			{
				$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Parameter, $cond[0]];
			}
			elseif ($rootActivity->IsVariableExists($cond[0]))
			{
				$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Variable, $cond[0]];
			}
		}
		return $usages;
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
		$type = is_array($property) ? $property['Type'] : $baseType;
		$condition = new Bizproc\Activity\Condition([
			'operator' => $operation,
			'value' => $rootActivity->ParseValue($value, $type),
		]);

		$fieldType = $rootActivity->workflow
			->GetService('DocumentService')
			->getFieldTypeObject($rootActivity->GetDocumentType(), ['Type' => $type]);

		if (!$fieldType)
		{
			$fieldType = $rootActivity->workflow
				->GetService('DocumentService')
				->getFieldTypeObject($rootActivity->GetDocumentType(), ['Type' => 'string']);
		}

		return $condition->checkValue($field, $fieldType, $rootActivity->GetDocumentId());
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
					if ($arCurrentValues["variable_condition_count"] <> '')
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

				if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || $arCurrentValues["variable_condition_field_".$i] == '')
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

		if (!array_key_exists("variable_condition_count", $arCurrentValues) || $arCurrentValues["variable_condition_count"] == '')
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

			if (!array_key_exists("variable_condition_field_".$i, $arCurrentValues) || $arCurrentValues["variable_condition_field_".$i] == '')
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