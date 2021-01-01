<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Bizproc;

class CBPMixedCondition
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
			$this->condition = [$this->condition];
		}

		$rootActivity = $ownerActivity->GetRootActivity();

		$result = [0 => true];
		$i = 0;
		foreach ($this->condition as $cond)
		{
			$joiner = empty($cond['joiner'])? static::CONDITION_JOINER_AND : static::CONDITION_JOINER_OR;

			[$property, $value] = self::getRuntimeProperty($cond['object'], $cond['field'], $rootActivity);

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

	//TODO: move to general Activity API
	private function getRuntimeProperty($object, $field, CBPActivity $ownerActivity): array
	{
		$rootActivity = $ownerActivity->GetRootActivity();
		$documentType = $rootActivity->GetDocumentType();
		$documentId = $rootActivity->GetDocumentId();
		$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->GetDocumentFields($documentType);

		$result = null;
		$property = null;

		switch ($object)
		{
			case 'Template':
				$result = $rootActivity->__get($field);
				$property = $rootActivity->getTemplatePropertyType($field);
				break;
			case 'Variable':
				$result = $rootActivity->GetVariable($field);
				$property = $rootActivity->getVariableType($field);
				break;
			case 'Constant':
				$result = $rootActivity->GetConstant($field);
				$property = $rootActivity->GetConstantType($field);
				break;
			case 'GlobalConst':
				$result = Bizproc\Workflow\Type\GlobalConst::getValue($field);
				$property = Bizproc\Workflow\Type\GlobalConst::getById($field);
				break;
			case 'Document':
				$property = $documentFields[$field];
				$result = $documentService->getFieldValue($documentId, $field, $documentType);
				break;
			default:
				$activity = $rootActivity->workflow->GetActivityByName($object);
				if ($activity)
				{
					$result = $activity->__get($field);
					$property = $activity->getPropertyType($field);
				}
		}

		if (!$property)
		{
			$property = ['Type' => 'string'];
		}

		return [$property, $result];
	}

	public function collectUsages(CBPActivity $ownerActivity)
	{
		$usages = [];
		foreach ($this->condition as $cond)
		{
			switch ($cond['object'])
			{
				case 'Template':
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Parameter, $cond['field']];
					break;
				case 'Variable':
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Variable, $cond['field']];
					break;
				case 'Constant':
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Constant, $cond['field']];
					break;
				case 'GlobalConst':
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::GlobalConstant, $cond['field']];
					break;
				case 'Document':
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::DocumentField, $cond['field']];
					break;
				default:
					$usages[] = [\Bitrix\Bizproc\Workflow\Template\SourceType::Activity, $cond['object']];
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
			$defaultValue = static::GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, $errors, $arWorkflowConstants);
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

		$javascriptFunctions = $documentService->GetJSFunctionsForFields($documentType, "objFieldsPVC", $arWorkflowParameters + $arWorkflowVariables, $arFieldTypes);

		return $runtime->ExecuteResourceFile(
			__FILE__,
			"properties_dialog.php",
			array(
				"arCurrentValues" => $arCurrentValues,
				"documentService" => $documentService,
				"documentType" => $documentType,
				"arProperties" => $arWorkflowParameters,
				"arVariables" => $arWorkflowVariables,
				"formName" => $formName,
				"arFieldTypes" => $arFieldTypes,
				"javascriptFunctions" => $javascriptFunctions,
			)
		);
	}

	public static function GetPropertiesDialogValues($documentType, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, &$errors, $arWorkflowConstants = null)
	{
		$errors = [];

		if (!array_key_exists("mixed_condition", $arCurrentValues) || !is_array($arCurrentValues["mixed_condition"]))
		{
			$errors[] = array(
				"code" => "",
				"message" => GetMessage("BPMC_EMPTY_CONDITION"),
			);
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
			$errors[] = array(
				"code" => "",
				"message" => GetMessage("BPMC_EMPTY_CONDITION"),
			);
			return null;
		}

		return $result;
	}

	private static function getDialogProperty($object, $field, $documentType, $template, $parameters, $variables, $constants): ?array
	{
		switch ($object)
		{
			case 'Template':
				return $parameters[$field] ?? null;
				break;
			case 'Variable':
				return $variables[$field]?? null;
				break;
			case 'Constant':
				if (is_array($constants))
				{
					return $constants[$field] ?? null;
				}
				break;
			case 'GlobalConst':
				return Bizproc\Workflow\Type\GlobalConst::getById($field);
				break;
			case 'Document':
				static $fields;
				if (!$fields)
				{
					$documentService = CBPRuntime::GetRuntime(true)->getDocumentService();
					$fields = $documentService->GetDocumentFields($documentType);
				}

				return $fields[$field] ?? null;
				break;
			default:
				return self::findActivityProperty($object, $field, $template);
				break;
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