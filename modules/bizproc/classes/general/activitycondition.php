<?php

abstract class CBPActivityCondition
{
	use \Bitrix\Bizproc\Debugger\Mixins\WriterDebugTrack;

	public const CONDITION_JOINER_AND = 0;
	public const CONDITION_JOINER_OR = 1;

	public $condition = null;

	abstract public function evaluate(CBPActivity $ownerActivity);

	public static function createInstance($code, $data)
	{
		if (preg_match("#[^a-zA-Z0-9_]#", $code))
		{
			throw new Exception("Activity '" . $code . "' is not valid");
		}

		$classname = 'CBP' . $code;

		return new $classname($data);
	}

	public function collectUsages(CBPActivity $ownerActivity)
	{
		return [];
	}

	public static function validateProperties($value = null, CBPWorkflowTemplateUser $user = null)
	{
		return [];
	}

	public static function callStaticMethod($code, $method, $arParameters = array())
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->IncludeActivityFile($code);

		if (preg_match("#[^a-zA-Z0-9_]#", $code))
		{
			throw new Exception("Activity '" . $code . "' is not valid");
		}

		$classname = 'CBP'.$code;

		return call_user_func_array(array($classname, $method), $arParameters);
	}

	protected static function getConditionFieldInputValue(string $operator, $parameterDocumentType, $property, $fieldName, $request): Bitrix\Main\Result
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		$result = new Bitrix\Main\Result();
		$isBetweenOperator = $operator === \Bitrix\Bizproc\Activity\Operator\BetweenOperator::getCode();

		$errors = [];
		$value =
			$isBetweenOperator
				? []
				: $documentService->getFieldInputValue($parameterDocumentType, $property, $fieldName, $request, $errors)
		;

		if ($isBetweenOperator)
		{
			$property['Multiple'] = false;

			$value1 = $documentService->getFieldInputValue(
				$parameterDocumentType,
				$property,
				$fieldName . '_greater_then',
				$request,
				$errors
			);

			$value2 = $documentService->getFieldInputValue(
				$parameterDocumentType,
				$property,
				$fieldName . '_less_then',
				$request,
				$errors
			);

			$value = [$value1 ?? '', $value2 ?? ''];
		}

		if (!empty($errors))
		{
			foreach ($errors as $error)
			{
				$result->addError(new \Bitrix\Main\Error((string)$error['message'], (string)$error['code']));
			}
		}

		$result->setData(['value' => $value]);

		return $result;
	}

	protected function getFieldTypeObject(CBPActivity $rootActivity, $property): \Bitrix\Bizproc\FieldType
	{
		$documentService = $rootActivity->workflow->getRuntime()->getDocumentService();
		$documentType = $rootActivity->getDocumentType();

		if (!is_array($property))
		{
			return $documentService->getFieldTypeObject($documentType, ['Type' => 'string']);
		}

		$fieldType = $documentService->getFieldTypeObject($documentType, $property);
		if (!$fieldType)
		{
			$fieldType = $documentService->getFieldTypeObject($documentType, ['Type' => 'string']);
		}

		return $fieldType;
	}

	protected function isConditionGroupExist(): bool
	{
		if ($this->condition == null || !is_array($this->condition) || count($this->condition) <= 0)
		{
			return false;
		}

		return true;
	}

	protected function conditionGroupToArray()
	{
		if (!isset($this->condition[0]) || !is_array($this->condition[0]))
		{
			$this->condition = [$this->condition];
		}
	}

	protected function getJoiner($condition): int
	{
		return empty($condition[3]) ? static::CONDITION_JOINER_AND : static::CONDITION_JOINER_OR;
	}

	protected function writeAutomationConditionLog(array $conditions, array $results, bool $result, CBPActivity $ownerActivity): array
	{
		$toLog = [];
		foreach ($conditions as $index => $condition)
		{
			/** @var \Bitrix\Bizproc\FieldType $fieldType */
			$fieldType = $condition['fieldType'];

			if (in_array($condition['operator'], ['!empty', 'empty']))
			{
				$formattedValue = '';
			}
			else
			{
				$multiple = $fieldType->isMultiple();
				$fieldType->setMultiple(true);
				$formattedValue = $fieldType->formatValue($condition['value']);
				$fieldType->setMultiple($multiple);
			}

			$toLog[] = [
				'condition' => [
					'field' => $condition['fieldName'],
					'operator' => $condition['operator'],
					'value' => $formattedValue
				],
				'joiner' => $condition['joiner'] === static::CONDITION_JOINER_OR ? 'OR' : 'AND',
				'fieldValue' => $condition['valueToCheck'] ? $fieldType->formatValue($condition['valueToCheck']) : null,
				'result' => $results[$index] ? 'Y' : 'N',
			];
		}

		$toLog = array_merge(['result' => $result ? 'Y' : 'N'], $toLog);
		$title = $ownerActivity->isPropertyExists('Title') ? $ownerActivity->Title : '';

		$id = $this->writeDebugTrack(
			$ownerActivity->getWorkflowInstanceId(),
			$ownerActivity->getName(),
			$ownerActivity->executionStatus,
			$ownerActivity->executionResult,
			$title ?? '',
			$toLog,
			CBPTrackingType::DebugAutomation
		);

		return [$toLog, $id];
	}
}
