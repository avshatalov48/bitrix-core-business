<?php

namespace Bitrix\Bizproc\Activity\Mixins;

use Bitrix\Bizproc\Automation\Engine\ConditionGroup;
use Bitrix\Bizproc\Automation\Engine\Condition;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\Error;
use Bitrix\Bizproc\FieldType;
use Bitrix\Bizproc\Result;

trait EntityFilter
{
	abstract public function getDocumentType();

	public function getOrmFilter(ConditionGroup $conditionGroup, ?array $targetDocumentType = null): array
	{
		$filter = ['LOGIC' => 'OR'];

		$documentService = \CBPRuntime::getRuntime()->getDocumentService();
		if (is_null($targetDocumentType))
		{
			$targetDocumentType = $this->getDocumentType();
		}

		$fieldsMap = $documentService->getDocumentFields($targetDocumentType);
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

			if ($condition->getOperator() === \Bitrix\Bizproc\Activity\Operator\BetweenOperator::getCode())
			{
				$betweenFilterResult = $this->getBetweenFilter($fieldsMap[$fieldId] ?? [], $condition);
				if (!$betweenFilterResult->isSuccess())
				{
					continue;
				}

				$filter[$i][] = $betweenFilterResult->getData()['filter1'];
				$filter[$i][] = $betweenFilterResult->getData()['filter2'];
			}
			elseif ($conditionGroup->isInternalized())
			{
				$value = $condition->getValue();
			}
			else
			{
				$extractionResult = $this->extractValue($fieldsMap[$fieldId], (string)$condition->getValue());
				if ($extractionResult->isSuccess())
				{
					$value = $extractionResult->getData()['extractedValue'];
				}
				else
				{
					continue;
				}
			}

			if ($fieldsMap[$fieldId]['Type'] === FieldType::USER && $value)
			{
				$value = \CBPHelper::extractUsers($value, $targetDocumentType);
			}
			elseif ($fieldsMap[$fieldId]['Type'] === FieldType::BOOL && isset($value))
			{
				$value = \CBPHelper::getBool($value);
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

			$filter[$i][] = $this->createRowFilter($operator, $condition->getField(), $value);

			if ($joiner === ConditionGroup::JOINER_OR)
			{
				$filter[++$i] = [];
			}
		}

		return $filter;
	}

	private function getBetweenFilter(array $property, Condition $condition): Result
	{
		$value = $condition->getValue();
		$value_greater_then = (is_array($value) && isset($value[0]) ? $value[0] : $value);
		$value_less_then = (is_array($value) && isset($value[1]) ? $value[1] : '');

		$extractionResult1 = $this->extractValue($property, (string)$value_greater_then);
		if (!$extractionResult1->isSuccess())
		{
			return Result::createOk()->addErrors($extractionResult1->getErrors());
		}

		$extractionResult2 = $this->extractValue($property, (string)$value_less_then);
		if (!$extractionResult2->isSuccess())
		{
			return Result::createOk()->addErrors($extractionResult2->getErrors());
		}

		$filter1 = $this->createRowFilter('>=', $condition->getField(), $extractionResult1->getData()['extractedValue']);
		$filter2 = $this->createRowFilter('<=', $condition->getField(), $extractionResult2->getData()['extractedValue']);

		return Result::createOk(['filter1' => $filter1, 'filter2' => $filter2]);
	}

	private function createRowFilter(string $operator, string $field, $value): array
	{
		return [$operator . $field => $value];
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
			$operator = $currentValues[$prefix . 'operator'][$index];
			if (
				$operator === \Bitrix\Bizproc\Activity\Operator\BetweenOperator::getCode()
				&& isset($currentValues[$prefix . 'value'][$index], $currentValues[$prefix . 'value'][$index + 1])
			)
			{
				$currentValues[$prefix . 'value'][$index] = [
					$currentValues[$prefix . 'value'][$index],
					$currentValues[$prefix . 'value'][$index + 1],
				];

				array_splice($currentValues[$prefix . 'value'], $index + 1, 1);
			}

			$conditionGroup['items'][] = [
				// condition
				[
					'object' => $currentValues[$prefix . 'object'][$index],
					'field' => $currentValues[$prefix . 'field'][$index],
					'operator' => $operator,
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
