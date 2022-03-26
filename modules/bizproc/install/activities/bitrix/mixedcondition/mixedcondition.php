<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc;

class CBPMixedCondition extends CBPActivityCondition
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
			$this->condition = [$this->condition];
		}

		$rootActivity = $ownerActivity->GetRootActivity();

		$result = [0 => true];
		$i = 0;
		foreach ($this->condition as $cond)
		{
			$joiner = empty($cond['joiner'])? static::CONDITION_JOINER_AND : static::CONDITION_JOINER_OR;

			[$property, $value] = $ownerActivity->getRuntimeProperty($cond['object'], $cond['field'], $rootActivity);

			if ($property)
			{
				$r = $this->checkCondition(
					$value,
					$cond['operator'],
					$cond['value'],
					$rootActivity,
					$property
				);
			}
			else
			{
				$r = ($cond['operator'] === 'empty');
			}

			if ($joiner == static::CONDITION_JOINER_OR)
			{
				++$i;
				$result[$i] = $r;
			}
			elseif (!$r)
			{
				$result[$i] = false;
			}
		}
		$result = array_filter($result);
		return sizeof($result) > 0 ? true : false;
	}

	public function collectUsages(CBPActivity $ownerActivity)
	{
		$usages = [];
		foreach ($this->condition as $cond)
		{
			$usages[] = Bizproc\Workflow\Template\SourceType::getObjectSourceType($cond['object'], $cond['field']);
			if (is_string($cond['value']))
			{
				$parsed = $ownerActivity::parseExpression($cond['value']);
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

	/**
	 * @param $field
	 * @param $operation
	 * @param $value
	 * @param CBPActivity $rootActivity
	 * @param array $property
	 * @return bool
	 */
	private function checkCondition($field, $operation, $value, CBPActivity $rootActivity, array $property): bool
	{
		$condition = new Bizproc\Activity\Condition([
			'operator' => $operation,
			'value' => $rootActivity->ParseValue($value, $property['Type']),
		]);

		$fieldType = $rootActivity->workflow
			->GetService('DocumentService')
			->getFieldTypeObject($rootActivity->GetDocumentType(), $property);

		if (!$fieldType)
		{
			$fieldType = $rootActivity->workflow
				->GetService('DocumentService')
				->getFieldTypeObject($rootActivity->GetDocumentType(), ['Type' => 'string']);
		}

		return $condition->checkValue($field, $fieldType, $rootActivity->GetDocumentId());
	}

	public static function GetPropertiesDialog(
		$documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $defaultValue,
		$arCurrentValues = null, $formName = "", $popupWindow = null, $currentSiteId = null, $arWorkflowConstants = null)
	{
		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arFieldTypes = $documentService->GetDocumentFieldTypes($documentType);

		if (is_array($arCurrentValues))
		{
			$defaultValue = static::GetPropertiesDialogValues(
				$documentType,
				$arWorkflowTemplate,
				$arWorkflowParameters,
				$arWorkflowVariables,
				$arCurrentValues,
				$errors,
				$arWorkflowConstants
			);
		}

		$arCurrentValues = ['conditions' => []];
		if (is_array($defaultValue))
		{
			foreach ($defaultValue as $cond)
			{
				$property = static::getDialogProperty(
					$cond['object'], $cond['field'], $documentType,
					$arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arWorkflowConstants
				);
				if ($property)
				{
					$cond['__property__'] = $property;
					$arCurrentValues['conditions'][] = $cond;
				}
			}
		}
		if (!$arCurrentValues['conditions'])
		{
			$arCurrentValues['conditions'][] = ['operator' => '!empty'];
		}

		$javascriptFunctions = $documentService->GetJSFunctionsForFields(
			$documentType,
			"objFieldsPVC",
			$arWorkflowParameters + $arWorkflowVariables,
			$arFieldTypes
		);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			[
				"arCurrentValues" => $arCurrentValues,
				"documentService" => $documentService,
				"documentType" => $documentType,
				"arProperties" => $arWorkflowParameters,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
			]
		);
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$errors, $arWorkflowConstants = null)
	{
		$errors = [];

		if (
			!array_key_exists("mixed_condition", $arCurrentValues)
			|| !is_array($arCurrentValues["mixed_condition"])
		)
		{
			$errors[] = [
				"code" => "",
				"message" => GetMessage("BPMC_EMPTY_CONDITION"),
			];

			return null;
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$result = [];

		foreach ($arCurrentValues['mixed_condition'] as $index => $condition)
		{
			$property = static::getDialogProperty(
				$condition['object'],
				$condition['field'],
				$documentType,
				$arWorkflowTemplate,
				$arWorkflowParameters,
				$arWorkflowVariables,
				$arWorkflowConstants
			);

			if (!$property)
			{
				continue;
			}

			$errors = [];
			$value = $documentService->GetFieldInputValue(
				$documentType,
				$property,
				"mixed_condition_value_".$index,
				$arCurrentValues,
				$errors
			);

			$result[] = [
				'object' => $condition['object'],
				'field' => $condition['field'],
				'operator' => $condition['operator'],
				'value' => $value,
				'joiner' => (int)$condition['joiner'],
			];
		}

		if (count($result) <= 0)
		{
			$errors[] = [
				"code" => "",
				"message" => GetMessage("BPMC_EMPTY_CONDITION"),
			];

			return null;
		}

		return $result;
	}

	private static function getDialogProperty($object, $field, $documentType, $template, $parameters, $variables, $constants): ?array
	{
		switch ($object)
		{
			case 'Template':
			case 'Parameter':
				return $parameters[$field] ?? null;
			case 'Variable':
				return $variables[$field]?? null;
			case 'Constant':
				if (is_array($constants))
				{
					return $constants[$field] ?? null;
				}
				break;
			case 'GlobalConst':
				return Bizproc\Workflow\Type\GlobalConst::getById($field);
			case 'GlobalVar':
				return Bizproc\Workflow\Type\GlobalVar::getById($field);
			case 'Document':
				static $fields;
				if (!$fields)
				{
					$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
					$fields = $documentService->GetDocumentFields($documentType);
				}

				return $fields[$field] ?? null;
			default:
				return self::findActivityProperty($object, $field, $template);
		}

		return null;
	}

	private static function findActivityProperty($object, $field, array $template): ?array
	{
		$activity = self::findTemplateActivity($template, $object);
		if (!$activity)
		{
			return null;
		}

		$props = \CBPRuntime::GetRuntime(true)->getActivityReturnProperties($activity);

		return $props[$field] ?? null;
	}

	private static function findTemplateActivity(array $template, $id)
	{
		foreach ($template as $activity)
		{
			if ($activity['Name'] === $id)
			{
				return $activity;
			}
			if (is_array($activity['Children']))
			{
				$found = self::findTemplateActivity($activity['Children'], $id);
				if ($found)
				{
					return $found;
				}
			}
		}

		return null;
	}
}