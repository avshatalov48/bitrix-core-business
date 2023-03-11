<?php
namespace Bitrix\Bizproc\Automation\Trigger;

use Bitrix\Bizproc\Automation\Engine\ConditionGroup;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Main;

class BaseTrigger
{
	protected $target;
	protected $returnValues;

	/**
	 * @return string the fully qualified name of this class.
	 */
	public static function className()
	{
		return get_called_class();
	}

	public static function isEnabled()
	{
		return true;
	}

	/**
	 * @param BaseTarget $target
	 * @return $this
	 */
	public function setTarget(BaseTarget $target)
	{
		$this->target = $target;
		return $this;
	}

	/**
	 * @return BaseTarget
	 * @throws Main\InvalidOperationException
	 */
	public function getTarget()
	{
		if ($this->target === null)
		{
			throw new Main\InvalidOperationException('Target must be set by setTarget method.');
		}
		return $this->target;
	}

	/**
	 * @return string Gets the alphanumeric trigger code.
	 */
	public static function getCode()
	{
		return 'BASE';
	}

	/**
	 * @return string Gets the trigger name.
	 */
	public static function getName()
	{
		return 'Base trigger';
	}

	protected function getPotentialTriggers()
	{
		$triggers = [];

		$currentStatus = $this->getTarget()->getDocumentStatus();
		$allStatuses = array_keys($this->getTarget()->getDocumentStatusList());

		$needleKey = array_search($currentStatus, $allStatuses);

		if ($needleKey === false)
		{
			return $triggers;
		}

		$forwardStatuses = array_slice($allStatuses, $needleKey + 1);

		$code = static::getCode();
		$rows = [];
		$targetTriggers = $this->getTarget()->getTriggers($allStatuses);

		foreach ($targetTriggers as $row)
		{
			if ($row['CODE'] !== $code)
			{
				continue;
			}

			if (!in_array($row['DOCUMENT_STATUS'], $forwardStatuses))
			{
				if (
					!isset($row['APPLY_RULES']['ALLOW_BACKWARDS'])
					||
					$row['APPLY_RULES']['ALLOW_BACKWARDS'] !== 'Y'
				)
				{
					continue;
				}
			}

			$rows[$row['DOCUMENT_STATUS']][] = $row;
		}

		if ($rows)
		{
			foreach ($allStatuses as $needleStatus)
			{
				if (isset($rows[$needleStatus]))
				{
					$triggers = array_merge($triggers, $rows[$needleStatus]);
				}
			}
		}

		return $triggers;
	}

	public function checkApplyRules(array $trigger)
	{
		$conditionRules = is_array($trigger['APPLY_RULES']) && isset($trigger['APPLY_RULES']['Condition'])
			? $trigger['APPLY_RULES']['Condition'] : null;

		if ($conditionRules)
		{
			$conditionGroup = new ConditionGroup($conditionRules);
			$target = $this->getTarget();
			$result = $conditionGroup->evaluate($target);

			if ($result)
			{
				$target->setAppliedTriggerConditionResults($conditionGroup->getEvaluateResults());
			}

			return $result;
		}

		return true;
	}

	public function getReturnValues(): ?array
	{
		return $this->returnValues;
	}

	/**
	 * @param array $values
	 * @return $this
	 */
	public function setReturnValues(array $values)
	{
		$this->returnValues = $values;
		return $this;
	}

	public static function getReturnProperties(): array
	{
		return [];
	}

	public static function toArray()
	{
		return [
			'NAME' => static::getName(),
			'CODE' => static::getCode(),
			'RETURN' => static::getReturnProperties(),
			'DESCRIPTION' => static::getDescription(),
			'GROUP' => static::getGroup(),
			'SETTINGS' => static::getSettings(),
		];
	}

	public static function getDescription(): string
	{
		return '';
	}

	public static function getGroup(): array
	{
		return [];
	}

	protected static function getSettings(): ?array
	{
		$map = static::getPropertiesMap();
		if ($map)
		{
			return ['Properties' => array_values($map)];
		}

		return null;
	}

	protected static function getPropertiesMap(): array
	{
		return [];
	}
}
