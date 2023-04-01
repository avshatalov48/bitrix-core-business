<?php

namespace Bitrix\Calendar\Core\Queue\Rule;

use Bitrix\Calendar\Core\Queue\Exception\InvalidRuleException;
use Bitrix\Calendar\Core\Queue\Interfaces\RouteRule;
use Bitrix\Calendar\Core\Queue\Rule\Rules\EventAttendeesUpdateRule;
use Bitrix\Calendar\Core\Queue\Rule\Rules\EventDelayedSyncRule;
use Bitrix\Calendar\Core\Queue\Rule\Rules\EventWithEntityAttendeesFindRule;
use Bitrix\Calendar\Core\Queue\Rule\Rules\PushDelayedRule;
use Bitrix\Calendar\Internals\SingletonTrait;

class Registry
{
	use SingletonTrait;

	private array $rules = [];

	protected function __construct()
	{
		$this->registerRule(new EventDelayedSyncRule());
		$this->registerRule(new PushDelayedRule());
		$this->registerRule(new EventAttendeesUpdateRule());
		$this->registerRule(new EventWithEntityAttendeesFindRule());
		// add preinstalled rules here
		// for example:
		// $this->registerRule(new ExampleRule());
		// $this->registerRuleClass(ExampleRule::class);
	}

	/**
	 * @param RouteRule $rule
	 *
	 * @return $this
	 */
	public function registerRule(RouteRule $rule): self
	{
		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * @param string $className
	 *
	 * @return $this
	 *
	 * @throws InvalidRuleException
	 */
	public function registerRuleClass(string $className): self
	{
		if (class_exists($className))
		{
			$rule = new $className();
			if ($rule instanceof RouteRule)
			{
				$this->registerRule($rule);
			}
			else
			{
				throw InvalidRuleException::classIsNotRule();
			}
		}
		else
		{
			throw InvalidRuleException::classIsInvalid(404);
		}

		return $this;
	}

	/**
	 * @return RouteRule[]
	 */
	public function getRules(): array
	{
		return $this->rules;
	}
}