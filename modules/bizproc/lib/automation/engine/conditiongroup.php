<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Automation\Helper;
use Bitrix\Main\NotSupportedException;

Loc::loadMessages(__FILE__);

class ConditionGroup
{
	const TYPE_FIELD = 'field';
	const TYPE_MIXED = 'mixed';

	const JOINER_AND = 'AND';// 0
	const JOINER_OR = 'OR';// 1

	private $type;
	private $items = [];
	private array $activityNames = [];
	protected array $evaluateResults = [];

	public function __construct(array $params = null)
	{
		$this->setType(static::TYPE_FIELD);
		if ($params)
		{
			if (isset($params['type']))
			{
				$this->setType($params['type']);
			}
			if (isset($params['items']) && is_array($params['items']))
			{
				foreach ($params['items'] as [$item, $joiner])
				{
					if (!empty($item['field']))
					{
						$condition = new Condition($item);
						$this->addItem($condition, $joiner);
					}
				}
			}
		}
	}

	/**
	 * @param BaseTarget $target Automation target.
	 * @return bool
	 */
	public function evaluate(BaseTarget $target)
	{
		$documentType = $target->getDocumentType();
		$documentId = $documentType;
		$documentId[2] = $target->getDocumentId();

		return $this->evaluateByDocument($documentType, $documentId);
	}

	/**
	 * @param array $documentType
	 * @param array $documentId
	 * @param array|null $document
	 * @return bool
	 */
	public function evaluateByDocument(array $documentType, array $documentId, array $document = null): bool
	{
		if (empty($this->items))
		{
			return true;
		}

		if ($this->getType() === static::TYPE_MIXED)
		{
			throw new NotSupportedException('Mixed conditions can`t be evaluated by document only');
		}

		$documentService = \CBPRuntime::getRuntime(true)->getDocumentService();

		if ($document === null)
		{
			$document = $documentService->getDocument($documentId, $documentType);
		}

		$result = [0 => true];
		$i = 0;
		$joiner = static::JOINER_AND;

		$this->evaluateResults = [];

		foreach ($this->items as $item)
		{
			/** @var Condition $condition */
			$condition = $item[0];
			$conditionField = $condition->getField();

			$conditionResult = true;

			$fld = $document[$conditionField] ?? null;
			$fieldType = $this->getFieldTypeObject($documentService, $documentType, $conditionField);

			if (!$condition->checkValue($fld, $fieldType, $documentId))
			{
				$conditionResult = false;
			}

			if ($joiner == static::JOINER_OR)
			{
				++$i;
				$result[$i] = $conditionResult;
			}
			elseif (!$conditionResult)
			{
				$result[$i] = false;
			}

			$this->evaluateResults[] = [
				'condition' => $condition->toArray(),
				'joiner' => $joiner,
				'fieldValue' => $fld ? $fieldType->formatValue($fld) : null,
				'result' => $conditionResult ? 'Y' : 'N',
			];

			$joiner = ($item[1] === static::JOINER_OR) ? static::JOINER_OR : static::JOINER_AND;
		}

		return (count(array_filter($result)) > 0);
	}

	/**
	 * @param string $type Type of condition.
	 * @return ConditionGroup This instance.
	 */
	public function setType($type)
	{
		if ($type === static::TYPE_FIELD || $type === static::TYPE_MIXED)
		{
			$this->type = $type;
		}
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param Condition $condition Condition instance.
	 * @param string $joiner Condition joiner.
	 * @return $this This instance.
	 */
	public function addItem(Condition $condition, $joiner = self::JOINER_AND)
	{
		$this->items[] = [$condition, $joiner];
		return $this;
	}

	/**
	 * @return array Condition items.
	 */
	public function getItems()
	{
		return $this->items;
	}

	public function setActivityNames($activity)
	{
		$this->activityNames = [
			'Activity' => $activity['Name'],
			'Branch1' => $activity['Children'][0]['Name'],
			'Branch2' => $activity['Children'][1]['Name'],
		];
	}

	public function getActivityNames(): array
	{
		if (isset($this->activityNames))
		{
			return $this->activityNames;
		}

		return [];
	}

	/**
	 * @return array Array presentation of condition group.
	 */
	public function toArray()
	{
		$itemsArray = [];

		/** @var Condition $condition */
		foreach ($this->getItems() as [$condition, $joiner])
		{
			$itemsArray[] = [$condition->toArray(), $joiner];
		}

		return ['type' => $this->getType(), 'items' => $itemsArray, 'activityNames' => $this->getActivityNames()];
	}

	/**
	 * @param array $childActivity Child activity array.
	 * @param array $documentType
	 * @param Template $template
	 * @return array New activity array.
	 */
	public function createBizprocActivity(array $childActivity, array $documentType, Template $template)
	{
		$mixedCondition = [];
		$bizprocJoiner = 0;

		$documentService = \CBPRuntime::GetRuntime()->getDocumentService();

		/** @var Condition $condition */
		foreach ($this->getItems() as [$condition, $joiner])
		{
			$object = $condition->getObject();
			$field = $condition->getField();
			$value = $condition->getValue();
			$property = $template->getProperty($object, $field);

			$operator = $condition->getOperator();
			$isOperatorWithValue = !in_array(
				$operator,
				[Bizproc\Activity\Operator\EmptyOperator::getCode(), Bizproc\Activity\Operator\NotEmptyOperator::getCode()],
				true
			);
			if ($property && $isOperatorWithValue)
			{
				$currentValues = ['field' => $value];
				$errors = [];

				$isBetweenOperator = $operator === \Bitrix\Bizproc\Activity\Operator\BetweenOperator::getCode();
				$valueInternal =
					$isBetweenOperator
						? []
						: $documentService->getFieldInputValue(
							$documentType,
							$property,
							'field',
							$currentValues,
							$errors
					)
				;
				if ($isBetweenOperator)
				{
					$currentValues['field_greater_then'] = is_array($value) && isset($value[0]) ? $value[0] : $value;
					$currentValues['field_less_then'] = is_array($value) && isset($value[1]) ? $value[1] : '';

					$property['Multiple'] = false;
					$valueInternal1 = $documentService->getFieldInputValue(
						$documentType,
						$property,
						'field_greater_then',
						$currentValues,
						$errors
					);
					$valueInternal2 = $documentService->getFieldInputValue(
						$documentType,
						$property,
						'field_less_then',
						$currentValues,
						$errors
					);

					$valueInternal = [$valueInternal1 ?? '', $valueInternal2 ?? ''];
				}

				if (!$errors)
				{
					$value = $valueInternal;
				}
			}

			$mixedCondition[] = [
				'object' => $object,
				'field' => $field,
				'operator' => $condition->getOperator(),
				'value' => self::unConvertExpressions($value, $documentType),
				'joiner' => $bizprocJoiner,
			];
			$bizprocJoiner = ($joiner === static::JOINER_OR) ? 1 : 0;
		}

		$title = Loc::getMessage('BIZPROC_AUTOMATION_CONDITION_TITLE');
		$activity = [
			'Type' => 'IfElseActivity',
			'Name' => Robot::generateName(),
			'Properties' => ['Title' => $title],
			'Children' => [
				[
					'Type' => 'IfElseBranchActivity',
					'Name' => Robot::generateName(),
					'Properties' => [
						'Title' => $title,
						'mixedcondition' => $mixedCondition
					],
					'Children' => [$childActivity]
				],
				[
					'Type' => 'IfElseBranchActivity',
					'Name' => Robot::generateName(),
					'Properties' => [
						'Title' => $title,
						'truecondition' => '1',
					],
					'Children' => []
				]
			]
		];

		return $activity;
	}

	/**
	 * @param array &$activity Target activity array.
	 * @param array $documentType
	 * @param Template $template
	 * @return false|ConditionGroup Instance of false.
	 */
	public static function convertBizprocActivity(array &$activity, array $documentType, Template $template)
	{
		$conditionGroup = false;

		if (
			count($activity['Children']) === 2
			&& $activity['Children'][0]['Type'] === 'IfElseBranchActivity'
			&& $activity['Children'][1]['Type'] === 'IfElseBranchActivity'
			&& (
				!empty($activity['Children'][0]['Properties']['fieldcondition'])
				||
				!empty($activity['Children'][0]['Properties']['mixedcondition'])
			)
			&& !empty($activity['Children'][1]['Properties']['truecondition'])
			&& count($activity['Children'][0]['Children']) === 1
		)
		{
			$conditionGroup = new static();
			$conditionGroup->setType(static::TYPE_MIXED);
			$conditionGroup->setActivityNames($activity);

			$isMixed = isset($activity['Children'][0]['Properties']['mixedcondition']);
			$bizprocConditions = $activity['Children'][0]['Properties'][$isMixed?'mixedcondition':'fieldcondition'];

			foreach ($bizprocConditions as $index => $condition)
			{
				if (!$isMixed)
				{
					$condition = self::convertDocumentCondition($condition);
				}

				$property = $template->getProperty($condition['object'], $condition['field']);
				if ($property && $property['Type'] === 'user')
				{
					$condition['value'] = \CBPHelper::UsersArrayToString(
						$condition['value'],
						null,
						$documentType
					);
				}

				$conditionItem = new Condition(array(
					'object' => $condition['object'],
					'field' => $condition['field'],
					'operator' => $condition['operator'],
					'value' => self::convertExpressions($condition['value'], $documentType),
				));

				$nextCondition = isset($bizprocConditions[$index + 1]) ? $bizprocConditions[$index + 1] : null;
				$joiner = ($nextCondition && (!empty($nextCondition[3]) || !empty($nextCondition['joiner'])))
					? static::JOINER_OR : static::JOINER_AND;

				$conditionGroup->addItem($conditionItem, $joiner);
			}

			$activity = $activity['Children'][0]['Children'][0];
		}

		return $conditionGroup;
	}

	private static function convertDocumentCondition(array $condition): array
	{
		return [
			'object' => 'Document',
			'field' => $condition[0],
			'operator' => $condition[1],
			'value' => $condition[2],
			'joiner' => $condition[3],
		];
	}

	/**
	 * Convert values to internal format.
	 * @param array $documentType
	 * @return $this
	 */
	public function internalizeValues(array $documentType): self
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->GetDocumentFields($documentType);

		/** @var Condition $condition */
		foreach ($this->getItems() as [$condition, $joiner])
		{
			$field = $condition->getField();
			$value = $condition->getValue();
			$property = isset($documentFields[$field]) ? $documentFields[$field] : null;
			if ($property && !in_array($condition->getOperator(), ['empty', '!empty']))
			{
				$value = self::unConvertExpressions($value, $documentType);
				$valueInternal = $documentService->GetFieldInputValue(
					$documentType,
					$property,
					'field',
					['field' => $value],
					$errors
				);

				if (!$errors)
				{
					$condition->setValue($valueInternal);
				}
			}
		}

		return $this;
	}

	/**
	 * Convert value to external format.
	 * @param array $documentType
	 * @return $this
	 */
	public function externalizeValues(array $documentType): self
	{
		$documentService = \CBPRuntime::GetRuntime(true)->getDocumentService();
		$documentFields = $documentService->GetDocumentFields($documentType);

		/** @var Condition $condition */
		foreach ($this->getItems() as [$condition, $joiner])
		{
			$field = $condition->getField();
			$value = $condition->getValue();
			$property = isset($documentFields[$field]) ? $documentFields[$field] : null;
			if ($property && !in_array($condition->getOperator(), ['empty', '!empty']))
			{
				$value = self::convertExpressions($value, $documentType);
				if ($property['Type'] === 'user')
				{
					$value = \CBPHelper::UsersArrayToString(
						$value,
						null,
						$documentType
					);
				}
				$condition->setValue($value);
			}
		}

		return $this;
	}

	private static function convertExpressions($value, array $documentType)
	{
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = self::convertExpressions($v, $documentType);
			}
		}
		else
		{
			$value = Helper::convertExpressions($value, $documentType);
		}
		return $value;
	}

	private static function unConvertExpressions($value, array $documentType)
	{
		if (is_array($value))
		{
			foreach ($value as $k => $v)
			{
				$value[$k] = self::unConvertExpressions($v, $documentType);
			}
		}
		else
		{
			$value = Helper::unConvertExpressions($value, $documentType);
		}
		return $value;
	}

	private function getFieldTypeObject(\CBPDocumentService $documentService, array $documentType, $conditionField): ?\Bitrix\Bizproc\FieldType
	{
		$documentFields = $documentService->getDocumentFields($documentType);

		$fieldType = null;

		if (isset($documentFields[$conditionField]))
		{
			$fieldType = $documentService->getFieldTypeObject($documentType, $documentFields[$conditionField]);
		}

		if (!$fieldType)
		{
			$fieldType = $documentService->getFieldTypeObject($documentType, ['Type' => 'string']);
		}

		return $fieldType;
	}

	public function getEvaluateResults(): array
	{
		return $this->evaluateResults;
	}
}