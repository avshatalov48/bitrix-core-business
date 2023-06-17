<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc;

class CBPPropertyVariableCondition extends CBPActivityCondition
{
	public $condition = null;

	public function __construct($condition)
	{
		$this->condition = $condition;
	}

	public function evaluate(CBPActivity $ownerActivity)
	{
		if (!$this->isConditionGroupExist())
		{
			return true;
		}

		$this->conditionGroupToArray();

		$rootActivity = $ownerActivity->getRootActivity();

		$items = [];
		foreach ($this->condition as $cond)
		{
			$valueToCheck = null;
			$property = null;
			$baseType = null;

			if ($rootActivity->isPropertyExists($cond[0]))
			{
				$valueToCheck = $rootActivity->{$cond[0]};
				$property = $rootActivity->getTemplatePropertyType($cond[0]);
				$baseType = $rootActivity->getPropertyBaseType($cond[0]);
			}
			elseif ($rootActivity->isVariableExists($cond[0]))
			{
				$valueToCheck = $rootActivity->getVariable($cond[0]);
				$property = $rootActivity->getVariableType($cond[0]);
				$baseType =  $rootActivity->getVariableBaseType($cond[0]);
			}

			$type = is_array($property) ? $property['Type'] : $baseType;

			$items[] = [
				'joiner' => $this->getJoiner($cond),
				'operator' => $type ? $cond[1] : 'empty',
				'valueToCheck' => $valueToCheck,
				'fieldType' => $this->getFieldTypeObject($rootActivity, ['Type' => $type ?? 'string']),
				'value' => $type ? $rootActivity->parseValue($cond[2], $type) : null,
				'fieldName' => ($property && $property['Name']) ?? $cond[0],
			];
		}

		$conditionGroup = new Bizproc\Activity\ConditionGroup([
			'items' => $items,
			'parameterDocumentId' => $rootActivity->getDocumentId()
		]);

		return $conditionGroup->evaluate();
	}

	public function collectUsages(CBPActivity $ownerActivity)
	{
		$usages = [];
		$rootActivity = $ownerActivity->GetRootActivity();
		foreach ($this->condition as $cond)
		{
			if ($rootActivity->isPropertyExists($cond[0]))
			{
				$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Parameter, $cond[0]];
			}
			elseif ($rootActivity->IsVariableExists($cond[0]))
			{
				$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Variable, $cond[0]];
			}
			else
			{
				// don't know what type, but use broken link
				$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Variable, $cond[0]];
			}

			if (is_string($cond[2]))
			{
				$parsed = $ownerActivity::parseExpression($cond[2]);
				if ($parsed)
				{
					$usages[] = \Bitrix\Bizproc\Workflow\Template\SourceType::getObjectSourceType(
						$parsed['object'],
						$parsed['field']
					);
				}
			}
		}

		return $usages;
	}

	public static function GetPropertiesDialog(
		$documentType,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$defaultValue,
		$arCurrentValues = null,
		$formName = ""
	)
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
					if (!isset($arCurrentValues["variable_condition_count"]))
					{
						$arCurrentValues["variable_condition_count"] = '';
					}
					if (!CBPHelper::isEmptyValue($arCurrentValues["variable_condition_count"]))
					{
						$arCurrentValues["variable_condition_count"] .= ",";
					}
					$arCurrentValues["variable_condition_count"] .= $i;

					$arCurrentValues["variable_condition_field_" . $i] = $value[0];
					$arCurrentValues["variable_condition_condition_" . $i] = $value[1];
					$arCurrentValues["variable_condition_value_" . $i] = $value[2];
					$arCurrentValues["variable_condition_joiner_" . $i] = $value[3];

					$i++;
				}
			}
		}
		else
		{
			$arVariableConditionCount = explode(",", $arCurrentValues["variable_condition_count"]);
			foreach ($arVariableConditionCount as $i)
			{
				if (!is_numeric($i))
				{
					continue;
				}
				$i = (int)$i;

				$fieldId = $arCurrentValues['variable_condition_field_' . $i] ?? null;
				if (CBPHelper::isEmptyValue($fieldId))
				{
					continue;
				}

				$operator = $arCurrentValues['field_condition_condition_' . $i] ?? '=';
				$property = [];
				if (array_key_exists($fieldId, $arWorkflowParameters))
				{
					$property = $arWorkflowParameters[$fieldId];
				}
				elseif (array_key_exists($fieldId, $arWorkflowVariables))
				{
					$property = $arWorkflowVariables[$fieldId];
				}

				$value =
					static::getConditionFieldInputValue(
						(string)$operator,
						$documentType,
						$property,
						"variable_condition_value_" . $i,
						$arCurrentValues
					)
						->getData()['value']
				;

				$arCurrentValues["variable_condition_value_" . $i] = $value ?? '';
			}
		}

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"arProperties" => $arWorkflowParameters,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				'documentType' => $documentType,
			]
		);
	}

	public static function GetPropertiesDialogValues(
		$documentType,
		$arWorkflowTemplate,
		$arWorkflowParameters,
		$arWorkflowVariables,
		$arCurrentValues,
		&$errors
	)
	{
		$errors = [];

		$variableConditionCount = $arCurrentValues['variable_condition_count'] ?? null;
		if (CBPHelper::isEmptyValue($variableConditionCount))
		{
			$errors[] = [
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			];

			return null;
		}

		$result = [];

		$arVariableConditionCount = explode(",", (string)$variableConditionCount);
		foreach ($arVariableConditionCount as $i)
		{
			if (!is_numeric($i))
			{
				continue;
			}
			$i = (int)$i;

			$fieldId = $arCurrentValues['variable_condition_field_' . $i] ?? null;
			if (CBPHelper::isEmptyValue($fieldId))
			{
				continue;
			}

			$property = [];
			if (array_key_exists($fieldId, $arWorkflowParameters))
			{
				$property = $arWorkflowParameters[$fieldId];
			}
			elseif (array_key_exists($fieldId, $arWorkflowVariables))
			{
				$property = $arWorkflowVariables[$fieldId];
			}

			$operator = htmlspecialcharsback($arCurrentValues["variable_condition_condition_" . $i]);
			$inputResult = static::getConditionFieldInputValue(
				(string)$operator,
				$documentType,
				$property,
				"variable_condition_value_" . $i,
				$arCurrentValues,
			);

			if (!$inputResult->isSuccess())
			{
				foreach ($inputResult->getErrors() as $error)
				{
					$errors[] = [
						'message' => $error->getMessage(),
						'code' => $error->getCode(),
					];
				}
			}
			$joiner = (int)($arCurrentValues["variable_condition_joiner_" . $i] ?? 0);

			$result[] = [$fieldId, $operator, $inputResult->getData()['value'] ?? '', $joiner];
		}

		if (count($result) <= 0)
		{
			$errors[] = [
				"code" => "",
				"message" => GetMessage("BPPVC_EMPTY_CONDITION"),
			];

			return null;
		}

		return $result;
	}

}