<?php
namespace Bitrix\Bizproc\Automation\Engine;

class Robot
{
	/** @var array */
	protected $bizprocActivity;
	/** @var  DelayInterval */
	protected $delayInterval;
	protected $delayName;
	/** @var  ConditionGroup $condition */
	protected $condition;
	protected $executeAfterPrevious = false;

	public function __construct(array $bizprocActivity)
	{
		if (isset($bizprocActivity['Delay']))
		{
			$this->setDelayInterval(new DelayInterval($bizprocActivity['Delay']));
			unset($bizprocActivity['Delay']);
		}
		if (isset($bizprocActivity['DelayName']))
		{
			$this->setDelayName($bizprocActivity['DelayName']);
			unset($bizprocActivity['DelayName']);
		}
		if (isset($bizprocActivity['Condition']))
		{
			$this->setCondition(new ConditionGroup($bizprocActivity['Condition']));
			unset($bizprocActivity['Condition']);
		}

		if (isset($bizprocActivity['ExecuteAfterPrevious']) && (int)$bizprocActivity['ExecuteAfterPrevious'] === 1)
		{
			$this->setExecuteAfterPrevious();
		}
		unset($bizprocActivity['ExecuteAfterPrevious']);

		$this->bizprocActivity = $bizprocActivity;
	}

	/**
	 * @param DelayInterval $delayInterval Robot delay interval.
	 */
	public function setDelayInterval(DelayInterval $delayInterval)
	{
		$this->delayInterval = $delayInterval;
	}

	/**
	 * @return DelayInterval Robot delay interval.
	 */
	public function getDelayInterval()
	{
		return $this->delayInterval;
	}

	/**
	 * @param ConditionGroup $condition Robot condition.
	 */
	public function setCondition(ConditionGroup $condition)
	{
		$this->condition = $condition;
	}

	/**
	 * @return ConditionGroup Robot condition.
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * @param string $delayName Robot delay name.
	 */
	public function setDelayName($delayName)
	{
		$this->delayName = (string)$delayName;
	}

	/**
	 * @return string Robot delay name.
	 */
	public function getDelayName()
	{
		return $this->delayName;
	}

	public function setExecuteAfterPrevious()
	{
		$this->executeAfterPrevious = true;
	}

	public function isExecuteAfterPrevious()
	{
		return $this->executeAfterPrevious;
	}

	public function getProperties()
	{
		return isset($this->bizprocActivity['Properties']) && is_array($this->bizprocActivity['Properties'])
			? $this->bizprocActivity['Properties'] : array();
	}

	public function getProperty($name)
	{
		$name = (string)$name;
		$properties = $this->getProperties();
		return array_key_exists($name, $properties) ? $properties[$name] : null;
	}

	public function getTitle()
	{
		return $this->getProperty('Title');
	}

	public function getName()
	{
		return $this->bizprocActivity['Name'];
	}

	public function getType()
	{
		return $this->bizprocActivity['Type'];
	}

	public function toArray()
	{
		$activity = $this->bizprocActivity;
		unset($activity['Children']); //Robot activities has no Children
		$delayInterval = $this->getDelayInterval();
		if ($delayInterval)
		{
			$activity['Delay'] = $delayInterval->toArray();
			$activity['DelayName'] = $this->getDelayName();
		}
		if ($this->isExecuteAfterPrevious())
			$activity['ExecuteAfterPrevious'] = 1;

		$condition = $this->getCondition();
		if ($condition)
		{
			$activity['Condition'] = $condition->toArray();
		}

		return $activity;
	}

	public function getBizprocActivity()
	{
		$activity = $this->bizprocActivity;
		$activity['Children'] = array();
		return $activity;
	}

	public static function generateName()
	{
		return 'A'.mt_rand(10000, 99999)
		.'_'.mt_rand(10000, 99999)
		.'_'.mt_rand(10000, 99999)
		.'_'.mt_rand(10000, 99999);
	}
}