<?php

namespace Bitrix\Bizproc\Activity\Mixins;

use Bitrix\Bizproc\Automation\Engine\ConditionGroup;
use Bitrix\Bizproc\Automation\Engine\Condition;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\Error;
use Bitrix\Bizproc\Result;

trait EntityFilter
{
	abstract public function getDocumentType();

	public function getOrmFilter(ConditionGroup $conditionGroup): array
	{
		$filter = ['LOGIC' => 'OR'];

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		$fieldsMap = $documentService->getDocumentFields($this->getDocumentType());
		$i = 0;
		$filter[$i] = [];

		/**@var Condition $condition*/
		foreach ($conditionGroup->getItems() as [$condition, $joiner])
		{
			$fieldId = $condition->getField();
			if (!isset($fieldsMap[$fieldId]))
			{
				continue;
			}

			$extractionResult = $this->extractValue($fieldsMap[$fieldId], (string)$condition->getValue());
			if ($extractionResult->isSuccess())
			{
				$value = $extractionResult->getData()['extractedValue'];
			}
			else
			{
				continue;
			}

			switch ($condition->getOperator())
			{
				case 'empty':
					$operator = '=';
					$value = '';
					break;

				case '!empty':
					$operator = '!=';
					$value = '';
					break;

				case 'in':
					$operator = '@';
					break;

				case '!in':
					$operator = '!@';
					break;

				case 'contain':
					$operator = '%';
					break;

				case '!contain':
					$operator = '!%';
					break;

				case '>':
				case '>=':
				case '<':
				case '<=':
				case '=':
				case '!=':
					$operator = $condition->getOperator();
					break;

				default:
					$operator = '';
					break;
			}

			if (!$operator)
			{
				continue;
			}

			if ($joiner === ConditionGroup::JOINER_OR)
			{
				$filter[++$i] = [];
			}

			$filter[$i][] = [$operator . $condition->getField() => $value];
		}

		return $filter;
	}

	protected function extractValue(array $fieldProperties, $value): Result
	{
		$documentService = \CBPRuntime::getRuntime()->getDocumentService();

		$field = $documentService->getFieldTypeObject($this->getDocumentType(), $fieldProperties);

		$errors = [];
		$extractionErrors = [];
		if (!$field)
		{
			$errors[] = new Error('Can\'t create field type object');
		}
		else
		{
			if (!isset($fieldProperties['FieldName']))
			{
				$fieldProperties['FieldName'] = 'field_name';
			}

			$value = $field->extractValue(
				['Field' => $fieldProperties['FieldName']],
				[$fieldProperties['FieldName'] => $value],
				$extractionErrors,
			);
		}

		foreach ($extractionErrors as $singleError)
		{
			if (is_array($singleError))
			{
				$errors[] = new Error(
					$singleError['message'] ?? '',
					$singleError['code'] ?? '',
					$singleError['parameter'] ?? ''
				);
			}
		}

		return $errors ? Result::createOk()->addErrors($errors) : Result::createOk(['extractedValue' => $value]);
	}

	public static function extractFilterFromProperties(PropertiesDialog $dialog, array $fieldsMap): Result
	{
		$currentValues = $dialog->getCurrentValues();
		$prefix = $fieldsMap['DynamicFilterFields']['FieldName'] . '_';

		$conditionGroup = ['items' => []];

		foreach ($currentValues[$prefix . 'field'] ?? [] as $index => $fieldName)
		{
			$conditionGroup['items'][] = [
				// condition
				[
					'object' => $currentValues[$prefix . 'object'][$index],
					'field' => $currentValues[$prefix . 'field'][$index],
					'operator' => $currentValues[$prefix . 'operator'][$index],
					'value' => $currentValues[$prefix . 'value'][$index],
				],
				// joiner
				$currentValues[$prefix . 'joiner'][$index],
			];
		}

		$result = new Result();
		$result->setData($conditionGroup);

		return $result;
	}
}