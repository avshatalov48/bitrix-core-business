<?php
namespace Bitrix\Bizproc\Automation\Trigger;

use Bitrix\Bizproc\Automation\Engine\ConditionGroup;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Main;

class BaseTrigger
{
	protected $target;

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

	public function checkApplyRules(array $trigger)
	{
		$conditionRules = is_array($trigger['APPLY_RULES']) && isset($trigger['APPLY_RULES']['Condition'])
			? $trigger['APPLY_RULES']['Condition'] : null;

		if ($conditionRules)
		{
			$conditionGroup = new ConditionGroup($conditionRules);
			return $conditionGroup->evaluate($this->getTarget());
		}

		return true;
	}

	public static function toArray()
	{
		return [
			'NAME' => static::getName(),
			'CODE' => static::getCode()
		];
	}
}