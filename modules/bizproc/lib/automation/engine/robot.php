<?php

namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc\Workflow\Template\SourceType;

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

	public function setProperties(array $properties): self
	{
		$this->bizprocActivity['Properties'] = $properties;
		return $this;
	}

	public function getProperty(string $name)
	{
		$properties = $this->getProperties();
		return array_key_exists($name, $properties) ? $properties[$name] : null;
	}

	public function setProperty(string $name, $value)
	{
		$properties = $this->getProperties();
		$properties[$name] = $value;
		return $this->setProperties($properties);
	}

	public function getReturnProperties(): array
	{
		return \CBPRuntime::GetRuntime(true)->getActivityReturnProperties($this->getType());
	}

	public function getReturnProperty(string $name): ?array
	{
		$props = $this->getReturnProperties();
		return ($props && isset($props[$name])) ? $props[$name] : null;
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

	public function isActivated(): bool
	{
		return \CBPHelper::getBool($this->bizprocActivity['Activated'] ?? true);
	}

	public function getDescription(): ?array
	{
		return \CBPRuntime::GetRuntime(true)->GetActivityDescription($this->getType());
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

	public function collectUsages(): array
	{
		\CBPActivity::IncludeActivityFile($this->getType());
		try
		{
			$activity = \CBPActivity::createInstance($this->getType(), $this->getName());
			if ($activity)
			{
				$activity->initializeFromArray($this->getProperties());

				return $activity->collectUsages();
			}
		}
		catch (\Exception $e)
		{
		}

		return [];
	}

	public function hasBrokenLink(\Bitrix\Bizproc\Automation\Engine\Template $template): bool
	{
		$usages = $this->collectUsages();
		if (!$usages)
		{
			return false;
		}

		$checkObjects = [
			\Bitrix\Bizproc\Workflow\Template\SourceType::DocumentField,
			\Bitrix\Bizproc\Workflow\Template\SourceType::GlobalConstant,
			\Bitrix\Bizproc\Workflow\Template\SourceType::GlobalVariable,
			\Bitrix\Bizproc\Workflow\Template\SourceType::Variable,
			\Bitrix\Bizproc\Workflow\Template\SourceType::Constant,
		];

		foreach ($usages as $usage)
		{
			$object = $usage[0];
			$field = $usage[1];

			if (in_array($object, $checkObjects))
			{
				$property = $template->getProperty($object, $field);

				if (!$property)
				{
					return true;
				}
			}
		}

		return false;
	}
}